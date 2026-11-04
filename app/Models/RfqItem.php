<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfqItem extends Model
{
    protected $fillable = [
        'rfq_id', 'material_id', 'material_name_snapshot',
        'unit_name_snapshot', 'qty_required', 'budget_price', 'specification',
    ];

    protected $casts = [
        'qty_required' => 'decimal:2',
        'budget_price' => 'decimal:2',
    ];

    public function rfq(): BelongsTo      { return $this->belongsTo(Rfq::class); }
    public function material(): BelongsTo { return $this->belongsTo(Material::class); }
    public function responseItems(): HasMany { return $this->hasMany(RfqResponseItem::class); }
}
