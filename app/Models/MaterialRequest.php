<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequest extends Model
{
    protected $fillable = [
        'request_no', 'project_id', 'requested_by', 'status',
        'approved_by', 'approved_at', 'rejection_reason',
        'converted_po_id', 'urgency', 'needed_by', 'notes', 'is_auto_reorder',
    ];

    protected $casts = [
        'approved_at'    => 'datetime',
        'needed_by'      => 'date',
        'is_auto_reorder' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function convertedPo(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'converted_po_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaterialRequestItem::class);
    }

    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isSubmitted(): bool { return $this->status === 'submitted'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function isConverted(): bool { return $this->status === 'converted'; }

    public function getUrgencyLabelAttribute(): string
    {
        return match ($this->urgency) {
            'low'      => 'Rendah',
            'normal'   => 'Normal',
            'high'     => 'Tinggi',
            'critical' => 'Kritis',
            default    => $this->urgency,
        };
    }

    public function getUrgencyColorAttribute(): string
    {
        return match ($this->urgency) {
            'low'      => 'gray',
            'normal'   => 'blue',
            'high'     => 'yellow',
            'critical' => 'red',
            default    => 'gray',
        };
    }

    public function getTotalEstimatedAttribute(): float
    {
        return (float) $this->items->sum(fn ($i) => ($i->qty_requested ?? 0) * ($i->estimated_price ?? 0));
    }
}
