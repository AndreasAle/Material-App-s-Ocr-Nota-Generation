<?php

namespace App\Services\Tax;

/**
 * Referensi lengkap Kode Objek Pajak (KOP) DJP Coretax.
 *
 * Sumber: Sheet REF pada template BPPU Excel to XML v.3 DJP.
 * Mencakup: PPh Final Pasal 4(2), PPh Pasal 23, PPh Pasal 21.
 *
 * Format kode: XX-YYY-ZZ
 *   XX  = Jenis PPh (21, 22, 23, 24, 26, 28=Final 4(2))
 *   YYY = Kategori
 *   ZZ  = Sub-kategori
 */
class TaxKodeObjekPajakReference
{
    // ─────────────────────────────────────────────────────────────────────────
    //  PPh Final Pasal 4 Ayat 2 (kode 28-xxx)
    //  Sumber: PP No. 9 Tahun 2022 + PMK terkait
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * KOP PPh Final 4(2) — untuk BPPU.
     * Format: kode => [nama, tarif, keterangan]
     */
    public static function kodeObjekFinal(): array
    {
        return [
            // ── Jasa Konstruksi (PP No. 9/2022) ──────────────────────────────
            '28-409-01' => ['nama' => 'Pelaksana Konstruksi — Tidak Bersertifikat',          'tarif' => 4.00,  'ket' => 'PP 9/2022 Psl 3(1)a'],
            '28-409-02' => ['nama' => 'Pelaksana Konstruksi — Kualifikasi Kecil',             'tarif' => 1.75,  'ket' => 'PP 9/2022 Psl 3(1)b'],
            '28-409-03' => ['nama' => 'Pelaksana Konstruksi — Kualifikasi Menengah/Besar',   'tarif' => 2.65,  'ket' => 'PP 9/2022 Psl 3(1)c'],
            '28-409-04' => ['nama' => 'Perencana/Pengawas Konstruksi — Tidak Bersertifikat', 'tarif' => 6.00,  'ket' => 'PP 9/2022 Psl 3(1)d'],
            '28-409-05' => ['nama' => 'Perencana/Pengawas Konstruksi — Bersertifikat',       'tarif' => 4.00,  'ket' => 'PP 9/2022 Psl 3(1)e'],
            '28-409-25' => ['nama' => 'Konstruksi Terintegrasi — Bersertifikat',             'tarif' => 2.65,  'ket' => 'PP 9/2022'],
            '28-409-26' => ['nama' => 'Konstruksi Terintegrasi — Tidak Bersertifikat',       'tarif' => 4.00,  'ket' => 'PP 9/2022'],
            '28-409-27' => ['nama' => 'Konsultansi Konstruksi — Bersertifikat',              'tarif' => 3.50,  'ket' => 'PP 9/2022'],
            '28-409-28' => ['nama' => 'Konsultansi Konstruksi — Tidak Bersertifikat',        'tarif' => 6.00,  'ket' => 'PP 9/2022'],

            // ── Properti ──────────────────────────────────────────────────────
            '28-402-01' => ['nama' => 'Pengalihan Hak atas Tanah/Bangunan',                 'tarif' => 2.50,  'ket' => 'PP 34/2016'],
            '28-402-02' => ['nama' => 'Pengalihan Hak Tanah/Bangunan (Rumah Sederhana)',    'tarif' => 1.00,  'ket' => 'PP 34/2016'],
            '28-403-01' => ['nama' => 'Persewaan Tanah/Bangunan (oleh Badan/OP)',           'tarif' => 10.00, 'ket' => 'PP 29/1996 jo PP 5/2002'],
            '28-403-02' => ['nama' => 'Persewaan Tanah/Bangunan',                           'tarif' => 10.00, 'ket' => 'PP 29/1996'],

            // ── Bunga / Obligasi / Deposito ───────────────────────────────────
            '28-401-01' => ['nama' => 'Bunga Deposito/Tabungan/Diskonto SBI',               'tarif' => 20.00, 'ket' => 'PP 131/2000'],
            '28-401-02' => ['nama' => 'Bunga/Diskonto Obligasi',                            'tarif' => 10.00, 'ket' => 'PP 16/2009'],
            '28-401-03' => ['nama' => 'Bunga Simpanan Koperasi',                            'tarif' => 10.00, 'ket' => 'PP 15/2009'],

            // ── Dividen ───────────────────────────────────────────────────────
            '28-407-01' => ['nama' => 'Dividen diterima OP',                                'tarif' => 10.00, 'ket' => 'PP 19/2009'],

            // ── Hadiah / Undian ───────────────────────────────────────────────
            '28-408-01' => ['nama' => 'Hadiah Undian',                                      'tarif' => 25.00, 'ket' => 'PP 132/2000'],

            // ── Penjualan Saham ───────────────────────────────────────────────
            '28-404-01' => ['nama' => 'Penjualan Saham di Bursa Efek',                      'tarif' => 0.10,  'ket' => 'PP 14/1997'],
            '28-404-02' => ['nama' => 'Saham Pendiri Bursa Efek',                           'tarif' => 0.50,  'ket' => 'PP 14/1997'],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PPh Pasal 23 (kode 24-xxx)
    //  Dasar: UU PPh Pasal 23
    // ─────────────────────────────────────────────────────────────────────────

    public static function kodeObjekPph23(): array
    {
        return [
            // ── Dividen, Bunga, Royalti ───────────────────────────────────────
            '24-101-01' => ['nama' => 'Dividen (Badan)',                           'tarif' => 15.00, 'deemed' => null],
            '24-102-01' => ['nama' => 'Bunga (selain deposito/obligasi)',          'tarif' => 15.00, 'deemed' => null],
            '24-103-01' => ['nama' => 'Royalti',                                   'tarif' => 15.00, 'deemed' => null],
            '24-104-01' => ['nama' => 'Hadiah & Penghargaan',                      'tarif' => 15.00, 'deemed' => null],

            // ── Jasa Teknis & Manajemen ───────────────────────────────────────
            '24-100-99' => ['nama' => 'Jasa Lain (tidak ada di daftar)',           'tarif' => 2.00,  'deemed' => null],
            '24-201-01' => ['nama' => 'Jasa Teknik',                               'tarif' => 2.00,  'deemed' => null],
            '24-202-01' => ['nama' => 'Jasa Manajemen',                            'tarif' => 2.00,  'deemed' => null],
            '24-203-01' => ['nama' => 'Jasa Konsultan',                            'tarif' => 2.00,  'deemed' => null],
            '24-204-01' => ['nama' => 'Jasa Akuntansi / Pembukuan / Atestasi',     'tarif' => 2.00,  'deemed' => null],
            '24-205-01' => ['nama' => 'Jasa Hukum / Notaris / Pengacara',          'tarif' => 2.00,  'deemed' => null],
            '24-206-01' => ['nama' => 'Jasa Arsitektur',                           'tarif' => 2.00,  'deemed' => null],
            '24-207-01' => ['nama' => 'Jasa Perencana & Pengawas Konstruksi',      'tarif' => 2.00,  'deemed' => null],
            '24-208-01' => ['nama' => 'Jasa Pemborong / Subkontraktor',            'tarif' => 2.00,  'deemed' => null],
            '24-209-01' => ['nama' => 'Jasa Instalasi / Pemasangan',               'tarif' => 2.00,  'deemed' => null],
            '24-210-01' => ['nama' => 'Jasa Pemeliharaan / Perawatan',             'tarif' => 2.00,  'deemed' => null],
            '24-211-01' => ['nama' => 'Jasa Pengangkutan / Ekspedisi',             'tarif' => 2.00,  'deemed' => null],
            '24-212-01' => ['nama' => 'Jasa Kebersihan',                           'tarif' => 2.00,  'deemed' => null],
            '24-213-01' => ['nama' => 'Jasa Keamanan',                             'tarif' => 2.00,  'deemed' => null],
            '24-214-01' => ['nama' => 'Jasa Katering / Catering',                  'tarif' => 2.00,  'deemed' => null],
            '24-215-01' => ['nama' => 'Jasa Internet',                             'tarif' => 2.00,  'deemed' => null],
            '24-216-01' => ['nama' => 'Jasa Sewa Kendaraan',                       'tarif' => 2.00,  'deemed' => null],
            '24-217-01' => ['nama' => 'Jasa Sewa Peralatan/Mesin',                 'tarif' => 2.00,  'deemed' => null],
            '24-218-01' => ['nama' => 'Jasa Agen Iklan',                           'tarif' => 2.00,  'deemed' => null],
            '24-219-01' => ['nama' => 'Jasa Rekrutmen / Tenaga Kerja',             'tarif' => 2.00,  'deemed' => null],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PPh Pasal 21 (kode 21-xxx)
    //  Regulasi: PMK No. 168/PMK.03/2023 — TER berlaku 1 Jan 2024
    // ─────────────────────────────────────────────────────────────────────────

    public static function kodeObjekBp21(): array
    {
        return [
            // ── Pegawai Tetap ─────────────────────────────────────────────────
            '21-100-01' => ['nama' => 'Penghasilan Pegawai Tetap',                 'jenis_tarif' => 'TER',      'ket' => 'PMK 168/2023'],

            // ── Pegawai Tidak Tetap ───────────────────────────────────────────
            '21-100-02' => ['nama' => 'Upah Pegawai Tidak Tetap (Harian/Mingguan)','jenis_tarif' => 'HARIAN',   'ket' => 'PMK 168/2023'],
            '21-100-03' => ['nama' => 'Penghasilan Pegawai Tidak Tetap (Bulanan)', 'jenis_tarif' => 'PS17',     'ket' => 'PMK 168/2023'],

            // ── Bukan Pegawai ─────────────────────────────────────────────────
            '21-100-04' => ['nama' => 'Honorarium Tenaga Ahli',                    'jenis_tarif' => 'PS17',     'ket' => 'PMK 168/2023', 'deemed' => 50.0],
            '21-100-05' => ['nama' => 'Hadiah/Penghargaan Bukan Pegawai',          'jenis_tarif' => 'PS17',     'ket' => 'PMK 168/2023'],
            '21-100-06' => ['nama' => 'Komisi/Bonus Bukan Pegawai',                'jenis_tarif' => 'PS17',     'ket' => 'PMK 168/2023'],
            '21-100-07' => ['nama' => 'Jasa Bukan Pegawai Berkesinambungan',       'jenis_tarif' => 'PS17',     'ket' => 'PMK 168/2023', 'deemed' => 50.0],
            '21-100-08' => ['nama' => 'Jasa Bukan Pegawai Tidak Berkesinambungan', 'jenis_tarif' => 'PS17',     'ket' => 'PMK 168/2023', 'deemed' => 50.0],

            // ── Pesangon ─────────────────────────────────────────────────────
            '21-401-01' => ['nama' => 'Pesangon Sekaligus ≤ Rp 50 juta',          'jenis_tarif' => 'PESANGON', 'tarif_fix' => 0.00,  'ket' => 'PP 68/2009'],
            '21-401-02' => ['nama' => 'Pesangon Sekaligus Rp 50-100 juta',        'jenis_tarif' => 'PESANGON', 'tarif_fix' => 5.00,  'ket' => 'PP 68/2009'],
            '21-401-03' => ['nama' => 'Pesangon Sekaligus Rp 100-500 juta',       'jenis_tarif' => 'PESANGON', 'tarif_fix' => 15.00, 'ket' => 'PP 68/2009'],
            '21-401-04' => ['nama' => 'Pesangon Sekaligus > Rp 500 juta',         'jenis_tarif' => 'PESANGON', 'tarif_fix' => 25.00, 'ket' => 'PP 68/2009'],

            // ── Pensiun ───────────────────────────────────────────────────────
            '21-402-01' => ['nama' => 'Pensiun Bulanan',                           'jenis_tarif' => 'TER',      'ket' => 'PMK 168/2023'],
            '21-402-02' => ['nama' => 'Uang Pensiun Sekaligus ≤ Rp 50 juta',      'jenis_tarif' => 'PENSIUN',  'tarif_fix' => 0.00,  'ket' => 'PP 68/2009'],
            '21-402-03' => ['nama' => 'Uang Pensiun Sekaligus Rp 50-100 juta',    'jenis_tarif' => 'PENSIUN',  'tarif_fix' => 5.00,  'ket' => 'PP 68/2009'],
            '21-402-04' => ['nama' => 'Uang Pensiun Sekaligus > Rp 100 juta',     'jenis_tarif' => 'PENSIUN',  'tarif_fix' => 15.00, 'ket' => 'PP 68/2009'],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helper Methods
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Cari KOP berdasarkan kode (lintas semua jenis).
     */
    public static function find(string $kode): ?array
    {
        $all = array_merge(
            self::kodeObjekFinal(),
            self::kodeObjekPph23(),
            self::kodeObjekBp21()
        );
        return $all[$kode] ?? null;
    }

    /**
     * Nama KOP atau kode aslinya jika tidak ditemukan.
     */
    public static function namaKop(string $kode): string
    {
        $item = self::find($kode);
        return $item['nama'] ?? $kode;
    }

    /**
     * Tarif default dari KOP (untuk auto-fill).
     */
    public static function tarifDefault(string $kode): ?float
    {
        $item = self::find($kode);
        return $item['tarif'] ?? $item['tarif_fix'] ?? null;
    }

    /**
     * Mapping otomatis jenis_usaha + kualifikasi → KOP Final terbaik.
     * Digunakan untuk auto-suggest saat input PPh Final.
     */
    public static function kopDariKonstruksi(string $jenisUsaha, string $kualifikasi): string
    {
        if ($jenisUsaha === 'pelaksana') {
            return match($kualifikasi) {
                'kecil'              => '28-409-02',
                'menengah', 'besar'  => '28-409-03',
                default              => '28-409-01', // tidak_bersertifikat
            };
        }

        if (in_array($jenisUsaha, ['perencana', 'pengawas'])) {
            return $kualifikasi === 'tidak_bersertifikat' ? '28-409-04' : '28-409-05';
        }

        // konsultansi konstruksi
        return $kualifikasi === 'tidak_bersertifikat' ? '28-409-28' : '28-409-27';
    }

    /**
     * Semua KOP yang relevan untuk konstruksi (untuk filter dropdown).
     */
    public static function kopKonstruksi(): array
    {
        $final = self::kodeObjekFinal();
        return array_filter($final, fn($v, $k) => str_starts_with($k, '28-409'), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Semua KOP PPh Final sebagai select options [kode => label].
     */
    public static function selectOptionsFinal(): array
    {
        return array_map(
            fn($v, $k) => ['kode' => $k, 'label' => $k . ' — ' . $v['nama'], 'tarif' => $v['tarif']],
            self::kodeObjekFinal(),
            array_keys(self::kodeObjekFinal())
        );
    }

    /**
     * Semua KOP PPh 23 sebagai select options.
     */
    public static function selectOptionsPph23(): array
    {
        return array_map(
            fn($v, $k) => ['kode' => $k, 'label' => $k . ' — ' . $v['nama'], 'tarif' => $v['tarif']],
            self::kodeObjekPph23(),
            array_keys(self::kodeObjekPph23())
        );
    }

    /**
     * Semua KOP BP21 sebagai select options.
     */
    public static function selectOptionsBp21(): array
    {
        return array_map(
            fn($v, $k) => ['kode' => $k, 'label' => $k . ' — ' . $v['nama'], 'jenis_tarif' => $v['jenis_tarif']],
            self::kodeObjekBp21(),
            array_keys(self::kodeObjekBp21())
        );
    }

    /**
     * Data tarif lengkap dalam format JSON-friendly (untuk Alpine.js).
     */
    public static function allAsJson(): string
    {
        $result = [];
        foreach (self::kodeObjekFinal() as $k => $v) {
            $result[$k] = ['nama' => $v['nama'], 'tarif' => $v['tarif'], 'jenis' => 'final'];
        }
        foreach (self::kodeObjekPph23() as $k => $v) {
            $result[$k] = ['nama' => $v['nama'], 'tarif' => $v['tarif'], 'jenis' => 'pph23'];
        }
        foreach (self::kodeObjekBp21() as $k => $v) {
            $result[$k] = ['nama' => $v['nama'], 'jenis_tarif' => $v['jenis_tarif'], 'jenis' => 'bp21',
                          'deemed' => $v['deemed'] ?? null];
        }
        return json_encode($result);
    }
}
