<?php

namespace App\Services\Purchases;

use App\Models\PurchaseTransaction;
use App\Models\SupplierPriceHistory;

class SupplierPriceHistoryService
{
    public function recordFromTransaction(PurchaseTransaction $transaction): void
    {
        if (! $transaction->supplier_id) {
            return;
        }

        $transaction->loadMissing('items');

        foreach ($transaction->items as $item) {
            if ((float) $item->price <= 0) {
                continue;
            }

            // Upsert per supplier + material + bulan — satu record per kombinasi per bulan
            SupplierPriceHistory::updateOrCreate(
                [
                    'supplier_id'             => $transaction->supplier_id,
                    'material_id'             => $item->material_id,
                    'material_name_snapshot'  => $item->material_name_snapshot,
                    'recorded_at'             => $transaction->transaction_date->format('Y-m-01'),
                ],
                [
                    'unit_name_snapshot'      => $item->unit_name_snapshot,
                    'price'                   => $item->price,
                    'purchase_transaction_id' => $transaction->id,
                ]
            );
        }
    }
}
