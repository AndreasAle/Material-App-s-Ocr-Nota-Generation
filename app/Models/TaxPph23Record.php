<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxPph23Record extends Model
{
    protected $table = 'tax_pph23_records';

    protected $fillable = [
        'purchase_transaction_id',
        'nama_penerima',
        'npwp_penerima',
        'jenis_penghasilan',
        'kode_objek_pajak',
        'nama_objek_pajak',
        'id_tku_pemotong',
        'id_tku_penerima',
        'bruto',
        'tarif',
        'fasilitas',
        'deemed_percent',
        'pph_dipotong',
        'no_bukti_potong',
        'tanggal_bukti_potong',
        'tanggal_pemotongan',
        'periode',
        'status',
        'tanggal_setor',
        'ntpn',
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
        'tanggal_setor'         => 'date',
        'tanggal_pemotongan'    => 'date',
        'tanggal_dok_referensi' => 'date',
        'bruto'                 => 'decimal:2',
        'tarif'                 => 'decimal:2',
        'deemed_percent'        => 'decimal:2',
        'pph_dipotong'          => 'decimal:2',
    ];

    public function purchaseTransaction(): BelongsTo
    {
        return $this->belongsTo(PurchaseTransaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(TaxImportBatch::class, 'import_batch_id');
    }

    /** Nilai yang ditransfer ke penerima setelah PPh dipotong */
    public function getNilaiDibayar(): float
    {
        return (float) $this->bruto - (float) $this->pph_dipotong;
    }

    public function isPunyaNpwp(): bool
    {
        return !empty(trim((string) $this->npwp_penerima));
    }
}
