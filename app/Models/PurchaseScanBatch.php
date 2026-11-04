<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseScanBatch extends Model
{
    protected $fillable = [
        'batch_code',
        'scan_mode',
        'status',
        'supplier_name',
        'supplier_name_normalized',
        'detected_transaction_date',
        'total_files',
        'total_items',
        'grand_total',
        'progress',
        'progress_message',
        'error_message',
        'result_ready',
        'created_by',
        'processed_at',
        'parsed_json',
    ];

    protected $casts = [
        'parsed_json' => 'array',
        'detected_transaction_date' => 'date',
        'processed_at' => 'datetime',
        'result_ready' => 'boolean',
        'progress' => 'integer',
        'total_files' => 'integer',
        'total_items' => 'integer',
        'grand_total' => 'integer',
        'created_by' => 'integer',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(PurchaseScanBatchFile::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}