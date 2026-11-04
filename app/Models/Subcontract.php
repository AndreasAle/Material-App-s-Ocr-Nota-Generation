<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcontract extends Model
{
    use HasFactory;

    protected $fillable = [
        'spk_no', 'subcontractor_id', 'project_id', 'work_name',
        'scope_of_work', 'start_date', 'end_date', 'contract_value',
        'paid_amount', 'progress_pct', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'end_date'       => 'date',
        'contract_value' => 'decimal:2',
        'paid_amount'    => 'decimal:2',
        'progress_pct'   => 'integer',
    ];

    public function subcontractor(): BelongsTo
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubcontractPayment::class);
    }

    public function getRemainingValueAttribute(): float
    {
        return (float) $this->contract_value - (float) $this->paid_amount;
    }

    public function getPaymentPctAttribute(): float
    {
        if ($this->contract_value <= 0) return 0;
        return round(($this->paid_amount / $this->contract_value) * 100, 1);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'     => 'Draft',
            'active'    => 'Aktif',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default     => $this->status,
        };
    }

    public static function generateSpkNo(): string
    {
        $prefix = 'SPK-' . date('Ym') . '-';
        $last = static::where('spk_no', 'like', $prefix . '%')
            ->orderByDesc('spk_no')->first();
        $seq = $last ? ((int) substr($last->spk_no, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
