<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'material_id', 'material_name_snapshot', 'unit_name', 'factor',
        'qty', 'qty_base', 'price', 'cost_price', 'subtotal', 'qty_returned',
    ];

    protected function casts(): array
    {
        return [
            'factor'       => 'decimal:4',
            'qty'          => 'decimal:2',
            'qty_base'     => 'decimal:4',
            'price'        => 'decimal:2',
            'cost_price'   => 'decimal:2',
            'subtotal'     => 'decimal:2',
            'qty_returned' => 'decimal:2',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function returnableQty(): float
    {
        return max(0, (float) $this->qty - (float) $this->qty_returned);
    }
}
