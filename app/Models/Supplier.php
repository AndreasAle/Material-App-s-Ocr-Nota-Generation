<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'city',
        'notes',
        'is_active',
        // Added in 2026_05_30 migration
        'email',
        'npwp',
        'is_pkp',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_pkp'    => 'boolean',
        ];
    }

    public function purchaseTransactions()
    {
        return $this->hasMany(PurchaseTransaction::class);
    }

    public function priceHistories()
    {
        return $this->hasMany(\App\Models\SupplierPriceHistory::class);
    }
}