<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_category_id',
        'unit_id',
        'code',
        'barcode',
        'name',
        'default_price',
        'sell_price',
        'wholesale_price',
        'wholesale_min_qty',
        'last_purchase_price',
        'minimum_stock',
        'specification',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
            'sell_price'          => 'decimal:2',
            'wholesale_price'     => 'decimal:2',
            'wholesale_min_qty'   => 'decimal:2',
            'last_purchase_price' => 'decimal:2',
            'minimum_stock' => 'decimal:2',
            'is_active'     => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(MaterialCategory::class, 'material_category_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseTransactionItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(\App\Models\StockMovement::class);
    }

    /** Satuan tambahan (1 satuan = factor × satuan dasar). */
    public function units()
    {
        return $this->hasMany(MaterialUnit::class)->orderBy('factor');
    }

    public function baseUnitName(): string
    {
        return (string) ($this->unit?->name ?? '-');
    }

    /**
     * Faktor konversi suatu satuan ke satuan dasar. Satuan yang tidak dikenal dianggap satuan dasar (1).
     */
    public function factorFor(?string $unitName): float
    {
        $name = mb_strtolower(trim((string) $unitName));
        if ($name === '' || $name === mb_strtolower($this->baseUnitName()) || $name === mb_strtolower((string) $this->unit?->code)) {
            return 1.0;
        }

        $unit = $this->units->first(fn ($u) => mb_strtolower($u->unit_name) === $name);

        return $unit ? (float) $unit->factor : 1.0;
    }

    /**
     * Harga jual per satuan tertentu. Grosir berlaku bila qty (dalam satuan dasar) ≥ minimum grosir.
     */
    public function sellPriceFor(?string $unitName, float $qty = 1): float
    {
        $factor = $this->factorFor($unitName);
        $qtyBase = $qty * $factor;

        $base = (float) $this->sell_price;
        if ($this->wholesale_price && $this->wholesale_min_qty && $qtyBase >= (float) $this->wholesale_min_qty) {
            $base = (float) $this->wholesale_price;
        }

        if ($factor != 1.0) {
            $unit = $this->units->first(fn ($u) => mb_strtolower($u->unit_name) === mb_strtolower(trim((string) $unitName)));
            if ($unit && $unit->sell_price !== null && (float) $unit->sell_price > 0) {
                return (float) $unit->sell_price;
            }
        }

        return round($base * $factor, 2);
    }

    /** Saran harga jual dari harga beli terakhir + margin default. */
    public function suggestedSellPrice(?float $cost = null): float
    {
        $cost ??= (float) ($this->last_purchase_price ?? $this->default_price);
        $margin = (float) config('store.default_margin', 15);

        // dibulatkan ke atas kelipatan 100 agar enak dipakai di kasir
        return (float) (ceil(($cost * (1 + $margin / 100)) / 100) * 100);
    }
}