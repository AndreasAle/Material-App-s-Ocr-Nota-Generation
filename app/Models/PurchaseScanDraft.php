<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseScanDraft extends Model
{
    protected $fillable = [
        'file_path',
        'file_name',
        'file_size',
        'content_hash',
        'mime_type',
        'raw_text',
        'parsed_json',
        'detected_note_number',
        'detected_transaction_date',
        'status',
        'progress',
        'progress_message',
        'error_message',
        'result_ready',
        'created_by',
    ];

    protected $casts = [
        'parsed_json' => 'array',
        'detected_transaction_date' => 'date',
        'file_size' => 'integer',
        'created_by' => 'integer',
        'progress' => 'integer',
        'result_ready' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}