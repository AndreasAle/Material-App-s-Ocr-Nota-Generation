<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxPpnKeluaran extends Model
{
    protected $table = 'tax_ppn_keluaran';

    protected $fillable = [
        'invoice_id',
        'no_faktur',
        'tanggal_faktur',
        'npwp_pembeli',
        'nama_pembeli',
        'alamat_pembeli',
        'dpp',
        'ppn',
        'periode',
        'status',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_faktur' => 'date',
        'dpp'            => 'decimal:2',
        'ppn'            => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
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
