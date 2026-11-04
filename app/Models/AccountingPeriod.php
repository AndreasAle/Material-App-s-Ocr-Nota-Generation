<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingPeriod extends Model
{
    protected $fillable = [
        'fiscal_year_id',
        'period_month',
        'period_year',
        'start_date',
        'end_date',
        'status',
        'closed_at',
        'closed_by',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year'  => 'integer',
            'start_date'   => 'date',
            'end_date'     => 'date',
            'closed_at'    => 'datetime',
        ];
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return ($months[$this->period_month] ?? $this->period_month) . ' ' . $this->period_year;
    }
}
