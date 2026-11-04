<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'project_id',
        'client_name',
        'client_address',
        'client_phone',
        'invoice_date',
        'due_date',
        'payment_status',
        'subtotal',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'grand_total',
        'paid_amount',
        'notes',
        'created_by',
        // Accounting fields (added in 2026_05_30 migration)
        'dpp',
        'pph_final_rate',
        'pph_final_amount',
        'doc_status',
        'posted_at',
        'posted_by',
        'journal_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date'    => 'date',
            'due_date'        => 'date',
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_percent'     => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'grand_total'     => 'decimal:2',
            'paid_amount'       => 'decimal:2',
            'dpp'               => 'decimal:2',
            'pph_final_rate'    => 'decimal:2',
            'pph_final_amount'  => 'decimal:2',
            'posted_at'         => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    // -----------------------------------------------------------------------

    public function isUnpaid(): bool
    {
        return $this->payment_status === 'unpaid';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === 'partial';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->grand_total - (float) $this->paid_amount);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)->orderBy('payment_date');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    // -----------------------------------------------------------------------

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->payment_status !== 'paid'
            && $this->due_date->isPast();
    }
}
