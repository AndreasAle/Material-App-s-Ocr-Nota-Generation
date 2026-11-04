<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id', 'material_id',
        'material_name_snapshot', 'unit_name_snapshot',
        'qty', 'unit_price', 'subtotal', 'notes',
        'qty_received', 'qty_pending',
    ];

    protected function casts(): array
    {
        return [
            'qty'          => 'decimal:2',
            'unit_price'   => 'decimal:2',
            'subtotal'     => 'decimal:2',
            'qty_received' => 'decimal:2',
            'qty_pending'  => 'decimal:2',
        ];
    }

    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function material()      { return $this->belongsTo(Material::class); }

    public function getQtyRemainingAttribute(): float
    {
        return max(0, (float) $this->qty - (float) $this->qty_received);
    }

    public function getReceivePercentAttribute(): float
    {
        return $this->qty > 0 ? ((float) $this->qty_received / (float) $this->qty) * 100 : 0;
    }

    public function isFullyReceived(): bool
    {
        return (float) $this->qty_received >= (float) $this->qty;
    }
}
