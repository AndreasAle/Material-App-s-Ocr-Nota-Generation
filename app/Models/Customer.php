<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'address', 'credit_limit', 'notes', 'is_active'];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'is_active'    => 'boolean',
        ];
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    /** Total bon (piutang) yang belum dibayar. */
    public function outstanding(): float
    {
        return (float) $this->sales()->where('remaining_amount', '>', 0)->sum('remaining_amount');
    }
}
