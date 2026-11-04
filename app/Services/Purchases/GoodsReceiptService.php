<?php

namespace App\Services\Purchases;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseTransaction;
use App\Services\Stock\StockMovementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptService
{
    public function __construct(
        protected StockMovementService $stockService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // [EXISTING] GR dari PurchaseTransaction (invoice sudah ada)
    // ─────────────────────────────────────────────────────────────────────────
    public function store(PurchaseTransaction $transaction, array $data, int $userId): GoodsReceipt
    {
        return DB::transaction(function () use ($transaction, $data, $userId) {
            $items = $data['items'] ?? [];
            $this->guardTransactionReceipt($transaction, $items);

            $receipt = GoodsReceipt::create([
                'purchase_transaction_id' => $transaction->id,
                'purchase_order_id'       => $transaction->purchase_order_id,
                'receipt_no'              => $data['receipt_no'],
                'supplier_do_no'          => $data['supplier_do_no'] ?? null,
                'supplier_do_date'        => $data['supplier_do_date'] ?? null,
                'receipt_date'            => $data['receipt_date'],
                'received_by'             => $data['received_by'],
                'inspector_name'          => $data['inspector_name'] ?? null,
                'inspection_status'       => $data['inspection_status'] ?? 'pending',
                'notes'                   => $data['notes'] ?? null,
                'status'                  => 'pending',
                'created_by'              => $userId,
            ]);

            foreach ($items as $item) {
                $receipt->items()->create([
                    'purchase_transaction_item_id' => $item['purchase_transaction_item_id'] ?? null,
                    'material_name_snapshot'       => $item['material_name_snapshot'],
                    'unit_name_snapshot'           => $item['unit_name_snapshot'],
                    'qty_ordered'                  => $item['qty_ordered'],
                    'qty_received'                 => $item['qty_received'],
                    'notes'                        => $item['notes'] ?? null,
                ]);
            }

            $receipt->status = $this->resolveStatus($receipt->refresh());
            $receipt->save();

            $this->stockService->recordFromGoodsReceipt($receipt->load('items', 'purchaseTransaction'));

            return $receipt;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // [NEW] GR dari PurchaseOrder — barang tiba SEBELUM invoice
    // ─────────────────────────────────────────────────────────────────────────
    public function storeFromPo(PurchaseOrder $po, array $data, int $userId): GoodsReceipt
    {
        return DB::transaction(function () use ($po, $data, $userId) {
            $items = $data['items'] ?? [];
            $this->guardPoReceipt($po, $items);

            $receipt = GoodsReceipt::create([
                'purchase_order_id'  => $po->id,
                'receipt_no'         => $data['receipt_no'],
                'supplier_do_no'     => $data['supplier_do_no'] ?? null,
                'supplier_do_date'   => $data['supplier_do_date'] ?? null,
                'receipt_date'       => $data['receipt_date'],
                'received_by'        => $data['received_by'],
                'inspector_name'     => $data['inspector_name'] ?? null,
                'inspection_status'  => $data['inspection_status'] ?? 'pending',
                'inspection_notes'   => $data['inspection_notes'] ?? null,
                'notes'              => $data['notes'] ?? null,
                'status'             => 'pending',
                'created_by'         => $userId,
            ]);

            foreach ($items as $item) {
                $poItem = isset($item['purchase_order_item_id'])
                    ? PurchaseOrderItem::find($item['purchase_order_item_id'])
                    : null;

                $receipt->items()->create([
                    'purchase_order_item_id' => $poItem?->id,
                    'material_name_snapshot' => $poItem?->material_name_snapshot ?? $item['material_name_snapshot'],
                    'unit_name_snapshot'     => $poItem?->unit_name_snapshot ?? $item['unit_name_snapshot'],
                    'qty_ordered'            => $item['qty_ordered'],
                    'qty_received'           => $item['qty_received'],
                    'quality_status'         => $item['quality_status'] ?? 'ok',
                    'notes'                  => $item['notes'] ?? null,
                ]);

                // Update PO item qty_received
                if ($poItem) {
                    $newQtyReceived = (float) $poItem->qty_received + (float) $item['qty_received'];
                    $newQtyPending  = max(0, (float) $poItem->qty - $newQtyReceived);
                    $poItem->update([
                        'qty_received' => $newQtyReceived,
                        'qty_pending'  => $newQtyPending,
                    ]);
                }
            }

            $receipt->status = $this->resolveStatus($receipt->refresh());
            $receipt->save();

            // Update delivery_status di PO
            $this->updatePoDeliveryStatus($po);

            // Auto-record stok masuk
            $this->stockService->recordFromGoodsReceipt($receipt->load('items', 'purchaseOrder'));

            return $receipt;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // [NEW] Konfirmasi QC Pass / Fail
    // ─────────────────────────────────────────────────────────────────────────
    public function confirmInspection(GoodsReceipt $receipt, string $status, string $notes = ''): void
    {
        $receipt->update([
            'inspection_status' => $status,
            'inspection_notes'  => $notes,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────
    private function updatePoDeliveryStatus(PurchaseOrder $po): void
    {
        $po->load('items');
        $allFull    = $po->items->every(fn ($i) => $i->isFullyReceived());
        $anyReceived = $po->items->some(fn ($i) => (float) $i->qty_received > 0);

        $deliveryStatus = match (true) {
            $allFull     => 'complete',
            $anyReceived => 'partial',
            default      => 'not_started',
        };

        // Status PO tidak diubah di sini: "converted" hanya saat PO dijadikan transaksi,
        // supaya PO yang barangnya sudah lengkap tetap bisa dikonversi menjadi tagihan.
        $po->update(['delivery_status' => $deliveryStatus]);
    }

    /**
     * Qty yang sudah diterima per item transaksi dari surat jalan sebelumnya.
     *
     * @return array<int, float>
     */
    public function receivedQtyPerTransactionItem(PurchaseTransaction $transaction): array
    {
        return GoodsReceiptItem::query()
            ->whereIn('purchase_transaction_item_id', $transaction->items()->pluck('id'))
            ->selectRaw('purchase_transaction_item_id, SUM(qty_received) as total')
            ->groupBy('purchase_transaction_item_id')
            ->pluck('total', 'purchase_transaction_item_id')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    private function guardTransactionReceipt(PurchaseTransaction $transaction, array $items): void
    {
        if ($transaction->approval_status === 'rejected') {
            throw ValidationException::withMessages(['items' => 'Transaksi yang ditolak tidak bisa dibuatkan surat jalan.']);
        }

        // Barang dari PO yang sudah diterima lewat surat jalan PO jangan dihitung dua kali
        if ($transaction->purchase_order_id
            && GoodsReceipt::where('purchase_order_id', $transaction->purchase_order_id)->whereNull('purchase_transaction_id')->exists()) {
            throw ValidationException::withMessages([
                'items' => 'Barang untuk PO transaksi ini sudah diterima lewat Surat Jalan PO. Buat surat jalan lanjutan dari halaman PO.',
            ]);
        }

        $txItems  = $transaction->items()->get()->keyBy('id');
        $received = $this->receivedQtyPerTransactionItem($transaction);

        foreach ($items as $item) {
            $id = $item['purchase_transaction_item_id'] ?? null;
            if (! $id) {
                continue;
            }
            if (! $txItems->has($id)) {
                throw ValidationException::withMessages(['items' => 'Item surat jalan tidak sesuai dengan item transaksi.']);
            }

            $remaining = (float) $txItems[$id]->qty - ($received[$id] ?? 0);
            if ((float) $item['qty_received'] > $remaining + 0.0001) {
                throw ValidationException::withMessages([
                    'items' => "Qty diterima untuk {$txItems[$id]->material_name_snapshot} melebihi sisa yang belum diterima (" . rtrim(rtrim(number_format($remaining, 2, ',', '.'), '0'), ',') . ').',
                ]);
            }
        }
    }

    private function guardPoReceipt(PurchaseOrder $po, array $items): void
    {
        $poItems = $po->items()->get()->keyBy('id');

        foreach ($items as $item) {
            $id = $item['purchase_order_item_id'] ?? null;
            if (! $id) {
                continue;
            }
            if (! $poItems->has($id)) {
                throw ValidationException::withMessages(['items' => 'Item surat jalan tidak sesuai dengan item PO.']);
            }

            $remaining = (float) $poItems[$id]->qty - (float) $poItems[$id]->qty_received;
            if ((float) $item['qty_received'] > $remaining + 0.0001) {
                throw ValidationException::withMessages([
                    'items' => "Qty diterima untuk {$poItems[$id]->material_name_snapshot} melebihi sisa PO yang belum diterima.",
                ]);
            }
        }
    }

    private function resolveStatus(GoodsReceipt $receipt): string
    {
        $items = $receipt->items;
        if ($items->isEmpty()) return 'pending';

        $allComplete = $items->every(
            fn ($item) => (float) $item->qty_received >= (float) $item->qty_ordered
        );
        if ($allComplete) return 'complete';

        $anyReceived = $items->some(fn ($item) => (float) $item->qty_received > 0);
        return $anyReceived ? 'partial' : 'pending';
    }

    public function generateReceiptNoFromPo(PurchaseOrder $po): string
    {
        $count = $po->goodsReceipts()->count() + 1;
        return 'GR-' . $po->po_no . '-' . str_pad($count, 2, '0', STR_PAD_LEFT);
    }

    public function generateReceiptNo(PurchaseTransaction $transaction): string
    {
        $count = $transaction->goodsReceipts()->count() + 1;
        return 'GR-' . $transaction->transaction_no . '-' . str_pad($count, 2, '0', STR_PAD_LEFT);
    }
}
