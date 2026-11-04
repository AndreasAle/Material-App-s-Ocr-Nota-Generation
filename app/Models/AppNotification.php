<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'data', 'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    // Icon and color helpers for UI
    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'approval_request'  => '📋',
            'approval_result'   => '✅',
            'rejection_result'  => '❌',
            'low_stock'         => '⚠️',
            'budget_alert'      => '💰',
            'invoice_due'       => '🔔',
            'invoice_overdue'   => '🚨',
            'goods_receipt'     => '📦',
            'po_status'         => '📄',
            'material_request'  => '📝',
            default             => '🔔',
        };
    }

    public function getColorClassAttribute(): string
    {
        return match ($this->type) {
            'approval_request', 'material_request' => 'blue',
            'approval_result', 'goods_receipt'     => 'green',
            'rejection_result'                      => 'red',
            'low_stock', 'budget_alert'             => 'yellow',
            'invoice_due'                           => 'orange',
            'invoice_overdue'                       => 'red',
            'po_status'                             => 'purple',
            default                                 => 'gray',
        };
    }
}
