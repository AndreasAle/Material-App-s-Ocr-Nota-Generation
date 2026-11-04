<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_no',
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'total_debit',
        'total_credit',
        'status',
        'currency',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date'   => 'date',
            'total_debit'  => 'decimal:2',
            'total_credit' => 'decimal:2',
            'posted_at'    => 'datetime',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class)->orderBy('line_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    // -----------------------------------------------------------------------

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isBalanced(): bool
    {
        return abs((float) $this->total_debit - (float) $this->total_credit) < 0.01;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'  => 'Draft',
            'posted' => 'Diposting',
            'void'   => 'Dibatalkan',
            default  => $this->status,
        };
    }
}
