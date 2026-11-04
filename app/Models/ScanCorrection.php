<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanCorrection extends Model
{
    protected $fillable = [
        'source_text',
        'source_text_normalized',
        'material_id',
        'corrected_material_name',
        'corrected_unit_name',
        'correction_type',
        'corrected_by',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'usage_count' => 'integer',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function corrector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
}
