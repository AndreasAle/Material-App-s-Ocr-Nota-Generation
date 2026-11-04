<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseScanBatchFile extends Model
{
    protected $fillable = [
        'purchase_scan_batch_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'sort_order',
        'status',
        'raw_text',
        'parsed_json',
        'detected_note_number',
        'detected_transaction_date',
        'detected_supplier_name',
        'detected_supplier_name_normalized',
        'grand_total',
        'error_message',
    ];

    protected $casts = [
        'parsed_json' => 'array',
        'detected_transaction_date' => 'date',
        'file_size' => 'integer',
        'sort_order' => 'integer',
        'grand_total' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PurchaseScanBatch::class, 'purchase_scan_batch_id');
    }
}