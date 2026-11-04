<?php

namespace App\Services\Reports;

use App\Models\Material;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryValuationService
{
    /**
     * Calculate inventory valuation from stock movements (weighted average cost).
     * Returns per-material per-warehouse breakdown.
     */
    public function calculate(?int $warehouseId = null, ?string $asOfDate = null): Collection
    {
        $asOf = $asOfDate ?? now()->toDateString();

        $query = StockMovement::selectRaw('
                material_id,
                warehouse_id,
                material_name_snapshot,
                unit_name_snapshot,
                SUM(CASE WHEN movement_type IN (\'in\',\'adjustment\') THEN qty ELSE 0 END) -
                SUM(CASE WHEN movement_type = \'out\' THEN qty ELSE 0 END) AS qty_on_hand,
                SUM(CASE WHEN movement_type IN (\'in\',\'adjustment\') THEN (qty * COALESCE(unit_price, 0)) ELSE 0 END) /
                NULLIF(SUM(CASE WHEN movement_type IN (\'in\',\'adjustment\') THEN qty ELSE 0 END), 0) AS avg_cost
            ')
            ->whereDate('movement_date', '<=', $asOf)
            ->groupBy('material_id', 'warehouse_id', 'material_name_snapshot', 'unit_name_snapshot');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->get()->map(function ($row) {
            $qtyOnHand = (float) max(0, $row->qty_on_hand ?? 0);
            $avgCost   = (float) ($row->avg_cost ?? 0);
            $row->qty_on_hand  = $qtyOnHand;
            $row->avg_cost     = $avgCost;
            $row->total_value  = round($qtyOnHand * $avgCost, 2);
            return $row;
        })->filter(fn ($row) => $row->qty_on_hand > 0 || $row->total_value > 0);
    }

    /**
     * Summary totals.
     */
    public function getSummary(?int $warehouseId = null, ?string $asOfDate = null): array
    {
        $items = $this->calculate($warehouseId, $asOfDate);

        return [
            'total_items'       => $items->count(),
            'total_value'       => $items->sum('total_value'),
            'total_qty'         => $items->sum('qty_on_hand'),
            'zero_stock_count'  => $items->where('qty_on_hand', '<=', 0)->count(),
        ];
    }

    /**
     * Items with stock below minimum_stock threshold.
     */
    public function getLowStockItems(): Collection
    {
        $valuations = $this->calculate();

        return Material::with('unit')
            ->where('is_active', true)
            ->whereNotNull('minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->get()
            ->map(function ($material) use ($valuations) {
                $row = $valuations->firstWhere('material_id', $material->id);
                $material->qty_on_hand = $row ? $row->qty_on_hand : 0;
                $material->avg_cost    = $row ? $row->avg_cost : 0;
                $material->total_value = $row ? $row->total_value : 0;
                return $material;
            })
            ->filter(fn ($m) => $m->qty_on_hand <= (float) $m->minimum_stock)
            ->sortBy('qty_on_hand');
    }
}
