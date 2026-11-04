<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    protected $fillable = ['return_no', 'sale_id', 'return_date', 'total', 'cost_total', 'refund_amount', 'reason', 'created_by'];

    protected function casts(): array
    {
        return [
            'return_date'   => 'date',
            'total'         => 'decimal:2',
            'cost_total'    => 'decimal:2',
            'refund_amount' => 'decimal:2',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}
