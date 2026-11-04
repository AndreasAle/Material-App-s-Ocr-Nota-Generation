<?php

namespace App\Services\Accounting;

use App\Models\Material;
use App\Models\StockBalance;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockBalanceService
{
    /**
     * Rebuild all stock balances from stock_movements.
     * Called by artisan command: notago:rebuild-stock-balances
     */
    public function rebuildAll(): int
    {
        $rebuilt = 0;

        // Get all unique material+warehouse combos from movements
        $combos = StockMovement::selectRaw('material_id, warehouse_id')
            ->groupBy('material_id', 'warehouse_id')
            ->get();

        // TRUNCATE must happen outside transaction (MySQL DDL auto-commits)
        StockBalance::truncate();

        foreach ($combos as $combo) {
            $this->recalculate($combo->material_id, $combo->warehouse_id);
            $rebuilt++;
        }

        return $rebuilt;
    }

    /**
     * Recalculate balance for a specific material + warehouse.
     */
    public function recalculate(int $materialId, ?int $warehouseId): StockBalance
    {
        $movements = StockMovement::where('material_id', $materialId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('movement_date')
            ->get();

        $qtyOnHand  = 0.0;
        $totalCost  = 0.0;

        foreach ($movements as $mov) {
            $qty   = (float) $mov->qty;
            $price = (float) ($mov->unit_price ?? 0);

            if (in_array($mov->movement_type, ['in', 'adjustment'])) {
                // Weighted average cost: nilai masuk ditambahkan ke total biaya
                $qtyOnHand += $qty;
                $totalCost += $qty * $price;
            } elseif ($mov->movement_type === 'out') {
                // Barang keluar dinilai dengan rata-rata saat itu
                $avg       = $qtyOnHand > 0 ? $totalCost / $qtyOnHand : 0;
                $qtyOnHand = max(0, $qtyOnHand - $qty);
                $totalCost = $qtyOnHand * $avg;
            }
        }

        $avgCost    = $qtyOnHand > 0 ? $totalCost / $qtyOnHand : 0;
        $totalValue = $qtyOnHand * $avgCost;

        return StockBalance::updateOrCreate(
            ['material_id' => $materialId, 'warehouse_id' => $warehouseId],
            [
                'qty_on_hand'     => round($qtyOnHand, 4),
                'avg_cost'        => round($avgCost, 4),
                'total_value'     => round($totalValue, 2),
                'last_updated_at' => now(),
            ]
        );
    }

    /**
     * Get total inventory valuation grouped by material.
     */
    public function getInventoryValuation(?int $warehouseId = null)
    {
        $query = StockBalance::with(['material.unit', 'material.category'])
            ->where('qty_on_hand', '>', 0);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->get();
    }

    /**
     * Total inventory value.
     */
    public function getTotalValue(?int $warehouseId = null): float
    {
        $query = StockBalance::query();
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        return (float) $query->sum('total_value');
    }
}
