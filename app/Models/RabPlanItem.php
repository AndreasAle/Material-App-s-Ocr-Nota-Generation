<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RabPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'rab_plan_id',
        'material_id',
        'material_name_snapshot',
        'unit_name_snapshot',
        'qty_planned',
        'price_planned',
        'subtotal_planned',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'qty_planned'      => 'decimal:2',
            'price_planned'    => 'decimal:2',
            'subtotal_planned' => 'decimal:2',
        ];
    }

    public function rabPlan()
    {
        return $this->belongsTo(RabPlan::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
