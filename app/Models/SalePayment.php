<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $fillable = ['sale_id', 'customer_id', 'payment_date', 'amount', 'payment_method', 'notes', 'created_by'];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount'       => 'decimal:2',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
