<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_no',
        'transaction_date',
        'supplier_id',
        'project_id',
        'purchase_order_id',
        'user_id',
        'note_number',
        'supplier_invoice_no',
        'supplier_invoice_date',
        'goods_receipt_status',
        'grand_total',
        'attachment_count',
        'status',
        'notes',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        // Payment / accounting fields (added in 2026_05_30 migration)
        'subtotal',
        'discount_amount',
        'dpp',
        'ppn_rate',
        'ppn_amount',
        'due_date',
        'paid_amount',
        'remaining_amount',
        'payment_status',
        'posted_at',
        'posted_by',
        'journal_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date'       => 'date',
        'supplier_invoice_date'  => 'date',
            'grand_total'      => 'decimal:2',
            'attachment_count' => 'integer',
            'approved_at'      => 'datetime',
            'due_date'         => 'date',
            'subtotal'         => 'decimal:2',
            'discount_amount'  => 'decimal:2',
            'dpp'              => 'decimal:2',
            'ppn_rate'         => 'decimal:2',
            'ppn_amount'       => 'decimal:2',
            'paid_amount'      => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'posted_at'        => 'datetime',
        ];
    }

    /**
     * Transaksi yang dihitung di laporan: semua kecuali yang ditolak (draft & pending ikut).
     */
    public function scopeNotRejected($query)
    {
        return $query->where(fn ($q) => $q->whereNull('approval_status')->orWhere('approval_status', '!=', 'rejected'));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseTransactionItem::class);
    }

    public function attachments()
    {
        return $this->hasMany(PurchaseAttachment::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function ppnMasukan()
    {
        return $this->hasOne(TaxPpnMasukan::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    // -----------------------------------------------------------------------

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === 'partial';
    }

    public function isUnpaid(): bool
    {
        return $this->payment_status === 'unpaid';
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->payment_status !== 'paid'
            && $this->due_date->isPast();
    }
}