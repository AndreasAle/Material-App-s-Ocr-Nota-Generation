<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'return_no', 'purchase_transaction_id', 'goods_receipt_id',
        'supplier_id', 'project_id', 'return_date', 'reason', 'reason_detail',
        'status', 'credit_note_no', 'credit_note_date', 'credit_amount',
        'approved_by', 'approved_at', 'rejection_reason', 'created_by', 'notes',
    ];

    protected $casts = [
        'return_date'      => 'date',
        'credit_note_date' => 'date',
        'approved_at'      => 'datetime',
        'credit_amount'    => 'decimal:2',
    ];

    public function purchaseTransaction(): BelongsTo
    {
        return $this->belongsTo(PurchaseTransaction::class);
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    // ── Status helpers ─────────────────────────────────────────────────────
    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isSubmitted(): bool { return $this->status === 'submitted'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isReturned(): bool  { return $this->status === 'returned'; }
    public function isCredited(): bool  { return $this->status === 'credited'; }

    // ── Labels ─────────────────────────────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Draft',
            'submitted' => 'Menunggu Persetujuan',
            'approved'  => 'Disetujui',
            'returned'  => 'Barang Dikembalikan',
            'credited'  => 'Credit Note Diterima',
            default     => $this->status,
        };
    }

    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'wrong_item'       => 'Barang Salah',
            'damaged'          => 'Barang Rusak',
            'quantity_excess'  => 'Kelebihan Qty',
            'quality_reject'   => 'Tolak Mutu',
            'spec_mismatch'    => 'Spesifikasi Tidak Sesuai',
            'other'            => 'Lainnya',
            default            => $this->reason,
        };
    }

    public function getTotalReturnValueAttribute(): float
    {
        return (float) $this->items->sum('subtotal');
    }
}
