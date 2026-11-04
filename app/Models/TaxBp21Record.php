<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxBp21Record extends Model
{
    protected $table = 'tax_bp21_records';

    protected $fillable = [
        'no_bukti_potong',
        'masa_pajak',
        'tahun_pajak',
        'pembetulan',
        'npwp_penerima',
        'nik_penerima',
        'nama_penerima',
        'id_tku_penerima',
        'status_ptkp',
        'kode_objek_pajak',
        'nama_objek_pajak',
        'jenis_tarif',
        'penghasilan_bruto',
        'deemed_percent',
        'tarif',
        'pph_dipotong',
        'jenis_dok_referensi',
        'no_dok_referensi',
        'tanggal_dok_referensi',
        'id_tku_pemotong',
        'tanggal_pemotongan',
        'project_id',
        'invoice_id',
        'status',
        'tanggal_setor',
        'ntpn',
        'fasilitas',
        'keterangan',
        'sumber',
        'import_batch_id',
        'created_by',
    ];

    protected $casts = [
        'tanggal_pemotongan'    => 'date',
        'tanggal_dok_referensi' => 'date',
        'tanggal_setor'         => 'date',
        'penghasilan_bruto'     => 'decimal:2',
        'deemed_percent'        => 'decimal:2',
        'tarif'                 => 'decimal:2',
        'pph_dipotong'          => 'decimal:2',
        'tahun_pajak'           => 'integer',
        'pembetulan'            => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(TaxImportBatch::class, 'import_batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getNilaiDibayar(): float
    {
        return (float) $this->penghasilan_bruto - (float) $this->pph_dipotong;
    }

    public function isValid(): bool
    {
        // Cek PPh dipotong = bruto × tarif / 100 (toleransi ±1 rupiah)
        $expectedPph = round((float) $this->penghasilan_bruto * (float) $this->tarif / 100, 0);
        return abs($expectedPph - (float) $this->pph_dipotong) <= 1;
    }
}
