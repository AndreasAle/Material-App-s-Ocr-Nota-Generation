<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    protected $fillable = [
        'purchase_transaction_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_no',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount'       => 'decimal:2',
        ];
    }

    public function purchaseTransaction(): BelongsTo
    {
        return $this->belongsTo(PurchaseTransaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
