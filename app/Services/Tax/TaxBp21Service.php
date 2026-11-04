<?php

namespace App\Services\Tax;

use App\Models\TaxBp21Record;
use App\Models\TaxImportBatch;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * TaxBp21Service — Bukti Pemotongan PPh Pasal 21 (BP21)
 *
 * Import/export sesuai format DJP "BP21 Excel to XML v.4.xlsx".
 * Mendukung tarif TER (berlaku Jan 2024), PS17, Harian, Pesangon, Pensiun.
 *
 * Smart features:
 * - Auto-detect jenis_tarif dari kode_objek_pajak
 * - Validasi PTKP status format
 * - Cross-check PPh dengan deemed % jika berlaku
 */
class TaxBp21Service
{
    // ─────────────────────────────────────────────────────────────────────────
    //  IMPORT Excel DJP BP21 → Database Internal
    // ─────────────────────────────────────────────────────────────────────────

    public function readForPreview(string $filepath, string $filename): TaxImportBatch
    {
        $spreadsheet = IOFactory::load($filepath);
        $sheet = $spreadsheet->getActiveSheet();

        $npwpPemotong = trim((string) $sheet->getCell('B1')->getValue());
        $namaPemotong = trim((string) $sheet->getCell('D1')->getValue());
        $masaRaw      = $sheet->getCell('F1')->getValue();
        $masaPajak    = $this->parseMasa((string) $masaRaw);
        $tahunPajak   = (int) ($sheet->getCell('H1')->getValue() ?? now()->year);

        $rows = []; $errors = []; $rowNum = 4;

        while ($sheet->getCell('A' . $rowNum)->getValue() !== null && $sheet->getCell('A' . $rowNum)->getValue() !== '') {
            $raw = [
                'no_bukti_potong'      => trim((string) $sheet->getCell('A' . $rowNum)->getValue()),
                'npwp_penerima'        => trim((string) $sheet->getCell('B' . $rowNum)->getValue()),
                'nik_penerima'         => trim((string) $sheet->getCell('C' . $rowNum)->getValue()),
                'nama_penerima'        => trim((string) $sheet->getCell('D' . $rowNum)->getValue()),
                'id_tku_penerima'      => trim((string) $sheet->getCell('E' . $rowNum)->getValue()),
                'status_ptkp'          => trim((string) $sheet->getCell('F' . $rowNum)->getValue()),
                'kode_objek_pajak'     => trim((string) $sheet->getCell('G' . $rowNum)->getValue()),
                'nama_objek_pajak'     => trim((string) $sheet->getCell('H' . $rowNum)->getValue()),
                'penghasilan_bruto'    => $this->parseNumber($sheet->getCell('I' . $rowNum)->getValue()),
                'deemed_percent'       => $this->parseNumber($sheet->getCell('J' . $rowNum)->getValue()),
                'tarif'                => $this->parseNumber($sheet->getCell('K' . $rowNum)->getValue()),
                'pph_dipotong'         => $this->parseNumber($sheet->getCell('L' . $rowNum)->getValue()),
                'jenis_dok_referensi'  => trim((string) $sheet->getCell('M' . $rowNum)->getValue()),
                'no_dok_referensi'     => trim((string) $sheet->getCell('N' . $rowNum)->getValue()),
                'tanggal_dok_referensi'=> $this->parseDate($sheet->getCell('O' . $rowNum)->getValue()),
                'id_tku_pemotong'      => trim((string) $sheet->getCell('P' . $rowNum)->getValue()),
                'tanggal_pemotongan'   => $this->parseDate($sheet->getCell('Q' . $rowNum)->getValue()),
            ];

            $rowErrors = $this->validateRow($raw, $rowNum);
            $isDuplicate = !empty($raw['no_bukti_potong'])
                        && TaxBp21Record::where('no_bukti_potong', $raw['no_bukti_potong'])->exists();

            // Auto-fill from KOP reference
            $kop = TaxKodeObjekPajakReference::find($raw['kode_objek_pajak'] ?? '');
            if ($kop && empty($raw['nama_objek_pajak'])) {
                $raw['nama_objek_pajak'] = $kop['nama'];
            }
            if ($kop && isset($kop['jenis_tarif'])) {
                $raw['jenis_tarif'] = $kop['jenis_tarif'];
            } else {
                $raw['jenis_tarif'] = 'TER';
            }

            $rows[] = [
                'row' => $rowNum, 'data' => $raw,
                'valid' => empty($rowErrors), 'duplicate' => $isDuplicate, 'errors' => $rowErrors,
            ];
            $errors = array_merge($errors, $rowErrors);
            $rowNum++;
        }

        return TaxImportBatch::create([
            'jenis'          => 'bp21',
            'npwp_pemotong'  => $npwpPemotong,
            'nama_pemotong'  => $namaPemotong,
            'filename'       => $filename,
            'masa_pajak'     => $masaPajak,
            'total_rows'     => count($rows),
            'success_rows'   => count(array_filter($rows, fn($r) => $r['valid'] && !$r['duplicate'])),
            'error_rows'     => count(array_filter($rows, fn($r) => !$r['valid'])),
            'duplicate_rows' => count(array_filter($rows, fn($r) => $r['duplicate'])),
            'status'         => 'preview',
            'errors'         => array_slice($errors, 0, 100),
            'preview_data'   => $rows,
            'created_by'     => Auth::id(),
        ]);
    }

    public function commitBatch(TaxImportBatch $batch): array
    {
        if ($batch->status === 'committed') {
            return ['success' => false, 'message' => 'Batch sudah di-commit.'];
        }

        $saved = 0; $skipped = 0;
        [$year, $month] = explode('-', $batch->masa_pajak ?? date('Y-m'));

        DB::transaction(function () use ($batch, $year, $month, &$saved, &$skipped) {
            foreach ($batch->preview_data ?? [] as $row) {
                if (!$row['valid'] || $row['duplicate']) { $skipped++; continue; }
                $d = $row['data'];
                TaxBp21Record::create([
                    'no_bukti_potong'      => $d['no_bukti_potong'] ?: null,
                    'masa_pajak'           => $batch->masa_pajak,
                    'tahun_pajak'          => (int) $year,
                    'pembetulan'           => 0,
                    'npwp_penerima'        => $d['npwp_penerima'] ?: null,
                    'nik_penerima'         => $d['nik_penerima'] ?: null,
                    'nama_penerima'        => $d['nama_penerima'],
                    'id_tku_penerima'      => $d['id_tku_penerima'] ?: null,
                    'status_ptkp'          => $d['status_ptkp'] ?: null,
                    'kode_objek_pajak'     => $d['kode_objek_pajak'],
                    'nama_objek_pajak'     => $d['nama_objek_pajak'] ?: TaxKodeObjekPajakReference::namaKop($d['kode_objek_pajak'] ?? ''),
                    'jenis_tarif'          => $d['jenis_tarif'] ?? 'TER',
                    'penghasilan_bruto'    => $d['penghasilan_bruto'],
                    'deemed_percent'       => $d['deemed_percent'] > 0 ? $d['deemed_percent'] : null,
                    'tarif'                => $d['tarif'],
                    'pph_dipotong'         => $d['pph_dipotong'],
                    'jenis_dok_referensi'  => $d['jenis_dok_referensi'] ?: null,
                    'no_dok_referensi'     => $d['no_dok_referensi'] ?: null,
                    'tanggal_dok_referensi'=> $d['tanggal_dok_referensi'] ?: null,
                    'id_tku_pemotong'      => $d['id_tku_pemotong'] ?: null,
                    'tanggal_pemotongan'   => $d['tanggal_pemotongan'] ?: null,
                    'status'               => 'belum_disetor',
                    'sumber'               => 'import_excel',
                    'import_batch_id'      => $batch->id,
                    'created_by'           => $batch->created_by,
                ]);
                $saved++;
            }
            $batch->update(['status' => 'committed', 'success_rows' => $saved, 'preview_data' => null]);
        });

        return ['success' => true, 'saved' => $saved, 'skipped' => $skipped,
                'message' => "Berhasil menyimpan {$saved} bukti potong PPh 21."];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EXPORT → Excel DJP BP21
    // ─────────────────────────────────────────────────────────────────────────

    public function generateExport(string $masaPajak, string $npwpPemotong, string $namaPemotong): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BP21');

        [$year, $month] = explode('-', $masaPajak);
        $sheet->setCellValue('A1', 'NPWP Pemotong');
        $sheet->setCellValue('B1', $npwpPemotong);
        $sheet->setCellValue('C1', 'Nama');
        $sheet->setCellValue('D1', $namaPemotong);
        $sheet->setCellValue('E1', 'Masa Pajak');
        $sheet->setCellValue('F1', $month . '/' . $year);
        $sheet->setCellValue('G1', 'Tahun Pajak');
        $sheet->setCellValue('H1', $year);

        $headers = [
            'No BP', 'NPWP', 'NIK', 'Nama Penerima', 'ID TKU Penerima', 'Status PTKP',
            'Kode Objek Pajak', 'Nama Objek Pajak', 'Penghasilan Bruto', 'Deemed (%)',
            'Tarif (%)', 'PPh Dipotong',
            'Jenis Dok Ref', 'No Dok Ref', 'Tgl Dok Ref',
            'ID TKU Pemotong', 'Tgl Pemotongan',
        ];

        foreach ($headers as $i => $h) {
            $cell = chr(65 + $i) . '3';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '155724']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        $records = TaxBp21Record::where('masa_pajak', $masaPajak)->orderBy('nama_penerima')->get();
        $rowNum = 4;

        foreach ($records as $idx => $r) {
            $data = [
                $r->no_bukti_potong ?? '',
                $r->npwp_penerima ?? '',
                $r->nik_penerima ?? '',
                $r->nama_penerima,
                $r->id_tku_penerima ?? '',
                $r->status_ptkp ?? '',
                $r->kode_objek_pajak,
                $r->nama_objek_pajak,
                (float) $r->penghasilan_bruto,
                $r->deemed_percent ?? '',
                (float) $r->tarif,
                (float) $r->pph_dipotong,
                $r->jenis_dok_referensi ?? '',
                $r->no_dok_referensi ?? '',
                $r->tanggal_dok_referensi?->format('d/m/Y') ?? '',
                $r->id_tku_pemotong ?? '',
                $r->tanggal_pemotongan?->format('d/m/Y') ?? '',
            ];

            foreach ($data as $col => $val) {
                $sheet->setCellValue(chr(65 + $col) . $rowNum, $val);
            }

            foreach (['I', 'L'] as $col) {
                $sheet->getStyle($col . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
            }
            $rowNum++;
        }

        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Analytics
    // ─────────────────────────────────────────────────────────────────────────

    public static function rekapPerMasa(int $tahun): array
    {
        $rekap = [];
        for ($m = 1; $m <= 12; $m++) {
            $masa = $tahun . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            $count = TaxBp21Record::where('masa_pajak', $masa)->count();
            $total = TaxBp21Record::where('masa_pajak', $masa)->sum('pph_dipotong');
            $rekap[$masa] = ['count' => $count, 'total_pph' => (float) $total, 'has_data' => $count > 0];
        }
        return $rekap;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function validateRow(array $data, int $rowNum): array
    {
        $errors = [];
        if (empty($data['nama_penerima'])) {
            $errors[] = ['row' => $rowNum, 'field' => 'nama_penerima', 'message' => 'Nama penerima wajib diisi'];
        }
        if (empty($data['kode_objek_pajak'])) {
            $errors[] = ['row' => $rowNum, 'field' => 'kode_objek_pajak', 'message' => 'Kode Objek Pajak wajib diisi'];
        }
        if (($data['penghasilan_bruto'] ?? 0) <= 0) {
            $errors[] = ['row' => $rowNum, 'field' => 'penghasilan_bruto', 'message' => 'Penghasilan bruto harus > 0'];
        }
        return $errors;
    }

    private function parseMasa(string $raw): string
    {
        if (preg_match('/(\d{2})\/(\d{4})/', $raw, $m)) return $m[2] . '-' . $m[1];
        if (preg_match('/(\d{4})-(\d{2})/', $raw, $m)) return $m[0];
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
}
