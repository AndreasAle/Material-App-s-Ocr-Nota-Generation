<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'sale_no', 'sale_date', 'customer_id', 'user_id',
        'subtotal', 'discount_amount', 'grand_total', 'cost_total', 'returned_total',
        'paid_amount', 'cash_received', 'change_amount', 'remaining_amount',
        'payment_method', 'payment_status', 'due_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'sale_date'        => 'datetime',
            'due_date'         => 'date',
            'subtotal'         => 'decimal:2',
            'discount_amount'  => 'decimal:2',
            'grand_total'      => 'decimal:2',
            'cost_total'       => 'decimal:2',
            'returned_total'   => 'decimal:2',
            'paid_amount'      => 'decimal:2',
            'cash_received'    => 'decimal:2',
            'change_amount'    => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class)->orderBy('payment_date');
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    /** Penjualan bersih setelah retur. */
    public function netTotal(): float
    {
        return (float) $this->grand_total - (float) $this->returned_total;
    }

    public function isOverdue(): bool
    {
        return (float) $this->remaining_amount > 0 && $this->due_date && $this->due_date->isPast();
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'paid'    => 'Lunas',
            'partial' => 'Bon Sebagian',
            default   => 'Bon',
        };
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'transfer' => 'Transfer',
            'bon'      => 'Bon',
            default    => 'Tunai',
        };
    }
}
