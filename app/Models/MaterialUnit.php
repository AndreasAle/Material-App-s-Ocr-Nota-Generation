<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satuan tambahan material: 1 unit_name = factor × satuan dasar.
 */
class MaterialUnit extends Model
{
    protected $fillable = ['material_id', 'unit_name', 'factor', 'sell_price'];

    protected function casts(): array
    {
        return [
            'factor'     => 'decimal:4',
            'sell_price' => 'decimal:2',
        ];
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
