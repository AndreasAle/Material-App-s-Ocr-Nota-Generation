<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rfq extends Model
{
    protected $fillable = [
        'rfq_no', 'material_request_id', 'project_id', 'created_by',
        'deadline', 'notes', 'terms_and_conditions', 'status',
        'awarded_supplier_id', 'awarded_response_id', 'awarded_at', 'awarded_by',
        'converted_po_id',
    ];

    protected $casts = [
        'deadline'    => 'date',
        'awarded_at'  => 'datetime',
    ];

    public function materialRequest(): BelongsTo   { return $this->belongsTo(MaterialRequest::class); }
    public function project(): BelongsTo           { return $this->belongsTo(Project::class); }
    public function creator(): BelongsTo           { return $this->belongsTo(User::class, 'created_by'); }
    public function awardedSupplier(): BelongsTo   { return $this->belongsTo(Supplier::class, 'awarded_supplier_id'); }
    public function awardedBy(): BelongsTo         { return $this->belongsTo(User::class, 'awarded_by'); }
    public function convertedPo(): BelongsTo       { return $this->belongsTo(PurchaseOrder::class, 'converted_po_id'); }
    public function awardedResponse(): BelongsTo   { return $this->belongsTo(RfqResponse::class, 'awarded_response_id'); }

    public function items(): HasMany      { return $this->hasMany(RfqItem::class); }
    public function responses(): HasMany  { return $this->hasMany(RfqResponse::class); }

    // ── Status helpers ─────────────────────────────────────────────────────
    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isSent(): bool      { return $this->status === 'sent'; }
    public function isClosed(): bool    { return $this->status === 'closed'; }
    public function isAwarded(): bool   { return $this->status === 'awarded'; }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Draft',
            'sent'      => 'Dikirim ke Supplier',
            'closed'    => 'Ditutup',
            'awarded'   => 'Pemenang Dipilih',
            'cancelled' => 'Dibatalkan',
            default     => $this->status,
        };
    }

    public function getResponseCountAttribute(): int
    {
        return $this->responses()->where('status', 'submitted')->count();
    }

    public function isExpired(): bool
    {
        return $this->deadline->isPast() && !in_array($this->status, ['awarded', 'cancelled']);
    }
}
