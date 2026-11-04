<?php

namespace App\Services;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MaterialRequestService
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function generateRequestNo(): string
    {
        $prefix = 'MR-' . now()->format('Ym') . '-';
        $last   = MaterialRequest::where('request_no', 'like', $prefix . '%')
            ->orderByDesc('request_no')
            ->value('request_no');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function store(array $data, int $userId): MaterialRequest
    {
        return DB::transaction(function () use ($data, $userId) {
            $mr = MaterialRequest::create([
                'request_no'  => $this->generateRequestNo(),
                'project_id'  => $data['project_id'] ?? null,
                'requested_by' => $userId,
                'urgency'     => $data['urgency'] ?? 'normal',
                'needed_by'   => $data['needed_by'] ?? null,
                'notes'       => $data['notes'] ?? null,
                'status'      => 'draft',
            ]);

            foreach ($data['items'] as $item) {
                $material = isset($item['material_id']) && $item['material_id']
                    ? Material::find($item['material_id'])
                    : null;

                MaterialRequestItem::create([
                    'material_request_id'    => $mr->id,
                    'material_id'            => $material?->id,
                    'material_name_snapshot' => $item['material_name'],
                    'unit_name_snapshot'     => $item['unit_name'],
                    'qty_requested'          => $item['qty_requested'],
                    'estimated_price'        => $item['estimated_price'] ?? null,
                    'notes'                  => $item['notes'] ?? null,
                ]);
            }

            return $mr;
        });
    }

    public function submit(MaterialRequest $mr): void
    {
        $mr->update(['status' => 'submitted']);

        // Notify all admin/pimpinan
        $requester = $mr->requester;
        $this->notificationService->notifyAdmins(
            type: 'material_request',
            title: "Permintaan Material #{$mr->request_no}",
            message: ($requester?->name ?? 'Staff') . " mengajukan permintaan {$mr->items->count()} material" .
                ($mr->project ? " untuk proyek {$mr->project->name}" : '') . ".",
            data: ['url' => "/material-requests/{$mr->id}", 'request_no' => $mr->request_no],
        );
    }

    public function approve(MaterialRequest $mr, int $approverId): void
    {
        $mr->update([
            'status'      => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        // Notify requester
        if ($mr->requested_by) {
            $approver = User::find($approverId);
            $this->notificationService->create(
                userId: $mr->requested_by,
                type: 'approval_result',
                title: "MR #{$mr->request_no} Disetujui",
                message: "Permintaan material Anda telah disetujui oleh {$approver?->name}.",
                data: ['url' => "/material-requests/{$mr->id}"],
            );
        }
    }

    public function reject(MaterialRequest $mr, int $approverId, string $reason): void
    {
        $mr->update([
            'status'           => 'rejected',
            'approved_by'      => $approverId,
            'approved_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        if ($mr->requested_by) {
            $approver = User::find($approverId);
            $this->notificationService->create(
                userId: $mr->requested_by,
                type: 'rejection_result',
                title: "MR #{$mr->request_no} Ditolak",
                message: "Permintaan material Anda ditolak oleh {$approver?->name}: {$reason}",
                data: ['url' => "/material-requests/{$mr->id}"],
            );
        }
    }

    /**
     * Convert approved MR to a Draft Purchase Order.
     */
    public function convertToPo(MaterialRequest $mr, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($mr, $userId) {
            // Generate PO number
            $prefix = 'PO-' . now()->format('Ym') . '-';
            $last   = PurchaseOrder::where('po_no', 'like', $prefix . '%')->orderByDesc('po_no')->value('po_no');
            $seq    = $last ? ((int) substr($last, -4)) + 1 : 1;
            $poNo   = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $grandTotal = $mr->items->sum(fn ($i) => (float)$i->qty_requested * (float)($i->estimated_price ?? 0));

            $po = PurchaseOrder::create([
                'po_no'      => $poNo,
                'project_id' => $mr->project_id,
                'created_by' => $userId,
                'status'     => 'draft',
                'grand_total' => $grandTotal,
                'notes'      => "Dibuat dari MR #{$mr->request_no}" . ($mr->notes ? ": {$mr->notes}" : ''),
            ]);

            foreach ($mr->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id'      => $po->id,
                    'material_id'            => $item->material_id,
                    'material_name_snapshot' => $item->material_name_snapshot,
                    'unit_name_snapshot'     => $item->unit_name_snapshot,
                    'qty'                    => $item->qty_requested,
                    'unit_price'             => $item->estimated_price ?? 0,
                    'subtotal'               => (float)$item->qty_requested * (float)($item->estimated_price ?? 0),
                    'notes'                  => $item->notes,
                ]);
            }

            $mr->update([
                'status'          => 'converted',
                'converted_po_id' => $po->id,
            ]);

            // Notify requester
            if ($mr->requested_by) {
                $this->notificationService->create(
                    userId: $mr->requested_by,
                    type: 'po_status',
                    title: "MR dikonversi ke PO #{$poNo}",
                    message: "Permintaan material #{$mr->request_no} telah dikonversi menjadi Purchase Order #{$poNo}.",
                    data: ['url' => "/purchase-orders/{$po->id}"],
                );
            }

            return $po;
        });
    }

    /**
     * Auto-create MRs for materials below minimum stock.
     */
    public function autoReorder(array $lowStockItems): int
    {
        if (empty($lowStockItems)) return 0;

        $created = 0;
        foreach ($lowStockItems as $item) {
            $material = $item['material'];
            $shortage = $item['minimum_stock'] - $item['current_stock'];

            // Skip if there's already a pending MR for this material
            $alreadyPending = MaterialRequestItem::where('material_id', $material->id)
                ->whereHas('materialRequest', fn ($q) => $q->whereIn('status', ['draft', 'submitted', 'approved']))
                ->exists();

            if ($alreadyPending) continue;

            $mr = MaterialRequest::create([
                'request_no'      => $this->generateRequestNo(),
                'requested_by'    => null,
                'status'          => 'submitted',
                'urgency'         => 'high',
                'notes'           => "Auto-reorder: Stok {$material->name} tersisa {$item['current_stock']} dari minimum {$item['minimum_stock']}.",
                'is_auto_reorder' => true,
            ]);

            MaterialRequestItem::create([
                'material_request_id'    => $mr->id,
                'material_id'            => $material->id,
                'material_name_snapshot' => $material->name,
                'unit_name_snapshot'     => $material->unit?->name ?? '-',
                'qty_requested'          => max($shortage, $item['minimum_stock']), // reorder at least 1x minimum
            ]);

            // Notify admins
            $this->notificationService->notifyAdmins(
                type: 'low_stock',
                title: "Auto-Reorder: {$material->name}",
                message: "Stok {$material->name} di bawah minimum. MR #{$mr->request_no} dibuat otomatis.",
                data: ['url' => "/material-requests/{$mr->id}"],
            );

            $created++;
        }

        return $created;
    }
}
