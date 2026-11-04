<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseTransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_transaction_id',
        'material_id',
        'material_name_snapshot',
        'unit_name_snapshot',
        'qty',
        'price',
        'subtotal',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function purchaseTransaction()
    {
        return $this->belongsTo(PurchaseTransaction::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}