<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'purchase_transaction_id',
        'supplier_invoice_id',
        'receipt_no',
        'supplier_do_no',
        'supplier_do_date',
        'receipt_date',
        'received_by',
        'inspector_name',
        'inspection_status',
        'inspection_notes',
        'notes',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'receipt_date'    => 'date',
            'supplier_do_date'=> 'date',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseTransaction()
    {
        return $this->belongsTo(PurchaseTransaction::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    /** Total qty di GR ini yang sudah diretur */
    public function getTotalReturnedAttribute(): float
    {
        return (float) $this->items->sum('qty_returned');
    }

    /** Apakah GR ini perlu dikaitkan ke transaction (3-way match) */
    public function needsInvoiceMatch(): bool
    {
        return $this->isComplete() && is_null($this->purchase_transaction_id);
    }
}
