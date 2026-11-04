<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    protected $fillable = ['sale_return_id', 'sale_item_id', 'qty', 'amount'];

    protected function casts(): array
    {
        return [
            'qty'    => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }
}
