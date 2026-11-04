<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PurchaseAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_transaction_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function purchaseTransaction()
    {
        return $this->belongsTo(PurchaseTransaction::class);
    }

    /**
     * Lampiran baru ada di disk privat; lampiran lama masih di disk public.
     */
    public function storageDisk(): string
    {
        return Storage::disk('private')->exists($this->file_path) ? 'private' : 'public';
    }

    public function getUrlAttribute(): string
    {
        return route('purchase-transactions.attachments.show', $this);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}