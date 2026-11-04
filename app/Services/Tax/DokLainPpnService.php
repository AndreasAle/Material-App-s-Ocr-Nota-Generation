<?php

namespace App\Services\Tax;

use App\Models\TaxImportBatch;
use App\Models\TaxPpnMasukan;
use App\Models\TaxPpnKeluaran;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * DokLainPpnService — Dokumen Lain PPN (Masukan & Keluaran)
 *
 * Import/export dokumen non-faktur (nota kontan, bon, kuitansi, dll.)
 * yang dapat dikreditkan sebagai PPN Masukan atau dilaporkan sebagai Keluaran.
 *
 * Format referensi DJP:
 *   - "Create Dok Lain Masukan_0.xlsx"
 *   - "Create Dok Lain Keluaran_0.xlsx"
 *
 * Kode Dokumen Lain DJP:
 *   - Masukan: 02=Faktur Sederhana, 04=Bea Cukai, 05=BPHTB, 06=Bon, dll.
 *   - Keluaran: 01-09 sesuai jenis transaksi
 */
class DokLainPpnService
{
    // ─────────────────────────────────────────────────────────────────────────
    //  Kode Transaksi & Dokumen DJP
    // ─────────────────────────────────────────────────────────────────────────

    public static function kodeTransaksiMasukan(): array
    {
        return [
            '01' => 'Pembelian BKP/JKP dari PKP Rekanan',
            '02' => 'Pembelian dengan Dokumen Tertentu (Faktur Sederhana)',
            '03' => 'Pembelian BKP Tidak Berwujud/JKP dari Luar Negeri',
            '04' => 'Pemanfaatan BKP/JKP dari Luar Negeri melalui Pemungut',
            '06' => 'Pembelian dengan Dokumen Lain yang Dipersamakan',
        ];
    }

    public static function kodeTransaksiKeluaran(): array
    {
        return [
            '01' => 'Penyerahan ke Non-Pemungut PPN',
            '02' => 'Penyerahan ke Pemungut Bendaharawan Pemerintah',
            '03' => 'Penyerahan ke Pemungut Non-Bendaharawan (BUMN)',
            '07' => 'Penyerahan dengan DPP Nilai Lain',
            '08' => 'Ekspor',
        ];
    }

    public static function kodeDokumenLain(): array
    {
        return [
            '00' => 'Faktur Pajak Biasa',
            '01' => 'Nota Kontan',
            '02' => 'Faktur Sederhana',
            '03' => 'Bon / Kuitansi',
            '04' => 'Dokumen Kepabeanan (PIB/PEB)',
            '05' => 'Surat Setoran Pajak (BPHTB)',
            '06' => 'Dokumen Penagihan Listrik/Air/Telepon',
            '07' => 'Tiket/Boarding Pass',
            '08' => 'Faktur Penjualan',
            '09' => 'Dokumen Lain yang Dipersamakan',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  IMPORT Dok Lain Masukan
    // ─────────────────────────────────────────────────────────────────────────

    public function readMasukanForPreview(string $filepath, string $filename): TaxImportBatch
    {
        $spreadsheet = IOFactory::load($filepath);
        $sheet = $spreadsheet->getActiveSheet();

        $npwpPKP  = trim((string) $sheet->getCell('B1')->getValue());
        $namaPKP  = trim((string) $sheet->getCell('D1')->getValue());
        $masaPajak = $this->parseMasa($sheet->getCell('F1')->getValue());

        $rows = [];
        $errors = [];
        $rowNum = 4;

        while ($sheet->getCell('A' . $rowNum)->getValue() !== null && $sheet->getCell('A' . $rowNum)->getValue() !== '') {
            $raw = [
                'no_faktur'          => trim((string) $sheet->getCell('A' . $rowNum)->getValue()),
                'kode_transaksi'     => trim((string) $sheet->getCell('B' . $rowNum)->getValue()),
                'kode_dokumen_lain'  => trim((string) $sheet->getCell('C' . $rowNum)->getValue()),
                'tanggal_faktur'     => $this->parseDate($sheet->getCell('D' . $rowNum)->getValue()),
                'npwp_penjual'       => trim((string) $sheet->getCell('E' . $rowNum)->getValue()),
                'nama_penjual'       => trim((string) $sheet->getCell('F' . $rowNum)->getValue()),
                'alamat_penjual'     => trim((string) $sheet->getCell('G' . $rowNum)->getValue()),
                'dpp'                => $this->parseNumber($sheet->getCell('H' . $rowNum)->getValue()),
                'ppn'                => $this->parseNumber($sheet->getCell('I' . $rowNum)->getValue()),
                'ppnbm'              => $this->parseNumber($sheet->getCell('J' . $rowNum)->getValue()),
                'keterangan'         => trim((string) $sheet->getCell('K' . $rowNum)->getValue()),
            ];

            $rowErrors = [];
            if (empty($raw['nama_penjual'])) {
                $rowErrors[] = ['row' => $rowNum, 'field' => 'nama_penjual', 'message' => 'Nama penjual wajib diisi'];
            }
            if (empty($raw['tanggal_faktur'])) {
                $rowErrors[] = ['row' => $rowNum, 'field' => 'tanggal_faktur', 'message' => 'Tanggal faktur wajib diisi'];
            }

            $isDuplicate = !empty($raw['no_faktur']) && TaxPpnMasukan::where('no_faktur', $raw['no_faktur'])->exists();

            $rows[] = [
                'row' => $rowNum, 'data' => $raw,
                'valid' => empty($rowErrors), 'duplicate' => $isDuplicate, 'errors' => $rowErrors,
            ];
            $errors = array_merge($errors, $rowErrors);
            $rowNum++;
        }

        $total   = count($rows);
        $success = count(array_filter($rows, fn($r) => $r['valid'] && !$r['duplicate']));
        $errCnt  = count(array_filter($rows, fn($r) => !$r['valid']));
        $dupCnt  = count(array_filter($rows, fn($r) => $r['duplicate']));

        return TaxImportBatch::create([
            'jenis'          => 'dok_lain_masukan',
            'npwp_pemotong'  => $npwpPKP,
            'nama_pemotong'  => $namaPKP,
            'filename'       => $filename,
            'masa_pajak'     => $masaPajak,
            'total_rows'     => $total,
            'success_rows'   => $success,
            'error_rows'     => $errCnt,
            'duplicate_rows' => $dupCnt,
            'status'         => 'preview',
            'errors'         => array_slice($errors, 0, 100),
            'preview_data'   => $rows,
            'created_by'     => Auth::id(),
        ]);
    }

    public function commitMasukanBatch(TaxImportBatch $batch): array
    {
        if ($batch->status === 'committed') {
            return ['success' => false, 'message' => 'Batch sudah di-commit.'];
        }

        $saved = 0; $skipped = 0;
        DB::transaction(function () use ($batch, &$saved, &$skipped) {
            foreach ($batch->preview_data ?? [] as $row) {
                if (!$row['valid'] || $row['duplicate']) { $skipped++; continue; }
                $d = $row['data'];
                TaxPpnMasukan::create([
                    'no_faktur'         => $d['no_faktur'] ?: null,
                    'kode_transaksi'    => $d['kode_transaksi'] ?: null,
                    'kode_dokumen_lain' => $d['kode_dokumen_lain'] ?: null,
                    'tanggal_faktur'    => $d['tanggal_faktur'],
                    'npwp_penjual'      => $d['npwp_penjual'] ?: null,
                    'nama_penjual'      => $d['nama_penjual'],
                    'alamat_penjual'    => $d['alamat_penjual'] ?: null,
                    'dpp'               => $d['dpp'],
                    'ppn'               => $d['ppn'],
                    'ppnbm'             => $d['ppnbm'],
                    'keterangan'        => $d['keterangan'] ?: null,
                    'periode'           => $batch->masa_pajak,
                    'dapat_dikreditkan' => true,
                    'sumber'            => 'import_excel',
                    'import_batch_id'   => $batch->id,
                    'created_by'        => $batch->created_by,
                ]);
                $saved++;
            }
            $batch->update(['status' => 'committed', 'success_rows' => $saved, 'preview_data' => null]);
        });

        return ['success' => true, 'saved' => $saved, 'skipped' => $skipped,
                'message' => "Berhasil menyimpan {$saved} dokumen PPN Masukan."];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  IMPORT Dok Lain Keluaran
    // ─────────────────────────────────────────────────────────────────────────

    public function readKeluaranForPreview(string $filepath, string $filename): TaxImportBatch
    {
        $spreadsheet = IOFactory::load($filepath);
        $sheet = $spreadsheet->getActiveSheet();

        $npwpPKP   = trim((string) $sheet->getCell('B1')->getValue());
        $namaPKP   = trim((string) $sheet->getCell('D1')->getValue());
        $masaPajak = $this->parseMasa($sheet->getCell('F1')->getValue());

        $rows = []; $errors = []; $rowNum = 4;

        while ($sheet->getCell('A' . $rowNum)->getValue() !== null && $sheet->getCell('A' . $rowNum)->getValue() !== '') {
            $raw = [
                'no_faktur'             => trim((string) $sheet->getCell('A' . $rowNum)->getValue()),
                'kode_transaksi'        => trim((string) $sheet->getCell('B' . $rowNum)->getValue()),
                'kode_dokumen_lain'     => trim((string) $sheet->getCell('C' . $rowNum)->getValue()),
                'tanggal_faktur'        => $this->parseDate($sheet->getCell('D' . $rowNum)->getValue()),
                'npwp_pembeli'          => trim((string) $sheet->getCell('E' . $rowNum)->getValue()),
                'nama_pembeli'          => trim((string) $sheet->getCell('F' . $rowNum)->getValue()),
                'alamat_pembeli'        => trim((string) $sheet->getCell('G' . $rowNum)->getValue()),
                'dpp'                   => $this->parseNumber($sheet->getCell('H' . $rowNum)->getValue()),
                'ppn'                   => $this->parseNumber($sheet->getCell('I' . $rowNum)->getValue()),
                'ppnbm'                 => $this->parseNumber($sheet->getCell('J' . $rowNum)->getValue()),
                'keterangan'            => trim((string) $sheet->getCell('K' . $rowNum)->getValue()),
                'keterangan_tambahan'   => trim((string) $sheet->getCell('L' . $rowNum)->getValue()),
            ];

            $rowErrors = [];
            if (empty($raw['nama_pembeli'])) {
                $rowErrors[] = ['row' => $rowNum, 'field' => 'nama_pembeli', 'message' => 'Nama pembeli wajib diisi'];
            }
            if (empty($raw['tanggal_faktur'])) {
                $rowErrors[] = ['row' => $rowNum, 'field' => 'tanggal_faktur', 'message' => 'Tanggal faktur wajib diisi'];
            }

            $isDuplicate = !empty($raw['no_faktur']) && TaxPpnKeluaran::where('no_faktur', $raw['no_faktur'])->exists();

            $rows[] = ['row' => $rowNum, 'data' => $raw, 'valid' => empty($rowErrors), 'duplicate' => $isDuplicate, 'errors' => $rowErrors];
            $errors = array_merge($errors, $rowErrors);
            $rowNum++;
        }

        return TaxImportBatch::create([
            'jenis'          => 'dok_lain_keluaran',
            'npwp_pemotong'  => $npwpPKP,
            'nama_pemotong'  => $namaPKP,
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

    public function commitKeluaranBatch(TaxImportBatch $batch): array
    {
        if ($batch->status === 'committed') {
            return ['success' => false, 'message' => 'Batch sudah di-commit.'];
        }

        $saved = 0; $skipped = 0;
        DB::transaction(function () use ($batch, &$saved, &$skipped) {
            foreach ($batch->preview_data ?? [] as $row) {
                if (!$row['valid'] || $row['duplicate']) { $skipped++; continue; }
                $d = $row['data'];
                TaxPpnKeluaran::create([
                    'no_faktur'           => $d['no_faktur'] ?: null,
                    'kode_transaksi'      => $d['kode_transaksi'] ?: null,
                    'kode_dokumen_lain'   => $d['kode_dokumen_lain'] ?: null,
                    'tanggal_faktur'      => $d['tanggal_faktur'],
                    'npwp_pembeli'        => $d['npwp_pembeli'] ?: null,
                    'nama_pembeli'        => $d['nama_pembeli'],
                    'alamat_pembeli'      => $d['alamat_pembeli'] ?: null,
                    'dpp'                 => $d['dpp'],
                    'ppn'                 => $d['ppn'],
                    'ppnbm'              => $d['ppnbm'],
                    'keterangan'         => $d['keterangan'] ?: null,
                    'keterangan_tambahan'=> $d['keterangan_tambahan'] ?: null,
                    'periode'            => $batch->masa_pajak,
                    'sumber'             => 'import_excel',
                    'import_batch_id'    => $batch->id,
                    'created_by'         => $batch->created_by,
                ]);
                $saved++;
            }
            $batch->update(['status' => 'committed', 'success_rows' => $saved, 'preview_data' => null]);
        });

        return ['success' => true, 'saved' => $saved, 'skipped' => $skipped,
                'message' => "Berhasil menyimpan {$saved} dokumen PPN Keluaran."];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EXPORT → Excel DJP
    // ─────────────────────────────────────────────────────────────────────────

    public function generateExportMasukan(string $masaPajak, string $npwpPKP, string $namaPKP): Spreadsheet
    {
        $records = TaxPpnMasukan::where('periode', $masaPajak)->orderBy('tanggal_faktur')->get();
        return $this->buildExcel($records, 'masukan', $masaPajak, $npwpPKP, $namaPKP);
    }

    public function generateExportKeluaran(string $masaPajak, string $npwpPKP, string $namaPKP): Spreadsheet
    {
        $records = TaxPpnKeluaran::where('periode', $masaPajak)->orderBy('tanggal_faktur')->get();
        return $this->buildExcel($records, 'keluaran', $masaPajak, $npwpPKP, $namaPKP);
    }

    private function buildExcel($records, string $jenis, string $masaPajak, string $npwpPKP, string $namaPKP): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(strtoupper($jenis));

        [$year, $month] = explode('-', $masaPajak);
        $sheet->setCellValue('A1', 'NPWP PKP');
        $sheet->setCellValue('B1', $npwpPKP);
        $sheet->setCellValue('C1', 'Nama PKP');
        $sheet->setCellValue('D1', $namaPKP);
        $sheet->setCellValue('E1', 'Masa Pajak');
        $sheet->setCellValue('F1', $month . '/' . $year);

        $isKeluaran = $jenis === 'keluaran';
        $headers = ['No Faktur/Dok', 'Kode Transaksi', 'Kode Dok Lain', 'Tanggal',
                    'NPWP ' . ($isKeluaran ? 'Pembeli' : 'Penjual'),
                    'Nama ' . ($isKeluaran ? 'Pembeli' : 'Penjual'),
                    'Alamat', 'DPP', 'PPN', 'PPnBM', 'Keterangan'];
        if ($isKeluaran) $headers[] = 'Info Tambahan';

        foreach ($headers as $i => $h) {
            $cell = chr(65 + $i) . '3';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        $rowNum = 4;
        foreach ($records as $i => $r) {
            $sheet->setCellValue('A' . $rowNum, $r->no_faktur ?? '');
            $sheet->setCellValue('B' . $rowNum, $r->kode_transaksi ?? '');
            $sheet->setCellValue('C' . $rowNum, $r->kode_dokumen_lain ?? '');
            $sheet->setCellValue('D' . $rowNum, $r->tanggal_faktur?->format('d/m/Y') ?? '');
            $sheet->setCellValue('E' . $rowNum, $isKeluaran ? ($r->npwp_pembeli ?? '') : ($r->npwp_penjual ?? ''));
            $sheet->setCellValue('F' . $rowNum, $isKeluaran ? $r->nama_pembeli : $r->nama_penjual);
            $sheet->setCellValue('G' . $rowNum, $isKeluaran ? ($r->alamat_pembeli ?? '') : ($r->alamat_penjual ?? ''));
            $sheet->setCellValue('H' . $rowNum, (float) $r->dpp);
            $sheet->setCellValue('I' . $rowNum, (float) $r->ppn);
            $sheet->setCellValue('J' . $rowNum, (float) ($r->ppnbm ?? 0));
            $sheet->setCellValue('K' . $rowNum, $r->keterangan ?? '');
            if ($isKeluaran) $sheet->setCellValue('L' . $rowNum, $r->keterangan_tambahan ?? '');

            foreach (['H', 'I', 'J'] as $col) {
                $sheet->getStyle($col . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
            }
            $rowNum++;
        }

        foreach (range('A', $isKeluaran ? 'L' : 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function parseMasa($val): ?string
    {
        $raw = trim((string) $val);
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
