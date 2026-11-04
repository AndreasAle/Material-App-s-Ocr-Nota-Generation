<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_id',
        'purchase_transaction_item_id',
        'purchase_order_item_id',
        'quality_status',
        'material_name_snapshot',
        'unit_name_snapshot',
        'qty_ordered',
        'qty_received',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'qty_ordered'  => 'decimal:2',
            'qty_received' => 'decimal:2',
        ];
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseTransactionItem()
    {
        return $this->belongsTo(PurchaseTransactionItem::class);
    }
}
