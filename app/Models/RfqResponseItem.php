<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqResponseItem extends Model
{
    protected $fillable = [
        'rfq_response_id', 'rfq_item_id', 'unit_price', 'subtotal', 'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function response(): BelongsTo { return $this->belongsTo(RfqResponse::class, 'rfq_response_id'); }
    public function rfqItem(): BelongsTo  { return $this->belongsTo(RfqItem::class); }
}
