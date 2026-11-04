<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'material_name_snapshot',
        'unit_name_snapshot',
        'movement_type',
        'qty',
        'unit_price',
        'reference_type',
        'reference_id',
        'project_id',
        'warehouse',
        'warehouse_id',
        'notes',
        'movement_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'qty'           => 'decimal:2',
            'unit_price'    => 'decimal:2',
            'movement_date' => 'date',
            'created_by'    => 'integer',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function warehouseModel(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Signed qty: positif untuk IN, negatif untuk OUT
     */
    public function getSignedQtyAttribute(): float
    {
        return $this->movement_type === 'out'
            ? -(float) $this->qty
            : (float) $this->qty;
    }

    public function isIn(): bool
    {
        return $this->movement_type === 'in';
    }

    public function isOut(): bool
    {
        return $this->movement_type === 'out';
    }
}
