<?php

namespace App\Services\Tax;

use Carbon\Carbon;

/**
 * TaxCalculatorService
 *
 * Seluruh logika kalkulasi pajak berdasarkan regulasi yang berlaku:
 * - PPh Final Jasa Konstruksi : PP No. 9 Tahun 2022
 * - PPN                       : PMK No. 131 Tahun 2024 — tarif 12% berlaku 1 Jan 2025
 * - PPh Pasal 23               : UU PPh Pasal 23
 * - KAP & KJS                 : Kode Akun Pajak resmi DJP
 * - Jatuh tempo               : PMK & KUP
 */
class TaxCalculatorService
{
    /**
     * Tarif PPN 12% — PMK No. 131 Tahun 2024
     * Berlaku mulai 1 Januari 2025 (naik dari 11% sesuai UU HPP No. 7/2021)
     */
    const TARIF_PPN = 12.0;

    // ─────────────────────────────────────────────────
    //  PPh Final Pasal 4 Ayat 2 — Jasa Konstruksi
    //  Dasar: PP No. 9 Tahun 2022
    // ─────────────────────────────────────────────────

    /**
     * Ambil tarif PPh Final sesuai jenis usaha + kualifikasi SBU
     *
     * @param  string  $jenisUsaha   pelaksana|perencana|pengawas|konsultansi
     * @param  string  $kualifikasi  kecil|menengah|besar|tidak_bersertifikat
     * @return float   Tarif dalam persen (mis: 1.75, 2.65, 4.00, 6.00)
     */
    public static function getTarifPphFinal(string $jenisUsaha, string $kualifikasi): float
    {
        if ($jenisUsaha === 'pelaksana') {
            return match($kualifikasi) {
                'kecil'              => 1.75,
                'menengah', 'besar'  => 2.65,
                default              => 4.00, // tidak_bersertifikat
            };
        }

        // perencana, pengawas, konsultansi teknis
        return $kualifikasi === 'tidak_bersertifikat' ? 6.00 : 4.00;
    }

    /**
     * Hitung PPh Final yang dipotong klien
     *
     * @param  float   $nilaiKontrak  DPP (nilai bruto kontrak)
     * @param  float   $tarif         Tarif dalam persen
     * @return float
     */
    public static function hitungPphFinal(float $nilaiKontrak, float $tarif): float
    {
        return round($nilaiKontrak * $tarif / 100, 0);
    }

    // ─────────────────────────────────────────────────
    //  PPh Pasal 23
    //  Dasar: UU PPh Pasal 23 ayat (1) huruf c
    // ─────────────────────────────────────────────────

    /**
     * Tarif PPh 23: 2% jika penerima punya NPWP, 4% jika tidak
     */
    public static function getTarifPph23(?string $npwp): float
    {
        return empty(trim((string) $npwp)) ? 4.00 : 2.00;
    }

    /**
     * Hitung PPh Pasal 23
     */
    public static function hitungPph23(float $bruto, float $tarif): float
    {
        return round($bruto * $tarif / 100, 0);
    }

    // ─────────────────────────────────────────────────
    //  PPN
    // ─────────────────────────────────────────────────

    /** Hitung PPN dari DPP */
    public static function hitungPpn(float $dpp): float
    {
        return round($dpp * self::TARIF_PPN / 100, 0);
    }

    /** Hitung DPP dari nilai sudah termasuk PPN (gross-up) */
    public static function hitungDppDariTotal(float $total): float
    {
        return round($total / (1 + self::TARIF_PPN / 100), 0);
    }

    // ─────────────────────────────────────────────────
    //  Jatuh Tempo Setoran
    //  Sumber: PMK & KUP
    // ─────────────────────────────────────────────────

    /**
     * Hitung jatuh tempo setoran berdasarkan jenis pajak dan periode
     *
     * PPh 21, 23, Final 4(2) : tanggal 10 bulan berikutnya
     * PPN (penyetoran)        : tanggal 25 bulan berikutnya
     *
     * @param  string  $periode    Format: YYYY-MM (misal: 2026-05)
     * @param  string  $jenisPajak ppn|pph21|pph23|pph_final_4_2
     */
    public static function getJatuhTempo(string $periode, string $jenisPajak): Carbon
    {
        [$year, $month] = explode('-', $periode);
        $nextMonth = Carbon::create((int) $year, (int) $month, 1)->addMonth();

        return match($jenisPajak) {
            'ppn'   => $nextMonth->copy()->setDay(25),
            default => $nextMonth->copy()->setDay(10),
        };
    }

    // ─────────────────────────────────────────────────
    //  Kode Akun Pajak (KAP) & Kode Jenis Setoran (KJS)
    //  Sumber: Peraturan Dirjen Pajak resmi DJP
    // ─────────────────────────────────────────────────

    /**
     * Ambil KAP dan KJS berdasarkan jenis pajak
     *
     * @return array{kap: string, kjs: string, uraian: string}
     */
    public static function getKapKjs(string $jenisPajak): array
    {
        return match($jenisPajak) {
            'ppn'           => [
                'kap'    => '411211',
                'kjs'    => '100',
                'uraian' => 'PPN Dalam Negeri',
            ],
            'pph21'         => [
                'kap'    => '411121',
                'kjs'    => '100',
                'uraian' => 'PPh Pasal 21 Masa',
            ],
            'pph23'         => [
                'kap'    => '411124',
                'kjs'    => '104',
                'uraian' => 'PPh Pasal 23 atas Jasa',
            ],
            'pph_final_4_2' => [
                'kap'    => '411128',
                'kjs'    => '409',
                'uraian' => 'PPh Final Jasa Konstruksi',
            ],
            default => ['kap' => '', 'kjs' => '', 'uraian' => ''],
        };
    }

    // ─────────────────────────────────────────────────
    //  Label helpers
    // ─────────────────────────────────────────────────

    public static function labelJenisUsaha(string $jenis): string
    {
        return match($jenis) {
            'pelaksana'   => 'Pelaksana Konstruksi',
            'perencana'   => 'Perencana Konstruksi',
            'pengawas'    => 'Pengawas Konstruksi',
            'konsultansi' => 'Konsultansi Teknis',
            default       => $jenis,
        };
    }

    public static function labelKualifikasi(string $kual): string
    {
        return match($kual) {
            'kecil'               => 'Kecil (SBU Kecil)',
            'menengah'            => 'Menengah (SBU Menengah)',
            'besar'               => 'Besar (SBU Besar)',
            'tidak_bersertifikat' => 'Tidak Bersertifikat',
            default               => $kual,
        };
    }

    /** Daftar jenis penghasilan umum untuk PPh 23 */
    public static function daftarJenisPenghasilan(): array
    {
        return [
            'Jasa Konstruksi (Subkontraktor)',
            'Jasa Teknik',
            'Jasa Manajemen',
            'Jasa Konsultan',
            'Jasa Instalasi / Pemasangan',
            'Jasa Pemeliharaan / Perawatan',
            'Jasa Pengiriman / Ekspedisi',
            'Jasa Lainnya',
        ];
    }

    /** Format periode YYYY-MM ke tampilan "Mei 2026" */
    public static function formatPeriode(string $periode): string
    {
        [$year, $month] = explode('-', $periode);
        $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return ($bulan[(int) $month] ?? $month) . ' ' . $year;
    }
}
