<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnItem extends Model
{
    protected $fillable = [
        'purchase_return_id', 'purchase_transaction_item_id',
        'material_name_snapshot', 'unit_name_snapshot',
        'qty_returned', 'unit_price', 'subtotal', 'notes',
    ];

    protected $casts = [
        'qty_returned' => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'subtotal'     => 'decimal:2',
    ];

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function originalItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseTransactionItem::class, 'purchase_transaction_item_id');
    }
}
