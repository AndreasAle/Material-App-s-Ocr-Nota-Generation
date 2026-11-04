<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubcontractPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcontract_id', 'payment_date', 'amount',
        'payment_method', 'reference_no', 'notes', 'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function subcontract(): BelongsTo
    {
        return $this->belongsTo(Subcontract::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
