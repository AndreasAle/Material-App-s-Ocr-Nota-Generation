<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'code',
        'name',
        'account_type',
        'account_subtype',
        'normal_balance',
        'parent_id',
        'level',
        'is_header',
        'is_active',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'level'      => 'integer',
            'sort_order' => 'integer',
            'is_header'  => 'boolean',
            'is_active'  => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id')->orderBy('code');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    // -----------------------------------------------------------------------

    public function isDebitNormal(): bool
    {
        return $this->normal_balance === 'debit';
    }

    public function getTypeLabel(): string
    {
        return match($this->account_type) {
            'asset'     => 'Aset',
            'liability' => 'Kewajiban',
            'equity'    => 'Ekuitas',
            'revenue'   => 'Pendapatan',
            'expense'   => 'Beban',
            default     => $this->account_type,
        };
    }
}
