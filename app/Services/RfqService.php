<?php

namespace App\Services;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\RfqResponse;
use App\Models\RfqResponseItem;
use Illuminate\Support\Facades\DB;

class RfqService
{
    public function generateRfqNo(): string
    {
        $prefix = 'RFQ-' . now()->format('Ym') . '-';
        $last   = Rfq::where('rfq_no', 'like', $prefix . '%')->orderByDesc('rfq_no')->value('rfq_no');
        $seq    = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Buat RFQ baru — bisa dari MR atau langsung
     */
    public function store(array $data, int $userId): Rfq
    {
        return DB::transaction(function () use ($data, $userId) {
            $rfq = Rfq::create([
                'rfq_no'              => $this->generateRfqNo(),
                'material_request_id' => $data['material_request_id'] ?? null,
                'project_id'          => $data['project_id'] ?? null,
                'created_by'          => $userId,
                'deadline'            => $data['deadline'],
                'notes'               => $data['notes'] ?? null,
                'terms_and_conditions'=> $data['terms_and_conditions'] ?? null,
                'status'              => 'draft',
            ]);

            foreach (($data['items'] ?? []) as $item) {
                $material = isset($item['material_id']) ? Material::find($item['material_id']) : null;
                $rfq->items()->create([
                    'material_id'            => $material?->id,
                    'material_name_snapshot' => $material?->name ?? $item['material_name'],
                    'unit_name_snapshot'     => $material?->unit?->name ?? $item['unit_name'],
                    'qty_required'           => $item['qty_required'],
                    'budget_price'           => $item['budget_price'] ?? null,
                    'specification'          => $item['specification'] ?? null,
                ]);
            }

            return $rfq;
        });
    }

    /**
     * Kirim RFQ ke daftar supplier
     */
    public function send(Rfq $rfq, array $supplierIds): void
    {
        DB::transaction(function () use ($rfq, $supplierIds) {
            $rfq->update(['status' => 'sent']);

            // Buat placeholder response untuk setiap supplier
            foreach ($supplierIds as $supplierId) {
                // Avoid duplicate
                if (! $rfq->responses()->where('supplier_id', $supplierId)->exists()) {
                    $resp = RfqResponse::create([
                        'rfq_id'      => $rfq->id,
                        'supplier_id' => $supplierId,
                        'status'      => 'pending',
                    ]);

                    // Buat placeholder response items
                    foreach ($rfq->items as $item) {
                        $resp->items()->create([
                            'rfq_item_id' => $item->id,
                            'unit_price'  => 0,
                            'subtotal'    => 0,
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Supplier submit penawaran (atau staff input harga dari quotation manual)
     */
    public function submitResponse(RfqResponse $response, array $data): RfqResponse
    {
        return DB::transaction(function () use ($response, $data) {
            $total = 0;

            foreach (($data['items'] ?? []) as $itemData) {
                $price    = (float) ($itemData['unit_price'] ?? 0);
                $rfqItem  = RfqItem::find($itemData['rfq_item_id']);
                $subtotal = $price * (float) ($rfqItem?->qty_required ?? 0);
                $total   += $subtotal;

                $response->items()->where('rfq_item_id', $itemData['rfq_item_id'])
                    ->update([
                        'unit_price' => $price,
                        'subtotal'   => $subtotal,
                        'notes'      => $itemData['notes'] ?? null,
                    ]);
            }

            $response->update([
                'status'          => 'submitted',
                'valid_until'     => $data['valid_until'] ?? null,
                'supplier_ref_no' => $data['supplier_ref_no'] ?? null,
                'payment_terms'   => $data['payment_terms'] ?? 'net30',
                'delivery_days'   => $data['delivery_days'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'total_price'     => $total,
                'submitted_at'    => now(),
            ]);

            return $response;
        });
    }

    /**
     * Pilih pemenang RFQ dan konversi ke Draft PO
     */
    public function award(Rfq $rfq, int $responseId, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($rfq, $responseId, $userId) {
            $response = RfqResponse::with(['items.rfqItem', 'supplier'])->findOrFail($responseId);

            // Mark winner
            $rfq->update([
                'status'              => 'awarded',
                'awarded_supplier_id' => $response->supplier_id,
                'awarded_response_id' => $response->id,
                'awarded_at'          => now(),
                'awarded_by'          => $userId,
            ]);

            $response->update(['status' => 'accepted']);

            // Reject lainnya
            $rfq->responses()->where('id', '!=', $responseId)->update(['status' => 'rejected']);

            // Generate PO number
            $prefix  = 'PO-' . now()->format('Ym') . '-';
            $last    = PurchaseOrder::where('po_no', 'like', $prefix . '%')->orderByDesc('po_no')->value('po_no');
            $seq     = $last ? ((int) substr($last, -4)) + 1 : 1;
            $poNo    = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $total = (float) $response->total_price;

            $po = PurchaseOrder::create([
                'po_no'         => $poNo,
                'project_id'    => $rfq->project_id,
                'supplier_id'   => $response->supplier_id,
                'created_by'    => $userId,
                'rfq_id'        => $rfq->id,
                'status'        => 'draft',
                'grand_total'   => $total,
                'payment_terms' => $response->payment_terms,
                'notes'         => "Dibuat dari RFQ {$rfq->rfq_no} — Supplier: {$response->supplier?->name}",
            ]);

            foreach ($response->items as $ri) {
                PurchaseOrderItem::create([
                    'purchase_order_id'      => $po->id,
                    'material_id'            => $ri->rfqItem?->material_id,
                    'material_name_snapshot' => $ri->rfqItem?->material_name_snapshot ?? '-',
                    'unit_name_snapshot'     => $ri->rfqItem?->unit_name_snapshot ?? '-',
                    'qty'                    => $ri->rfqItem?->qty_required ?? 0,
                    'unit_price'             => $ri->unit_price,
                    'subtotal'               => $ri->subtotal,
                ]);
            }

            $rfq->update(['converted_po_id' => $po->id]);

            return $po;
        });
    }

    /**
     * Tutup RFQ tanpa pemenang
     */
    public function close(Rfq $rfq, string $reason = ''): void
    {
        $rfq->update([
            'status' => 'closed',
            'notes'  => $rfq->notes . ($reason ? "\n[Ditutup: {$reason}]" : ''),
        ]);
    }
}
