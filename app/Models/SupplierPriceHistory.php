<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'material_id',
        'material_name_snapshot',
        'unit_name_snapshot',
        'price',
        'recorded_at',
        'purchase_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'price'       => 'decimal:2',
            'recorded_at' => 'date',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function purchaseTransaction()
    {
        return $this->belongsTo(PurchaseTransaction::class);
    }
}
