<?php

namespace App\Services\Tax;

use App\Models\TaxImportBatch;
use App\Models\TaxPphFinalRecord;
use App\Models\TaxPph23Record;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * BpputService — Bukti Pemotongan PPh Unifikasi (BPPU)
 *
 * Fitur:
 * 1. Import Excel DJP (BPPU Excel to XML v.3.xlsx) → database internal
 * 2. Export data internal → Excel siap upload ke Coretax DJP
 * 3. Validasi duplikat, cross-check PPh, preview sebelum commit
 *
 * Format Excel DJP:
 *   Row 1: Header info (NPWP Pemotong di B1, Masa Pajak di D1)
 *   Row 2: Sub-header info
 *   Row 3: Kolom headers
 *   Row 4+: Data records
 */
class BpputService
{
    // Kolom header Excel DJP BPPU (row 3, 0-indexed kolom A=0)
    const COLS = [
        'no'                => 0,  // A: No
        'npwp_penerima'     => 1,  // B: NPWP Penerima (16-digit)
        'nik_penerima'      => 2,  // C: NIK
        'nama_penerima'     => 3,  // D: Nama Penerima
        'id_tku_penerima'   => 4,  // E: ID TKU Penerima
        'kode_objek_pajak'  => 5,  // F: Kode Objek Pajak
        'nama_objek_pajak'  => 6,  // G: Nama Objek Pajak (auto dari kode)
        'dpp_bruto'         => 7,  // H: DPP / Bruto
        'fasilitas'         => 8,  // I: Fasilitas
        'deemed_percent'    => 9,  // J: Deemed (%)
        'tarif'             => 10, // K: Tarif (%)
        'pph_dipotong'      => 11, // L: PPh Dipotong
        'jenis_dok_ref'     => 12, // M: Jenis Dok Referensi
        'no_dok_ref'        => 13, // N: No Dok Referensi
        'tgl_dok_ref'       => 14, // O: Tanggal Dok Referensi
        'id_tku_pemotong'   => 15, // P: ID TKU Pemotong
        'tgl_pemotongan'    => 16, // Q: Tanggal Pemotongan
    ];

    // ─────────────────────────────────────────────────────────────────────────
    //  IMPORT Excel DJP → Database Internal
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Baca file Excel DJP BPPU, validasi, simpan ke tax_import_batches (status preview).
     * Belum commit ke tabel PPh yang sebenarnya — user harus confirm dulu.
     *
     * @param  string  $filepath  Path ke file Excel yang sudah diupload
     * @param  string  $filename  Nama file asli
     * @return TaxImportBatch
     */
    public function readForPreview(string $filepath, string $filename): TaxImportBatch
    {
        $spreadsheet = IOFactory::load($filepath);
        $sheet = $spreadsheet->getActiveSheet();

        // ── Baca header ──────────────────────────────────────────────────────
        $npwpPemotong = trim((string) $sheet->getCell('B1')->getValue());
        $namaPemotong = trim((string) $sheet->getCell('D1')->getValue());
        $masaPajak    = $this->parseMasaPajak(trim((string) $sheet->getCell('F1')->getValue()));

        $rows        = [];
        $errors      = [];
        $duplicates  = 0;
        $rowNum      = 4; // Data mulai baris 4

        while ($sheet->getCell('A' . $rowNum)->getValue() !== null && $sheet->getCell('A' . $rowNum)->getValue() !== '') {
            $raw = $this->readRow($sheet, $rowNum);
            $validation = $this->validateRow($raw, $rowNum);

            $isDuplicate = false;
            if ($raw['no_bukti_potong'] ?? null) {
                // Cek duplikat di database (pph_final dan pph23)
                $isDuplicate = TaxPphFinalRecord::where('no_bukti_potong', $raw['no_bukti_potong'])->exists()
                            || TaxPph23Record::where('no_bukti_potong', $raw['no_bukti_potong'])->exists();
            }

            $rows[] = [
                'row'           => $rowNum,
                'data'          => $raw,
                'valid'         => empty($validation),
                'duplicate'     => $isDuplicate,
                'errors'        => $validation,
            ];

            if (!empty($validation)) {
                $errors = array_merge($errors, $validation);
            }
            if ($isDuplicate) $duplicates++;

            $rowNum++;
        }

        $total   = count($rows);
        $success = count(array_filter($rows, fn($r) => $r['valid'] && !$r['duplicate']));
        $errCnt  = count(array_filter($rows, fn($r) => !$r['valid']));

        $batch = TaxImportBatch::create([
            'jenis'          => 'bppu',
            'npwp_pemotong'  => $npwpPemotong,
            'nama_pemotong'  => $namaPemotong,
            'filename'       => $filename,
            'masa_pajak'     => $masaPajak,
            'total_rows'     => $total,
            'success_rows'   => $success,
            'error_rows'     => $errCnt,
            'duplicate_rows' => $duplicates,
            'status'         => 'preview',
            'errors'         => array_slice($errors, 0, 100), // max 100 error ditampilkan
            'preview_data'   => $rows,
            'created_by'     => Auth::id(),
        ]);

        return $batch;
    }

    /**
     * Commit batch import: simpan data valid ke tabel PPh yang sebenarnya.
     * Jenis PPh ditentukan dari kode_objek_pajak:
     *   - 28-xxx → PPh Final Pasal 4(2) → tax_pph_final_records
     *   - 24-xxx → PPh Pasal 23 → tax_pph23_records
     */
    public function commitBatch(TaxImportBatch $batch): array
    {
        if ($batch->status === 'committed') {
            return ['success' => false, 'message' => 'Batch ini sudah pernah di-commit.'];
        }

        $rows = $batch->preview_data ?? [];
        $saved = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $batch, &$saved, &$skipped) {
            foreach ($rows as $row) {
                if (!$row['valid'] || $row['duplicate']) {
                    $skipped++;
                    continue;
                }

                $data = $row['data'];
                $kop  = $data['kode_objek_pajak'] ?? '';

                if (str_starts_with($kop, '28-')) {
                    $this->savePphFinal($data, $batch);
                } elseif (str_starts_with($kop, '24-')) {
                    $this->savePph23($data, $batch);
                } else {
                    $skipped++;
                    continue;
                }

                $saved++;
            }

            $batch->update([
                'status'        => 'committed',
                'success_rows'  => $saved,
                'preview_data'  => null, // Bersihkan preview data setelah commit
            ]);
        });

        return [
            'success'  => true,
            'saved'    => $saved,
            'skipped'  => $skipped,
            'message'  => "Berhasil menyimpan {$saved} bukti pemotongan. {$skipped} baris dilewati.",
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EXPORT Database Internal → Excel DJP (siap upload Coretax)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate file Excel BPPU format DJP dari data internal.
     *
     * @param  string  $masaPajak   Format YYYY-MM
     * @param  string  $jenisData   'final' | 'pph23' | 'all'
     * @param  string  $npwpPemotong
     * @param  string  $namaPemotong
     * @return Spreadsheet
     */
    public function generateExportBppu(
        string $masaPajak,
        string $jenisData,
        string $npwpPemotong,
        string $namaPemotong
    ): Spreadsheet {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BPPU');

        // ── Header row 1 ──────────────────────────────────────────────────────
        $sheet->setCellValue('A1', 'NPWP Pemotong');
        $sheet->setCellValue('B1', $npwpPemotong);
        $sheet->setCellValue('C1', 'Nama Pemotong');
        $sheet->setCellValue('D1', $namaPemotong);
        $sheet->setCellValue('E1', 'Masa Pajak');
        [$year, $month] = explode('-', $masaPajak);
        $sheet->setCellValue('F1', $month . '/' . $year);

        // ── Header row 3 (kolom headers) ─────────────────────────────────────
        $headers = [
            'No', 'NPWP Penerima', 'NIK', 'Nama Penerima', 'ID TKU Penerima',
            'Kode Objek Pajak', 'Nama Objek Pajak', 'DPP/Bruto', 'Fasilitas',
            'Deemed (%)', 'Tarif (%)', 'PPh Dipotong',
            'Jenis Dok Referensi', 'No Dok Referensi', 'Tanggal Dok Referensi',
            'ID TKU Pemotong', 'Tanggal Pemotongan',
        ];
        foreach ($headers as $col => $header) {
            $cell = $this->colLetter($col) . '3';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }

        // ── Data rows ────────────────────────────────────────────────────────
        $records = collect();

        if (in_array($jenisData, ['final', 'all'])) {
            $final = TaxPphFinalRecord::where('periode', $masaPajak)->get();
            $records = $records->concat($final->map(fn($r) => $this->finalToRow($r)));
        }

        if (in_array($jenisData, ['pph23', 'all'])) {
            $pph23 = TaxPph23Record::where('periode', $masaPajak)->get();
            $records = $records->concat($pph23->map(fn($r) => $this->pph23ToRow($r)));
        }

        $rowNum = 4;
        foreach ($records as $idx => $rec) {
            $rec['no'] = $idx + 1;
            foreach ($rec as $col => $value) {
                if ($col === 'no') {
                    $sheet->setCellValue('A' . $rowNum, $value);
                    continue;
                }
                $colIndex = self::COLS[$col] ?? null;
                if ($colIndex === null) continue;
                $cell = $this->colLetter($colIndex) . $rowNum;
                $sheet->setCellValue($cell, $value);
            }

            // Format angka
            foreach (['H', 'L'] as $moneyCol) {
                $sheet->getStyle($moneyCol . $rowNum)
                      ->getNumberFormat()->setFormatCode('#,##0');
            }

            $rowNum++;
        }

        // ── Auto column width ─────────────────────────────────────────────────
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Smart Features — Analytics & Validation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Rekap status BPPU per masa pajak — untuk dashboard SPT Masa.
     * Return: ['2026-01' => ['final_count' => N, 'pph23_count' => N, 'total_pph' => X], ...]
     */
    public static function rekapPerMasa(int $tahun): array
    {
        $rekap = [];
        for ($m = 1; $m <= 12; $m++) {
            $masa = $tahun . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            $finalCount = TaxPphFinalRecord::where('periode', $masa)->count();
            $pph23Count = TaxPph23Record::where('periode', $masa)->count();
            $finalTotal = TaxPphFinalRecord::where('periode', $masa)->sum('pph_dipotong');
            $pph23Total = TaxPph23Record::where('periode', $masa)->sum('pph_dipotong');
            $rekap[$masa] = [
                'final_count' => $finalCount,
                'pph23_count' => $pph23Count,
                'total_count' => $finalCount + $pph23Count,
                'final_pph'   => (float) $finalTotal,
                'pph23_pph'   => (float) $pph23Total,
                'total_pph'   => (float) $finalTotal + (float) $pph23Total,
                'has_data'    => ($finalCount + $pph23Count) > 0,
            ];
        }
        return $rekap;
    }

    /**
     * Cek anomali PPh (pph_dipotong ≠ nilai_kontrak × tarif).
     * Return array of records dengan anomali.
     */
    public static function cariAnomali(string $masaPajak): array
    {
        $anomali = [];

        $final = TaxPphFinalRecord::where('periode', $masaPajak)->get();
        foreach ($final as $r) {
            $expected = round((float) $r->nilai_kontrak * (float) $r->tarif / 100, 0);
            $actual   = round((float) $r->pph_dipotong, 0);
            if (abs($expected - $actual) > 1) {
                $anomali[] = [
                    'type'     => 'PPh Final 4(2)',
                    'id'       => $r->id,
                    'no'       => $r->no_bukti_potong,
                    'nama'     => $r->nama_pemotong,
                    'expected' => $expected,
                    'actual'   => $actual,
                    'selisih'  => $actual - $expected,
                ];
            }
        }

        $pph23 = TaxPph23Record::where('periode', $masaPajak)->get();
        foreach ($pph23 as $r) {
            $expected = round((float) $r->bruto * (float) $r->tarif / 100, 0);
            $actual   = round((float) $r->pph_dipotong, 0);
            if (abs($expected - $actual) > 1) {
                $anomali[] = [
                    'type'     => 'PPh 23',
                    'id'       => $r->id,
                    'no'       => $r->no_bukti_potong,
                    'nama'     => $r->nama_penerima,
                    'expected' => $expected,
                    'actual'   => $actual,
                    'selisih'  => $actual - $expected,
                ];
            }
        }

        return $anomali;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Private Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function readRow($sheet, int $rowNum): array
    {
        return [
            'no_bukti_potong'  => trim((string) ($sheet->getCell('A' . $rowNum)->getValue() ?? '')),
            'npwp_penerima'    => trim((string) ($sheet->getCell('B' . $rowNum)->getValue() ?? '')),
            'nik_penerima'     => trim((string) ($sheet->getCell('C' . $rowNum)->getValue() ?? '')),
            'nama_penerima'    => trim((string) ($sheet->getCell('D' . $rowNum)->getValue() ?? '')),
            'id_tku_penerima'  => trim((string) ($sheet->getCell('E' . $rowNum)->getValue() ?? '')),
            'kode_objek_pajak' => trim((string) ($sheet->getCell('F' . $rowNum)->getValue() ?? '')),
            'nama_objek_pajak' => trim((string) ($sheet->getCell('G' . $rowNum)->getValue() ?? '')),
            'nilai_kontrak'    => $this->parseNumber($sheet->getCell('H' . $rowNum)->getValue()),
            'fasilitas'        => trim((string) ($sheet->getCell('I' . $rowNum)->getValue() ?? '')),
            'deemed_percent'   => $this->parseNumber($sheet->getCell('J' . $rowNum)->getValue()),
            'tarif'            => $this->parseNumber($sheet->getCell('K' . $rowNum)->getValue()),
            'pph_dipotong'     => $this->parseNumber($sheet->getCell('L' . $rowNum)->getValue()),
            'jenis_dok_ref'    => trim((string) ($sheet->getCell('M' . $rowNum)->getValue() ?? '')),
            'no_dok_ref'       => trim((string) ($sheet->getCell('N' . $rowNum)->getValue() ?? '')),
            'tgl_dok_ref'      => $this->parseDate($sheet->getCell('O' . $rowNum)->getValue()),
            'id_tku_pemotong'  => trim((string) ($sheet->getCell('P' . $rowNum)->getValue() ?? '')),
            'tgl_pemotongan'   => $this->parseDate($sheet->getCell('Q' . $rowNum)->getValue()),
        ];
    }

    private function validateRow(array $data, int $rowNum): array
    {
        $errors = [];

        if (empty($data['nama_penerima'])) {
            $errors[] = ['row' => $rowNum, 'field' => 'nama_penerima', 'message' => 'Nama penerima wajib diisi'];
        }

        if (empty($data['kode_objek_pajak'])) {
            $errors[] = ['row' => $rowNum, 'field' => 'kode_objek_pajak', 'message' => 'Kode Objek Pajak wajib diisi'];
        } elseif (!TaxKodeObjekPajakReference::find($data['kode_objek_pajak'])) {
            $errors[] = ['row' => $rowNum, 'field' => 'kode_objek_pajak', 'message' => "KOP '{$data['kode_objek_pajak']}' tidak dikenal"];
        }

        if (($data['nilai_kontrak'] ?? 0) <= 0) {
            $errors[] = ['row' => $rowNum, 'field' => 'nilai_kontrak', 'message' => 'DPP/Bruto harus lebih dari 0'];
        }

        if (($data['tarif'] ?? 0) <= 0) {
            $errors[] = ['row' => $rowNum, 'field' => 'tarif', 'message' => 'Tarif harus lebih dari 0'];
        }

        // Cross-check PPh = DPP × tarif (toleransi ±1 rupiah)
        if (!empty($data['nilai_kontrak']) && !empty($data['tarif']) && !empty($data['pph_dipotong'])) {
            $expected = round($data['nilai_kontrak'] * $data['tarif'] / 100, 0);
            if (abs($expected - $data['pph_dipotong']) > 1) {
                $errors[] = [
                    'row'     => $rowNum,
                    'field'   => 'pph_dipotong',
                    'message' => "PPh Dipotong Rp " . number_format($data['pph_dipotong'], 0, ',', '.') .
                                 " ≠ DPP×Tarif Rp " . number_format($expected, 0, ',', '.')
                ];
            }
        }

        return $errors;
    }

    private function savePphFinal(array $data, TaxImportBatch $batch): void
    {
        // Auto-detect jenis_usaha & kualifikasi dari kode_objek_pajak
        [$jenisUsaha, $kualifikasi] = $this->detectKonstruksiType($data['kode_objek_pajak']);

        TaxPphFinalRecord::create([
            'nama_pemotong'      => $batch->nama_pemotong ?? '',
            'npwp_pemotong'      => $batch->npwp_pemotong ?? '',
            'npwp_penerima'      => $data['npwp_penerima'] ?? null,
            'nik_penerima'       => $data['nik_penerima'] ?? null,
            'nama_penerima'      => $data['nama_penerima'],
            'id_tku_pemotong'    => $data['id_tku_pemotong'] ?? null,
            'id_tku_penerima'    => $data['id_tku_penerima'] ?? null,
            'no_bukti_potong'    => $data['no_bukti_potong'] ?? null,
            'tanggal_bukti_potong' => $data['tgl_pemotongan'] ?? now()->toDateString(),
            'tanggal_pemotongan' => $data['tgl_pemotongan'] ?? null,
            'jenis_usaha'        => $jenisUsaha,
            'kualifikasi'        => $kualifikasi,
            'kode_objek_pajak'   => $data['kode_objek_pajak'],
            'nama_objek_pajak'   => $data['nama_objek_pajak'] ?: TaxKodeObjekPajakReference::namaKop($data['kode_objek_pajak']),
            'nilai_kontrak'      => $data['nilai_kontrak'] ?? 0,
            'tarif'              => $data['tarif'] ?? 0,
            'fasilitas'          => $data['fasilitas'] ?? null,
            'deemed_percent'     => $data['deemed_percent'] > 0 ? $data['deemed_percent'] : null,
            'pph_dipotong'       => $data['pph_dipotong'] ?? 0,
            'periode'            => $batch->masa_pajak,
            'status'             => 'belum_terima_bukpot',
            'jenis_dok_referensi' => $data['jenis_dok_ref'] ?? null,
            'no_dok_referensi'   => $data['no_dok_ref'] ?? null,
            'tanggal_dok_referensi' => $data['tgl_dok_ref'] ?? null,
            'sumber'             => 'import_excel',
            'import_batch_id'    => $batch->id,
            'created_by'         => $batch->created_by,
        ]);
    }

    private function savePph23(array $data, TaxImportBatch $batch): void
    {
        $kop  = $data['kode_objek_pajak'];
        $ref  = TaxKodeObjekPajakReference::find($kop);
        $tarif = $data['tarif'] > 0 ? $data['tarif'] : ($ref['tarif'] ?? 2.0);

        TaxPph23Record::create([
            'nama_penerima'      => $data['nama_penerima'],
            'npwp_penerima'      => $data['npwp_penerima'] ?? null,
            'jenis_penghasilan'  => TaxKodeObjekPajakReference::namaKop($kop),
            'kode_objek_pajak'   => $kop,
            'nama_objek_pajak'   => $data['nama_objek_pajak'] ?: TaxKodeObjekPajakReference::namaKop($kop),
            'id_tku_pemotong'    => $data['id_tku_pemotong'] ?? null,
            'id_tku_penerima'    => $data['id_tku_penerima'] ?? null,
            'bruto'              => $data['nilai_kontrak'] ?? 0,
            'tarif'              => $tarif,
            'fasilitas'          => $data['fasilitas'] ?? null,
            'deemed_percent'     => $data['deemed_percent'] > 0 ? $data['deemed_percent'] : null,
            'pph_dipotong'       => $data['pph_dipotong'] ?? 0,
            'no_bukti_potong'    => $data['no_bukti_potong'] ?? null,
            'tanggal_bukti_potong' => $data['tgl_pemotongan'] ?? now()->toDateString(),
            'tanggal_pemotongan' => $data['tgl_pemotongan'] ?? null,
            'periode'            => $batch->masa_pajak,
            'status'             => 'belum_disetor',
            'jenis_dok_referensi' => $data['jenis_dok_ref'] ?? null,
            'no_dok_referensi'   => $data['no_dok_ref'] ?? null,
            'tanggal_dok_referensi' => $data['tgl_dok_ref'] ?? null,
            'sumber'             => 'import_excel',
            'import_batch_id'    => $batch->id,
            'created_by'         => $batch->created_by,
        ]);
    }

    private function finalToRow(TaxPphFinalRecord $r): array
    {
        return [
            'npwp_penerima'   => $r->npwp_penerima ?? '',
            'nik_penerima'    => $r->nik_penerima ?? '',
            'nama_penerima'   => $r->nama_penerima ?? $r->nama_pemotong,
            'id_tku_penerima' => $r->id_tku_penerima ?? '',
            'kode_objek_pajak'=> $r->kode_objek_pajak ?? '',
            'nama_objek_pajak'=> $r->nama_objek_pajak ?? TaxKodeObjekPajakReference::namaKop($r->kode_objek_pajak ?? ''),
            'dpp_bruto'       => (float) $r->nilai_kontrak,
            'fasilitas'       => $r->fasilitas ?? '',
            'deemed_percent'  => $r->deemed_percent ?? '',
            'tarif'           => (float) $r->tarif,
            'pph_dipotong'    => (float) $r->pph_dipotong,
            'jenis_dok_ref'   => $r->jenis_dok_referensi ?? '',
            'no_dok_ref'      => $r->no_dok_referensi ?? '',
            'tgl_dok_ref'     => $r->tanggal_dok_referensi?->format('d/m/Y') ?? '',
            'id_tku_pemotong' => $r->id_tku_pemotong ?? '',
            'tgl_pemotongan'  => $r->tanggal_pemotongan?->format('d/m/Y') ?? $r->tanggal_bukti_potong?->format('d/m/Y') ?? '',
        ];
    }

    private function pph23ToRow(TaxPph23Record $r): array
    {
        return [
            'npwp_penerima'   => $r->npwp_penerima ?? '',
            'nik_penerima'    => '',
            'nama_penerima'   => $r->nama_penerima,
            'id_tku_penerima' => $r->id_tku_penerima ?? '',
            'kode_objek_pajak'=> $r->kode_objek_pajak ?? '',
            'nama_objek_pajak'=> $r->nama_objek_pajak ?? TaxKodeObjekPajakReference::namaKop($r->kode_objek_pajak ?? ''),
            'dpp_bruto'       => (float) $r->bruto,
            'fasilitas'       => $r->fasilitas ?? '',
            'deemed_percent'  => $r->deemed_percent ?? '',
            'tarif'           => (float) $r->tarif,
            'pph_dipotong'    => (float) $r->pph_dipotong,
            'jenis_dok_ref'   => $r->jenis_dok_referensi ?? '',
            'no_dok_ref'      => $r->no_dok_referensi ?? '',
            'tgl_dok_ref'     => $r->tanggal_dok_referensi?->format('d/m/Y') ?? '',
            'id_tku_pemotong' => $r->id_tku_pemotong ?? '',
            'tgl_pemotongan'  => $r->tanggal_pemotongan?->format('d/m/Y') ?? $r->tanggal_bukti_potong?->format('d/m/Y') ?? '',
        ];
    }

    private function detectKonstruksiType(string $kop): array
    {
        return match($kop) {
            '28-409-01' => ['pelaksana', 'tidak_bersertifikat'],
            '28-409-02' => ['pelaksana', 'kecil'],
            '28-409-03' => ['pelaksana', 'menengah'],
            '28-409-25' => ['pelaksana', 'menengah'],
            '28-409-26' => ['pelaksana', 'tidak_bersertifikat'],
            '28-409-04' => ['perencana', 'tidak_bersertifikat'],
            '28-409-05' => ['perencana', 'kecil'],
            '28-409-27' => ['konsultansi', 'kecil'],
            '28-409-28' => ['konsultansi', 'tidak_bersertifikat'],
            default     => ['pelaksana', 'kecil'],
        };
    }

    private function parseMasaPajak(string $raw): ?string
    {
        // Format bisa: "01/2026", "2026-01", "Januari 2026"
        if (preg_match('/(\d{2})\/(\d{4})/', $raw, $m)) {
            return $m[2] . '-' . $m[1];
        }
        if (preg_match('/(\d{4})-(\d{2})/', $raw, $m)) {
            return $m[0];
        }
        return now()->format('Y-m');
    }

    private function parseNumber($val): float
    {
        if ($val === null || $val === '') return 0.0;
        return (float) preg_replace('/[^\d.]/', '', str_replace(',', '.', (string) $val));
    }

    private function parseDate($val): ?string
    {
        if (empty($val)) return null;
        try {
            if (is_numeric($val)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d');
            }
            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function colLetter(int $index): string
    {
        $letters = range('A', 'Z');
        if ($index < 26) return $letters[$index];
        return $letters[intdiv($index, 26) - 1] . $letters[$index % 26];
    }
}
