<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'account_code',
        'account_name',
        'debit',
        'credit',
        'description',
        'project_id',
        'line_order',
    ];

    protected function casts(): array
    {
        return [
            'debit'      => 'decimal:2',
            'credit'     => 'decimal:2',
            'line_order' => 'integer',
        ];
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getAmountAttribute(): float
    {
        return (float) $this->debit ?: (float) $this->credit;
    }
}
