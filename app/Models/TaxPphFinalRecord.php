<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxPphFinalRecord extends Model
{
    protected $table = 'tax_pph_final_records';

    protected $fillable = [
        'invoice_id',
        'project_id',
        'nama_pemotong',
        'npwp_pemotong',
        'npwp_penerima',
        'nik_penerima',
        'nama_penerima',
        'id_tku_pemotong',
        'id_tku_penerima',
        'no_bukti_potong',
        'tanggal_bukti_potong',
        'tanggal_pemotongan',
        'jenis_usaha',
        'kualifikasi',
        'kode_objek_pajak',
        'nama_objek_pajak',
        'nilai_kontrak',
        'tarif',
        'fasilitas',
        'deemed_percent',
        'pph_dipotong',
        'periode',
        'status',
        'keterangan',
        'jenis_dok_referensi',
        'no_dok_referensi',
        'tanggal_dok_referensi',
        'sumber',
        'import_batch_id',
        'created_by',
    ];

    protected $casts = [
        'tanggal_bukti_potong'  => 'date',
        'tanggal_pemotongan'    => 'date',
        'tanggal_dok_referensi' => 'date',
        'nilai_kontrak'         => 'decimal:2',
        'tarif'                 => 'decimal:2',
        'deemed_percent'        => 'decimal:2',
        'pph_dipotong'          => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(TaxImportBatch::class, 'import_batch_id');
    }

    public function getLabelJenisUsaha(): string
    {
        return match($this->jenis_usaha) {
            'pelaksana'   => 'Pelaksana',
            'perencana'   => 'Perencana',
            'pengawas'    => 'Pengawas',
            'konsultansi' => 'Konsultansi',
            default       => $this->jenis_usaha,
        };
    }

    public function getLabelKualifikasi(): string
    {
        return match($this->kualifikasi) {
            'kecil'    => 'Kecil',
            'menengah' => 'Menengah',
            'besar'    => 'Besar',
            default    => 'Tidak Bersertifikat',
        };
    }

    /** Nilai bersih yang diterima setelah PPh dipotong */
    public function getNilaiDiterima(): float
    {
        return (float) $this->nilai_kontrak - (float) $this->pph_dipotong;
    }
}
