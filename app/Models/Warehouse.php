<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = [
        'name', 'code', 'location', 'manager_name', 'phone', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Total current stock across all materials in this warehouse.
     */
    public function totalMovementsIn(): float
    {
        return (float) $this->stockMovements()
            ->whereIn('movement_type', ['in', 'adjustment'])
            ->sum('qty');
    }
}
