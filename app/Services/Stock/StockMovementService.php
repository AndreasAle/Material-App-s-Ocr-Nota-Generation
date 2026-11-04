<?php

namespace App\Services\Stock;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Material;
use App\Models\PurchaseTransactionItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    // -------------------------------------------------------------------------
    // Auto-record stock IN from GoodsReceipt
    // -------------------------------------------------------------------------
    public function recordFromGoodsReceipt(GoodsReceipt $receipt): void
    {
        // Hapus movement lama dari receipt ini (re-entrant safe)
        StockMovement::where('reference_type', 'goods_receipt')
            ->where('reference_id', $receipt->id)
            ->delete();

        foreach ($receipt->items as $item) {
            if ((float) $item->qty_received <= 0) {
                continue;
            }

            // Cari material_id dari purchase transaction item
            $materialId  = null;
            $unitPrice   = null;
            $unitName    = $item->unit_name_snapshot;

            if (! $item->purchase_transaction_item_id && $item->purchase_order_item_id) {
                $poItem = \App\Models\PurchaseOrderItem::find($item->purchase_order_item_id);
                if ($poItem) {
                    $materialId = $poItem->material_id;
                    $unitPrice  = $poItem->unit_price;
                    $unitName   = $poItem->unit_name_snapshot;
                }
            }

            if ($item->purchase_transaction_item_id) {
                $txItem = PurchaseTransactionItem::find($item->purchase_transaction_item_id);
                if ($txItem) {
                    $materialId = $txItem->material_id;
                    $unitPrice  = $txItem->price;
                    $unitName   = $txItem->unit_name_snapshot;
                }
            }

            // Konversi ke satuan dasar (mis. beli 2 ikat @10 batang → stok +20 batang)
            $qty = (float) $item->qty_received;
            $material = $materialId ? Material::with(['unit', 'units'])->find($materialId) : null;
            if ($material) {
                $factor = $material->factorFor($unitName);
                $qty *= $factor;
                $unitPrice = $unitPrice !== null && $factor > 0 ? round((float) $unitPrice / $factor, 2) : $unitPrice;
                $unitName = $material->baseUnitName();
            }

            StockMovement::create([
                'material_id'            => $materialId,
                'material_name_snapshot' => $item->material_name_snapshot,
                'unit_name_snapshot'     => $unitName,
                'movement_type'          => 'in',
                'qty'                    => $qty,
                'unit_price'             => $unitPrice,
                'reference_type'         => 'goods_receipt',
                'reference_id'           => $receipt->id,
                'project_id'             => $receipt->purchaseTransaction?->project_id ?? $receipt->purchaseOrder?->project_id,
                'notes'                  => "Auto dari Surat Jalan {$receipt->receipt_no}",
                'movement_date'          => $receipt->receipt_date,
                'created_by'             => $receipt->created_by,
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Manual movement (IN / OUT / adjustment)
    // -------------------------------------------------------------------------
    public function storeManual(array $data, int $userId): StockMovement
    {
        return DB::transaction(function () use ($data, $userId) {
            $material = isset($data['material_id'])
                ? Material::with('unit')->find($data['material_id'])
                : null;

            $movementType = $data['movement_type'];
            $referenceType = match($movementType) {
                'in'         => 'manual_in',
                'out'        => 'manual_out',
                default      => 'adjustment',
            };

            return StockMovement::create([
                'material_id'            => $material?->id,
                'material_name_snapshot' => $material?->name ?? $data['material_name_snapshot'],
                'unit_name_snapshot'     => $material?->unit?->name ?? $data['unit_name_snapshot'] ?? '—',
                'movement_type'          => $movementType,
                'qty'                    => abs((float) $data['qty']),
                'unit_price'             => $data['unit_price'] ?? null,
                'reference_type'         => $referenceType,
                'reference_id'           => null,
                'project_id'             => $data['project_id'] ?? null,
                'warehouse'              => $data['warehouse'] ?? null,
                'warehouse_id'           => $data['warehouse_id'] ?? null,
                'notes'                  => $data['notes'] ?? null,
                'movement_date'          => $data['movement_date'],
                'created_by'             => $userId,
            ]);
        });
    }

    // -------------------------------------------------------------------------
    // Hitung stok saat ini per material_id
    // -------------------------------------------------------------------------
    /**
     * HPP rata-rata tertimbang per satuan dasar dari semua barang masuk yang punya harga.
     */
    public function averageCost(int $materialId): float
    {
        $row = StockMovement::where('material_id', $materialId)
            ->whereIn('movement_type', ['in', 'adjustment'])
            ->whereNotNull('unit_price')
            ->where('unit_price', '>', 0)
            ->selectRaw('SUM(qty * unit_price) as value, SUM(qty) as qty')
            ->first();

        if ($row && (float) $row->qty > 0) {
            return round((float) $row->value / (float) $row->qty, 2);
        }

        $material = Material::find($materialId);

        return (float) ($material?->last_purchase_price ?? $material?->default_price ?? 0);
    }

    /**
     * Catat gerakan stok otomatis dari dokumen lain (penjualan / retur penjualan).
     */
    public function recordAuto(array $data): StockMovement
    {
        return StockMovement::create([
            'material_id'            => $data['material_id'],
            'material_name_snapshot' => $data['material_name_snapshot'],
            'unit_name_snapshot'     => $data['unit_name_snapshot'],
            'movement_type'          => $data['movement_type'],
            'qty'                    => $data['qty'],
            'unit_price'             => $data['unit_price'] ?? null,
            'reference_type'         => $data['reference_type'],
            'reference_id'           => $data['reference_id'],
            'notes'                  => $data['notes'] ?? null,
            'movement_date'          => $data['movement_date'],
            'created_by'             => $data['created_by'] ?? null,
        ]);
    }

    public function currentStock(int $materialId): float
    {
        $in  = StockMovement::where('material_id', $materialId)
                   ->whereIn('movement_type', ['in', 'adjustment'])
                   ->sum('qty');

        $out = StockMovement::where('material_id', $materialId)
                   ->where('movement_type', 'out')
                   ->sum('qty');

        return (float) $in - (float) $out;
    }

    // -------------------------------------------------------------------------
    // Hitung semua stok sekaligus (bulk) → return ['material_id' => float]
    // -------------------------------------------------------------------------
    public function currentStockAll(): array
    {
        $rows = StockMovement::select(
            'material_id',
            DB::raw("SUM(CASE WHEN movement_type IN ('in','adjustment') THEN qty ELSE -qty END) as balance")
        )
        ->whereNotNull('material_id')
        ->groupBy('material_id')
        ->get();

        return $rows->pluck('balance', 'material_id')
                    ->map(fn ($v) => (float) $v)
                    ->toArray();
    }
}
