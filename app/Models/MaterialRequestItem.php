<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequestItem extends Model
{
    protected $fillable = [
        'material_request_id', 'material_id', 'material_name_snapshot',
        'unit_name_snapshot', 'qty_requested', 'estimated_price', 'notes',
    ];

    protected $casts = [
        'qty_requested'   => 'decimal:2',
        'estimated_price' => 'decimal:2',
    ];

    public function materialRequest(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->qty_requested * (float) ($this->estimated_price ?? 0);
    }
}
