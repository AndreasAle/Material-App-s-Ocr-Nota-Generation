<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfqResponse extends Model
{
    protected $fillable = [
        'rfq_id', 'supplier_id', 'status', 'valid_until',
        'supplier_ref_no', 'payment_terms', 'delivery_days', 'notes',
        'total_price', 'submitted_at',
    ];

    protected $casts = [
        'valid_until'   => 'date',
        'submitted_at'  => 'datetime',
        'total_price'   => 'decimal:2',
    ];

    public function rfq(): BelongsTo      { return $this->belongsTo(Rfq::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function items(): HasMany      { return $this->hasMany(RfqResponseItem::class); }

    public function isSubmitted(): bool { return $this->status === 'submitted'; }
    public function isAccepted(): bool  { return $this->status === 'accepted'; }

    public function getPaymentTermsLabelAttribute(): string
    {
        return match ($this->payment_terms) {
            'cash'  => 'Tunai',
            'net7'  => 'NET 7 hari',
            'net14' => 'NET 14 hari',
            'net30' => 'NET 30 hari',
            'net45' => 'NET 45 hari',
            'net60' => 'NET 60 hari',
            'dp50'  => 'DP 50%',
            'dp30'  => 'DP 30%',
            'custom'=> 'Sesuai Negosiasi',
            default => (string) $this->payment_terms,
        };
    }
}
