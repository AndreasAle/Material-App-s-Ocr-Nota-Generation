<?php

namespace App\Services\Purchases;

use App\Models\GoodsReceiptItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionItem;
use App\Models\StockMovement;
use App\Models\VendorScore;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseReturnService
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function generateReturnNo(): string
    {
        $prefix = 'RTN-' . now()->format('Ym') . '-';
        $last   = PurchaseReturn::where('return_no', 'like', $prefix . '%')
                    ->orderByDesc('return_no')->value('return_no');
        $seq    = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function store(array $data, int $userId): PurchaseReturn
    {
        $this->guardReturnQty($data);

        return DB::transaction(function () use ($data, $userId) {
            $items = $data['items'] ?? [];

            // Hitung total
            $totalValue = collect($items)->sum(fn ($i) => (float)$i['qty_returned'] * (float)$i['unit_price']);

            $return = PurchaseReturn::create([
                'return_no'               => $this->generateReturnNo(),
                'purchase_transaction_id' => $data['purchase_transaction_id'],
                'goods_receipt_id'        => $data['goods_receipt_id'] ?? null,
                'supplier_id'             => $data['supplier_id'] ?? null,
                'project_id'              => $data['project_id'] ?? null,
                'return_date'             => $data['return_date'] ?? now()->toDateString(),
                'reason'                  => $data['reason'],
                'reason_detail'           => $data['description'] ?? $data['reason_detail'] ?? null,
                'status'                  => 'draft',
                'credit_note_no'          => $data['credit_note_no'] ?? null,
                'notes'                   => $data['notes'] ?? null,
                'created_by'              => $userId,
            ]);

            foreach ($items as $item) {
                $qty = (float) $item['qty_returned'];
                $price = (float) $item['unit_price'];
                $return->items()->create([
                    'purchase_transaction_item_id' => $item['purchase_transaction_item_id'] ?? null,
                    'material_name_snapshot'       => $item['material_name_snapshot'],
                    'unit_name_snapshot'           => $item['unit_name_snapshot'] ?? '',
                    'qty_returned'                 => $qty,
                    'unit_price'                   => $price,
                    'subtotal'                     => $qty * $price,
                    'notes'                        => $item['notes'] ?? null,
                ]);
            }

            return $return;
        });
    }

    public function submit(PurchaseReturn $return): void
    {
        $return->update(['status' => 'submitted']);

        $this->notifications->notifyAdmins(
            type: 'purchase_return',
            title: "Return Pembelian #{$return->return_no} Menunggu Persetujuan",
            message: "Return barang senilai Rp " . number_format($return->total_return_value, 0, ',', '.') . " menunggu persetujuan.",
            data: ['url' => "/purchase-returns/{$return->id}"],
        );
    }

    public function approve(PurchaseReturn $return, int $approverId): void
    {
        DB::transaction(function () use ($return, $approverId) {
            $return->update([
                'status'      => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);

            // Update GR items qty_returned
            if ($return->goods_receipt_id) {
                foreach ($return->items as $ri) {
                    if ($ri->purchase_transaction_item_id) {
                        GoodsReceiptItem::where('purchase_transaction_item_id', $ri->purchase_transaction_item_id)
                            ->where('goods_receipt_id', $return->goods_receipt_id)
                            ->increment('qty_returned', $ri->qty_returned);
                    }
                }
            }

            // Notify creator
            if ($return->creator) {
                $this->notifications->create(
                    userId: $return->created_by,
                    type: 'return_approved',
                    title: "Return #{$return->return_no} Disetujui",
                    message: "Return pembelian Anda telah disetujui.",
                    data: ['url' => "/purchase-returns/{$return->id}"],
                );
            }
        });
    }

    public function markReturned(PurchaseReturn $return): void
    {
        DB::transaction(function () use ($return) {
            $return->update(['status' => 'returned']);

            // Barang fisik sudah dikirim balik ke supplier → keluarkan dari stok (hanya bila pernah masuk lewat surat jalan)
            $hasReceipt = $return->goods_receipt_id
                || $return->purchaseTransaction?->goodsReceipts()->exists();
            if (! $hasReceipt) {
                return;
            }

            StockMovement::where('reference_type', 'purchase_return')->where('reference_id', $return->id)->delete();

            foreach ($return->items()->get() as $item) {
                if ((float) $item->qty_returned <= 0) {
                    continue;
                }
                $txItem = $item->purchase_transaction_item_id
                    ? PurchaseTransactionItem::find($item->purchase_transaction_item_id)
                    : null;

                StockMovement::create([
                    'material_id'            => $txItem?->material_id,
                    'material_name_snapshot' => $item->material_name_snapshot,
                    'unit_name_snapshot'     => $item->unit_name_snapshot ?? $txItem?->unit_name_snapshot,
                    'movement_type'          => 'out',
                    'qty'                    => $item->qty_returned,
                    'unit_price'             => $item->unit_price,
                    'reference_type'         => 'purchase_return',
                    'reference_id'           => $return->id,
                    'project_id'             => $return->purchaseTransaction?->project_id,
                    'notes'                  => "Auto dari Retur {$return->return_no}",
                    'movement_date'          => now()->toDateString(),
                    'created_by'             => auth()->id() ?? $return->created_by,
                ]);
            }
        });
    }

    /**
     * Qty retur per item tidak boleh melebihi qty beli dikurangi retur lain yang belum ditolak.
     */
    private function guardReturnQty(array $data): void
    {
        foreach ($data['items'] ?? [] as $item) {
            $id = $item['purchase_transaction_item_id'] ?? null;
            if (! $id || (float) ($item['qty_returned'] ?? 0) <= 0) {
                continue;
            }

            $txItem = PurchaseTransactionItem::find($id);
            if (! $txItem || (int) $txItem->purchase_transaction_id !== (int) $data['purchase_transaction_id']) {
                throw ValidationException::withMessages(['items' => 'Item retur tidak sesuai dengan transaksi.']);
            }

            $alreadyReturned = (float) PurchaseReturnItem::where('purchase_transaction_item_id', $id)
                ->whereHas('purchaseReturn', fn ($q) => $q->where('status', '!=', 'rejected'))
                ->sum('qty_returned');

            if ((float) $item['qty_returned'] > (float) $txItem->qty - $alreadyReturned + 0.0001) {
                throw ValidationException::withMessages([
                    'items' => "Qty retur {$txItem->material_name_snapshot} melebihi qty pembelian yang belum diretur.",
                ]);
            }
        }
    }

    public function markCredited(PurchaseReturn $return, array $data): void
    {
        DB::transaction(function () use ($return, $data) {
            $return->update([
                'status'           => 'credited',
                'credit_note_no'   => $data['credit_note_no'] ?? null,
                'credit_note_date' => $data['credit_note_date'] ?? null,
                'credit_amount'    => $data['credit_amount'] ?? $return->total_return_value,
            ]);

            // Nota kredit mengurangi sisa hutang transaksi asal
            if ($return->purchaseTransaction) {
                app(\App\Services\Accounting\SupplierPaymentService::class)
                    ->syncTransactionPaidAmount($return->purchaseTransaction);
            }
        });
    }
}
