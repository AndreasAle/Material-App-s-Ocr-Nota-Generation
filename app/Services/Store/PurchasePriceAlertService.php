<?php

namespace App\Services\Store;

use App\Models\Material;
use App\Models\PurchaseTransaction;
use App\Services\NotificationService;

/**
 * Bandingkan harga beli baru dengan harga beli terakhir & harga jual per barang.
 * Hasilnya ditampilkan setelah transaksi disimpan dan dikirim sebagai notifikasi ke pemilik.
 */
class PurchasePriceAlertService
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    /**
     * @return array<int, array{material_id:int, name:string, unit:string, old_cost:?float, new_cost:float, sell_price:float, suggested:float, type:string}>
     */
    public function check(PurchaseTransaction $transaction): array
    {
        $alerts = [];

        foreach ($transaction->items()->get() as $item) {
            if (! $item->material_id || (float) $item->price <= 0) {
                continue;
            }

            $material = Material::with(['unit', 'units'])->find($item->material_id);
            if (! $material) {
                continue;
            }

            $factor = $material->factorFor($item->unit_name_snapshot);
            $newCost = round((float) $item->price / max($factor, 0.0001), 2);
            $oldCost = $material->last_purchase_price !== null ? (float) $material->last_purchase_price : null;
            $sell = (float) $material->sell_price;

            $type = null;
            if ($sell > 0 && $newCost >= $sell) {
                $type = 'below_cost';      // harga jual sudah tidak menutup harga beli
            } elseif ($oldCost !== null && $oldCost > 0 && $newCost > $oldCost + 0.009) {
                $type = 'price_up';
            } elseif ($sell <= 0) {
                $type = 'no_sell_price';
            }

            if ($type) {
                $alerts[] = [
                    'material_id' => $material->id,
                    'name'        => $material->name,
                    'unit'        => $material->baseUnitName(),
                    'old_cost'    => $oldCost,
                    'new_cost'    => $newCost,
                    'sell_price'  => $sell,
                    'suggested'   => $material->suggestedSellPrice($newCost),
                    'type'        => $type,
                ];
            }

            $material->update(['last_purchase_price' => $newCost]);
        }

        $priceAlerts = array_filter($alerts, fn ($a) => $a['type'] !== 'no_sell_price');
        if ($priceAlerts) {
            $names = implode(', ', array_slice(array_column($priceAlerts, 'name'), 0, 5));
            $this->notifications->notifyAdmins(
                type: 'price_alert',
                title: 'Harga beli naik: ' . count($priceAlerts) . ' barang',
                message: "Cek harga jual untuk: {$names}.",
                data: ['url' => route('purchase-transactions.show', $transaction)],
            );
        }

        return $alerts;
    }

    /** Terapkan saran harga jual ke barang yang dipilih. */
    public function applySuggested(array $materialIds): int
    {
        $count = 0;
        foreach (Material::whereIn('id', $materialIds)->get() as $material) {
            if ($material->last_purchase_price === null) {
                continue;
            }
            $material->update(['sell_price' => $material->suggestedSellPrice((float) $material->last_purchase_price)]);
            $count++;
        }

        return $count;
    }
}
