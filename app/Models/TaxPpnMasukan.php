<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxPpnMasukan extends Model
{
    protected $table = 'tax_ppn_masukan';

    protected $fillable = [
        'purchase_transaction_id',
        'supplier_id',
        'no_faktur',
        'tanggal_faktur',
        'npwp_penjual',
        'nama_penjual',
        'dpp',
        'ppn',
        'periode',
        'status',
        'dapat_dikreditkan',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_faktur'     => 'date',
        'dpp'                => 'decimal:2',
        'ppn'                => 'decimal:2',
        'dapat_dikreditkan'  => 'boolean',
    ];

    public function purchaseTransaction(): BelongsTo
    {
        return $this->belongsTo(PurchaseTransaction::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotal(): float
    {
        return (float) $this->dpp + (float) $this->ppn;
    }
}
