<?php

namespace App\Services\Purchases;

use App\Models\Material;
use App\Models\PurchaseAttachment;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionItem;
use App\Models\RabPlan;
use App\Services\Accounting\SupplierPaymentService;
use App\Services\Tax\TaxAutoCreateService;
use App\Services\WhatsApp\PurchaseWhatsAppService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Models\Unit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PurchaseTransactionService
{
    public function __construct(
        protected PurchaseNumberService $purchaseNumberService,
        protected SupplierPriceHistoryService $supplierPriceHistoryService,
        protected PurchaseWhatsAppService $purchaseWhatsApp,
        protected TaxAutoCreateService $taxAutoCreate,
        protected SupplierPaymentService $supplierPayments,
    ) {
    }

    public function store(array $data, int $userId): PurchaseTransaction
    {
        return $this->withAttachmentCleanup(fn () => DB::transaction(function () use ($data, $userId) {
            $items = Arr::pull($data, 'items', []);
            /** @var array<int, UploadedFile> $attachments */
            $attachments = Arr::pull($data, 'attachments', []);

            if (! empty($data['auto_create_missing_materials'])) {
                $this->autoCreateMissingMaterials($items);
            }

            $grandTotal = 0;
            $normalizedItems = [];

            foreach ($items as $item) {
                $material = ! empty($item['material_id'])
                    ? Material::with('unit')->find($item['material_id'])
                    : null;

                $materialName = $item['material_name_snapshot'] ?? $material?->name;
                $unitName = $item['unit_name_snapshot'] ?? $material?->unit?->name ?? '-';
                $qty = (float) $item['qty'];
                $price = (float) $item['price'];
                $subtotal = $qty * $price;
                $grandTotal += $subtotal;

                $normalizedItems[] = [
                    'material_id' => $item['material_id'] ?? null,
                    'material_name_snapshot' => $materialName,
                    'unit_name_snapshot' => $unitName,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $transaction = PurchaseTransaction::create([
                'transaction_no' => $this->purchaseNumberService->generate($data['transaction_date'] ?? null),
                'transaction_date' => $data['transaction_date'],
                'supplier_id' => $data['supplier_id'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'user_id' => $userId,
                'note_number' => $data['note_number'] ?? null,
                'grand_total' => $grandTotal,
                'attachment_count' => count($attachments),
                'input_source' => $data['input_source'] ?? 'manual',
                'scan_source_type' => $data['scan_source_type'] ?? null,
                'scan_reference_id' => $data['scan_reference_id'] ?? null,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
                // Jatuh tempo hanya berarti bila nota belum dibayar
                'due_date' => empty($data['paid_in_full']) ? ($data['due_date'] ?? null) : null,
            ]);

            $transaction->items()->createMany($normalizedItems);

            foreach ($attachments as $file) {
                $this->storeAttachment($transaction, $file, $userId);
            }

            // Isi subtotal / sisa hutang / status bayar sejak awal agar laporan hutang & pembayaran konsisten
            $transaction->update(['subtotal' => $grandTotal]);

            // Barang datang bersama nota → langsung buat penerimaan (stok bertambah)
            if (! empty($data['mark_received'])) {
                $this->receiveAll($transaction, $userId);
            }

            // Dibayar lunas di tempat → catat pembayaran supplier
            if (! empty($data['paid_in_full']) && $grandTotal > 0) {
                $this->supplierPayments->store($transaction, [
                    'payment_date'   => $data['transaction_date'],
                    'amount'         => $grandTotal,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'notes'          => 'Lunas saat barang datang',
                ], $userId);
            }

            $this->supplierPayments->syncTransactionPaidAmount($transaction);

            $loaded = $transaction->load(['supplier', 'project', 'user', 'items', 'attachments']);

            $this->supplierPriceHistoryService->recordFromTransaction($transaction);
            $this->checkBudgetAlerts($transaction);
            $this->taxAutoCreate->createPpnMasukanDraft($loaded, $userId);

            return $loaded;
        }));
    }

    public function update(PurchaseTransaction $transaction, array $data): PurchaseTransaction
{
    return $this->withAttachmentCleanup(fn () => DB::transaction(function () use ($transaction, $data) {
        $items = Arr::pull($data, 'items', []);
        /** @var array<int, UploadedFile> $attachments */
        $attachments = Arr::pull($data, 'attachments', []);

        if (!is_array($items) || count($items) === 0) {
            throw new \RuntimeException('Item transaksi tidak boleh kosong saat update.');
        }

        $grandTotal = 0;
        $normalizedItems = [];

        foreach ($items as $item) {
            $material = !empty($item['material_id'])
                ? Material::with('unit')->find($item['material_id'])
                : null;

            $materialName = $item['material_name_snapshot'] ?? $material?->name;
            $unitName = $item['unit_name_snapshot'] ?? $material?->unit?->name ?? '-';
            $qty = (float) ($item['qty'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            $subtotal = $qty * $price;

            $grandTotal += $subtotal;

            $normalizedItems[] = [
                'material_id' => $item['material_id'] ?? null,
                'material_name_snapshot' => $materialName,
                'unit_name_snapshot' => $unitName,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $subtotal,
                'notes' => $item['notes'] ?? null,
            ];
        }

        $transaction->update([
            'transaction_date' => $data['transaction_date'],
            'supplier_id' => $data['supplier_id'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'note_number' => $data['note_number'] ?? null,
            'input_source' => $data['input_source'] ?? $transaction->input_source ?? 'manual',
            'scan_source_type' => $data['scan_source_type'] ?? $transaction->scan_source_type,
            'scan_reference_id' => $data['scan_reference_id'] ?? $transaction->scan_reference_id,
            'grand_total' => $grandTotal,
            'subtotal' => $grandTotal,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        // Isi transaksi berubah → persetujuan lama tidak berlaku lagi, harus diajukan ulang
        if (in_array($transaction->approval_status, ['pending', 'approved', 'rejected'], true)) {
            $transaction->update([
                'approval_status' => 'draft',
                'approved_by' => null,
                'approved_at' => null,
            ]);
        }

        if (count($normalizedItems) > 0) {
            $transaction->items()->delete();
            $transaction->items()->createMany($normalizedItems);
        }

        foreach ($attachments as $file) {
            $this->storeAttachment($transaction, $file, (int) auth()->id());
        }

        $transaction->update([
            'attachment_count' => $transaction->attachments()->count(),
        ]);
        $this->supplierPayments->syncTransactionPaidAmount($transaction);
        $this->supplierPriceHistoryService->recordFromTransaction($transaction);

        $result = $transaction->load(['supplier', 'project', 'user', 'items', 'attachments']);
        $this->checkBudgetAlerts($transaction);

        return $result;
    }));
}

    public function deleteAttachment(PurchaseAttachment $attachment): void
    {
        $disk = $attachment->storageDisk();

        DB::transaction(function () use ($attachment) {
            $transaction = $attachment->purchaseTransaction;
            $attachment->delete();

            if ($transaction) {
                $transaction->update([
                    'attachment_count' => $transaction->attachments()->count(),
                ]);
            }
        });

        // Hapus file fisik setelah DB commit, supaya tidak ada record yang menunjuk file hilang
        Storage::disk($disk)->delete($attachment->file_path);
    }

    /** @var array<int, string> path lampiran yang ditulis selama proses simpan berjalan */
    protected array $storedAttachmentPaths = [];

    /**
     * Lampiran disimpan di disk privat (hanya bisa dibuka lewat route yang butuh login).
     */
    protected function storeAttachment(PurchaseTransaction $transaction, UploadedFile $file, ?int $userId): PurchaseAttachment
    {
        $path = $file->store('purchase-attachments', 'private');
        $this->storedAttachmentPaths[] = $path;

        return PurchaseAttachment::create([
            'purchase_transaction_id' => $transaction->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize() ?: 0,
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => $userId,
        ]);
    }

    /**
     * Jika transaksi DB gagal, file yang sudah terlanjur ditulis ikut dihapus.
     */
    protected function withAttachmentCleanup(callable $callback): mixed
    {
        $this->storedAttachmentPaths = [];

        try {
            return $callback();
        } catch (\Throwable $e) {
            foreach ($this->storedAttachmentPaths as $path) {
                Storage::disk('private')->delete($path);
            }

            throw $e;
        } finally {
            $this->storedAttachmentPaths = [];
        }
    }

    private function checkBudgetAlerts(PurchaseTransaction $transaction): void
    {
        if (empty($transaction->project_id)) {
            return;
        }

        $activePlans = RabPlan::query()
            ->where('project_id', $transaction->project_id)
            ->where('status', 'active')
            ->get();

        foreach ($activePlans as $plan) {
            $totalBudget = (float) $plan->total_budget;

            if ($totalBudget <= 0) {
                continue;
            }

            $totalSpent = (float) PurchaseTransactionItem::query()
                ->whereHas('purchaseTransaction', function ($q) use ($plan) {
                    $q->where('project_id', $plan->project_id)
                      ->whereDate('transaction_date', '>=', $plan->period_start)
                      ->when(
                          $plan->period_end,
                          fn ($q) => $q->whereDate('transaction_date', '<=', $plan->period_end)
                      );
                })
                ->sum('subtotal');

            $pct = ($totalSpent / $totalBudget) * 100;

            // Kirim alert hanya saat melewati threshold 80% dan 100%
            if ($pct >= 100 || ($pct >= 80 && $pct < 100)) {
                $this->purchaseWhatsApp->notifyBudgetAlert($plan, $pct);
            }
        }
    }

    /**
     * Penerimaan penuh semua item transaksi (dipakai saat barang datang bersama nota).
     */
    protected function receiveAll(PurchaseTransaction $transaction, int $userId): void
    {
        $items = $transaction->items()->get();

        app(GoodsReceiptService::class)->store($transaction, [
            'receipt_no'   => app(GoodsReceiptService::class)->generateReceiptNo($transaction),
            'receipt_date' => $transaction->transaction_date->toDateString(),
            'received_by'  => auth()->user()?->name ?? 'Sistem',
            'notes'        => 'Otomatis: barang diterima bersama nota',
            'items'        => $items->map(fn ($i) => [
                'purchase_transaction_item_id' => $i->id,
                'material_name_snapshot'       => $i->material_name_snapshot,
                'unit_name_snapshot'           => $i->unit_name_snapshot,
                'qty_ordered'                  => $i->qty,
                'qty_received'                 => $i->qty,
            ])->all(),
        ], $userId);

        $transaction->update(['goods_receipt_status' => 'complete']);
    }

    /**
     * Baris nota yang belum cocok dengan master barang dibuatkan barang baru
     * (atau dicocokkan ke barang yang kode/namanya sama).
     */
    protected function autoCreateMissingMaterials(array &$items): void
    {
        foreach ($items as &$item) {
            $materialCode = trim((string) ($item['material_code'] ?? ''));
            $materialName = trim((string) ($item['material_name_snapshot'] ?? ''));
            $unitName = trim((string) ($item['unit_name_snapshot'] ?? '')) ?: 'Pcs';

            if (! empty($item['material_id']) || $materialName === '') {
                continue;
            }

            $normalizedCode = strtoupper(str_replace(['/', ' ', '-', '.'], '', $materialCode));
            $existing = Material::query()
                ->when($normalizedCode !== '', fn ($q) => $q->whereRaw("UPPER(REPLACE(REPLACE(REPLACE(REPLACE(code, '/', ''), ' ', ''), '-', ''), '.', '')) = ?", [$normalizedCode]))
                ->when($normalizedCode === '', fn ($q) => $q->whereRaw('LOWER(name) = ?', [mb_strtolower($materialName)]))
                ->first();

            if ($existing) {
                $item['material_id'] = $existing->id;
                $item['material_code'] = $existing->code;
                continue;
            }

            $unit = Unit::query()->whereRaw('LOWER(name) = ? OR LOWER(code) = ?', [mb_strtolower($unitName), mb_strtolower($unitName)])->first()
                ?? Unit::create(['name' => $unitName, 'code' => $this->uniqueUnitCode($unitName)]);

            $category = \App\Models\MaterialCategory::firstOrCreate(
                ['slug' => 'dari-nota'],
                ['name' => 'Dari Nota (perlu dicek)', 'is_active' => true]
            );

            $material = Material::create([
                'material_category_id' => $category->id,
                'unit_id' => $unit->id,
                'code' => $materialCode !== '' ? $materialCode : $this->generateMaterialCode(),
                'name' => $materialName,
                'default_price' => (float) ($item['price'] ?? 0),
                'is_active' => true,
            ]);

            $item['material_id'] = $material->id;
            $item['material_code'] = $material->code;
        }
        unset($item);
    }

    private function uniqueUnitCode(string $unitName): string
    {
        $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $unitName) ?: 'UNIT', 0, 12));
        $code = $base;
        $i = 2;
        while (Unit::where('code', $code)->exists()) {
            $code = $base . $i++;
        }

        return $code;
    }

    private function generateMaterialCode(): string
    {
        do {
            $code = 'BRG-' . strtoupper(Str::random(6));
        } while (Material::where('code', $code)->exists());

        return $code;
    }
}