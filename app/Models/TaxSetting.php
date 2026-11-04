<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    protected $table = 'tax_settings';

    protected $fillable = [
        'is_pkp',
        'kualifikasi_konstruksi',
        'jenis_usaha',
        'npwp',
        'nama_wp',
        'alamat_wp',
        'klu',
        'kpp',
    ];

    protected $casts = [
        'is_pkp' => 'boolean',
    ];

    /** Ambil setting (selalu ada 1 record, auto-create jika belum ada) */
    public static function getSetting(): self
    {
        return static::firstOrCreate([], [
            'is_pkp'                  => false,
            'kualifikasi_konstruksi'  => 'tidak_bersertifikat',
            'jenis_usaha'             => 'pelaksana',
        ]);
    }

    public function getLabelKualifikasi(): string
    {
        return match($this->kualifikasi_konstruksi) {
            'kecil'     => 'Kecil (SBU Kecil)',
            'menengah'  => 'Menengah (SBU Menengah)',
            'besar'     => 'Besar (SBU Besar)',
            default     => 'Tidak Bersertifikat',
        };
    }

    public function getLabelJenisUsaha(): string
    {
        return match($this->jenis_usaha) {
            'pelaksana'   => 'Pelaksana Konstruksi',
            'perencana'   => 'Perencana Konstruksi',
            'pengawas'    => 'Pengawas Konstruksi',
            'konsultansi' => 'Konsultansi Teknis',
            default       => $this->jenis_usaha,
        };
    }
}
