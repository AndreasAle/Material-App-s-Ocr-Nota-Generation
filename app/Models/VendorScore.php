<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorScore extends Model
{
    protected $fillable = [
        'supplier_id', 'year', 'month',
        'total_orders', 'on_time_deliveries', 'late_deliveries', 'partial_deliveries',
        'total_qty_ordered', 'total_qty_received', 'total_qty_returned',
        'delivery_score', 'quantity_score', 'quality_score', 'price_score', 'overall_score',
        'grade',
    ];

    protected $casts = [
        'delivery_score' => 'decimal:2',
        'quantity_score' => 'decimal:2',
        'quality_score'  => 'decimal:2',
        'price_score'    => 'decimal:2',
        'overall_score'  => 'decimal:2',
        'total_qty_ordered'  => 'decimal:2',
        'total_qty_received' => 'decimal:2',
        'total_qty_returned' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function getGradeColorAttribute(): string
    {
        return match ($this->grade) {
            'A' => 'green',
            'B' => 'blue',
            'C' => 'yellow',
            'D' => 'orange',
            'F' => 'red',
            default => 'gray',
        };
    }

    public static function computeGrade(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default      => 'F',
        };
    }
}
