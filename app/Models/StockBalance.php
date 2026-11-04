<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBalance extends Model
{
    protected $fillable = [
        'material_id',
        'warehouse_id',
        'qty_on_hand',
        'avg_cost',
        'total_value',
        'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'qty_on_hand'     => 'decimal:2',
            'avg_cost'        => 'decimal:2',
            'total_value'     => 'decimal:2',
            'last_updated_at' => 'datetime',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
