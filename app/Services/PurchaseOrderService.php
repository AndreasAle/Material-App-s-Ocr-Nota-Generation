<?php

namespace App\Services;

use App\Mail\PoApprovalRequestMail;
use App\Mail\PoApprovalResultMail;
use App\Models\ApprovalRule;
use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\PurchaseTransaction;
use App\Models\User;
use App\Services\BudgetCheckService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PurchaseOrderService
{
    public function __construct(
        protected BudgetCheckService $budgetCheck,
    ) {}

    public function generatePoNo(): string
    {
        $prefix = 'PO-' . now()->format('Ym') . '-';
        $last   = PurchaseOrder::where('po_no', 'like', $prefix . '%')->orderByDesc('po_no')->first();
        $seq    = $last ? ((int) substr($last->po_no, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function store(array $data, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            $items = collect($data['items'] ?? []);
            $grandTotal = $items->sum(fn ($i) => (float)($i['qty'] ?? 0) * (float)($i['unit_price'] ?? 0));

            // Budget check
            $budgetResult = ['status' => 'not_checked', 'available' => null];
            if (!empty($data['project_id'])) {
                $budgetResult = $this->budgetCheck->check((int) $data['project_id'], $grandTotal);
            }

            $po = PurchaseOrder::create([
                'po_no'                  => $data['po_no'] ?? $this->generatePoNo(),
                'project_id'             => $data['project_id'] ?? null,
                'supplier_id'            => $data['supplier_id'] ?? null,
                'created_by'             => $userId,
                'material_request_id'    => $data['material_request_id'] ?? null,
                'rfq_id'                 => $data['rfq_id'] ?? null,
                'status'                 => 'draft',
                'expected_date'          => $data['expected_date'] ?? null,
                'grand_total'            => $grandTotal,
                'notes'                  => $data['notes'] ?? null,
                'payment_terms'          => $data['payment_terms'] ?? 'net30',
                'dp_percent'             => $data['dp_percent'] ?? 0,
                'payment_due_days'       => $data['payment_due_days'] ?? 30,
                'delivery_terms'         => $data['delivery_terms'] ?? null,
                'budget_status'          => $budgetResult['status'],
                'budget_available'       => $budgetResult['available'] ?? null,
                'budget_override'        => (bool) ($data['budget_override'] ?? false),
                'budget_override_reason' => $data['budget_override_reason'] ?? null,
            ]);

            foreach ($items as $item) {
                $material = isset($item['material_id']) ? Material::with('unit')->find($item['material_id']) : null;
                $qty      = (float)($item['qty'] ?? 0);
                $price    = (float)($item['unit_price'] ?? 0);
                $po->items()->create([
                    'material_id'            => $material?->id,
                    'material_name_snapshot' => $material?->name ?? ($item['material_name_snapshot'] ?? ''),
                    'unit_name_snapshot'     => $material?->unit?->name ?? ($item['unit_name_snapshot'] ?? ''),
                    'qty'                    => $qty,
                    'unit_price'             => $price,
                    'subtotal'               => $qty * $price,
                    'notes'                  => $item['notes'] ?? null,
                ]);
            }

            return $po;
        });
    }

    public function submit(PurchaseOrder $po): PurchaseOrder
    {
        $po->update(['status' => 'submitted']);

        // Multi-level: cari approver yang tepat berdasarkan nilai
        $rule = ApprovalRule::findRule('purchase_order', (float) $po->grand_total);
        $approverRole = $rule?->approver_role ?? 'admin';

        $approvers = User::where('role', $approverRole)
            ->orWhereIn('role', ['admin', 'pimpinan'])
            ->whereNotNull('email')
            ->get()
            ->unique('id');

        foreach ($approvers as $approver) {
            try { Mail::to($approver->email)->queue(new PoApprovalRequestMail($po, $approver)); } catch (\Throwable) {}
        }

        return $po;
    }

    public function approve(PurchaseOrder $po, int $approverId): PurchaseOrder
    {
        // Cek apakah approver punya wewenang berdasarkan nilai PO
        $approver = User::find($approverId);
        if ($approver && !ApprovalRule::canApprove('purchase_order', (float) $po->grand_total, $approver->role)) {
            throw new \RuntimeException("Nilai PO melebihi limit persetujuan Anda.");
        }

        $po->update([
            'status'           => 'approved',
            'approved_by'      => $approverId,
            'approved_at'      => now(),
            'rejection_reason' => null,
        ]);

        if ($po->creator?->email) {
            try { Mail::to($po->creator->email)->queue(new PoApprovalResultMail($po, 'approved')); } catch (\Throwable) {}
        }

        return $po;
    }

    /**
     * PO dikirim ke supplier (update status sent_at)
     */
    public function markSent(PurchaseOrder $po): void
    {
        $po->update(['sent_at' => now()]);
    }

    /**
     * Supplier konfirmasi PO
     */
    public function confirmBySupplier(PurchaseOrder $po, array $data): void
    {
        $po->update([
            'supplier_ref_no'       => $data['supplier_ref_no'] ?? $po->supplier_ref_no,
            'supplier_confirmed_at' => now(),
        ]);
    }

    /**
     * Tutup PO secara manual (partial delivery, supplier tidak bisa penuhi)
     */
    public function close(PurchaseOrder $po, int $userId, string $reason): void
    {
        $po->update([
            'status'       => 'closed',
            'closed_at'    => now(),
            'closed_by'    => $userId,
            'close_reason' => $reason,
        ]);
    }

    public function reject(PurchaseOrder $po, int $approverId, string $reason): PurchaseOrder
    {
        $po->update([
            'status'           => 'rejected',
            'approved_by'      => $approverId,
            'approved_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        if ($po->creator?->email) {
            try { Mail::to($po->creator->email)->queue(new PoApprovalResultMail($po, 'rejected')); } catch (\Throwable) {}
        }

        return $po;
    }

    /**
     * Konversi PO → buat draft PurchaseTransaction (partial invoice support)
     * Bisa dipanggil berkali-kali untuk pengiriman bertahap.
     */
    public function convertToTransaction(PurchaseOrder $po, int $userId): PurchaseTransaction
    {
        return DB::transaction(function () use ($po, $userId) {
            // Nomor transaksi memakai generator yang sama dengan input manual agar tidak bentrok
            $trxNo = app(\App\Services\Purchases\PurchaseNumberService::class)->generate(now()->toDateString());

            $tx = PurchaseTransaction::create([
                'transaction_no'      => $trxNo,
                'transaction_date'    => now()->toDateString(),
                'supplier_id'         => $po->supplier_id,
                'project_id'          => $po->project_id,
                'purchase_order_id'   => $po->id,
                'user_id'             => $userId,
                'grand_total'         => $po->grand_total,
                'status'              => 'draft',
                'approval_status'     => 'draft',
                'goods_receipt_status'=> $po->isDeliveryComplete() ? 'complete' : 'pending',
                'notes'               => "Dikonversi dari PO {$po->po_no}",
                'input_source'        => 'manual',
                'due_date'            => $po->computeDueDate(),
            ]);

            foreach ($po->items as $item) {
                $tx->items()->create([
                    'material_id'            => $item->material_id,
                    'material_name_snapshot' => $item->material_name_snapshot,
                    'unit_name_snapshot'     => $item->unit_name_snapshot,
                    'qty'                    => $item->qty,
                    'price'                  => $item->unit_price,
                    'subtotal'               => $item->subtotal,
                ]);
            }

            $tx->update(['subtotal' => $po->grand_total]);
            app(\App\Services\Accounting\SupplierPaymentService::class)->syncTransactionPaidAmount($tx);

            // PO bisa dikonversi berkali-kali, simpan link via pivot
            DB::table('purchase_order_transactions')->insertOrIgnore([
                'purchase_order_id'        => $po->id,
                'purchase_transaction_id'  => $tx->id,
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);

            // Update PO status jika belum converted
            if (!$po->isConverted()) {
                $po->update(['status' => 'converted']);
            }

            return $tx;
        });
    }
}
