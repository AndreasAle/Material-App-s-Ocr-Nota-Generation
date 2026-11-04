<?php

namespace App\Imports;

use App\Models\Material;
use App\Models\Project;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionItem;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class PurchaseTransactionImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $errors   = [];
    public int   $imported = 0;
    public int   $skipped  = 0;

    public function __construct(
        protected int $userId,
    ) {}

    public function collection(Collection $rows): void
    {
        // Group rows by transaction_no — each unique no = one transaction
        $grouped = $rows->groupBy(fn ($row) => trim($row['nomor_transaksi'] ?? 'AUTO'));

        foreach ($grouped as $txNo => $items) {
            $firstRow = $items->first();
            $line     = 2; // approximate

            // Validate first row fields
            $txData = [
                'transaction_date' => trim($firstRow['tanggal'] ?? ''),
                'supplier_name'    => trim($firstRow['supplier'] ?? ''),
                'project_name'     => trim($firstRow['proyek'] ?? ''),
                'notes'            => trim($firstRow['catatan'] ?? ''),
            ];

            if (empty($txData['transaction_date'])) {
                $this->errors[] = "Transaksi \"{$txNo}\": Kolom 'tanggal' kosong, dilewati.";
                $this->skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($txNo, $items, $txData) {
                    // Find or resolve supplier
                    $supplierId = null;
                    if (! empty($txData['supplier_name'])) {
                        $supplier   = Supplier::where('name', $txData['supplier_name'])->first()
                            ?? Supplier::firstOrCreate(['name' => $txData['supplier_name']], ['is_active' => true]);
                        $supplierId = $supplier->id;
                    }

                    // Find project
                    $projectId = null;
                    if (! empty($txData['project_name'])) {
                        $project   = Project::where('name', $txData['project_name'])->first();
                        $projectId = $project?->id;
                    }

                    // Generate transaction_no
                    $autoNo = $txNo === 'AUTO' || str_starts_with($txNo, 'AUTO')
                        ? $this->generateTxNo()
                        : $txNo;

                    // Skip if already exists
                    if (PurchaseTransaction::where('transaction_no', $autoNo)->exists()) {
                        $this->skipped++;
                        return;
                    }

                    // Build items and total
                    $txItems   = [];
                    $totalAmt  = 0;
                    foreach ($items as $row) {
                        $materialName = trim($row['nama_material'] ?? '');
                        $unitName     = trim($row['satuan'] ?? '');
                        $qty          = (float) ($row['qty'] ?? 0);
                        $unitPrice    = (float) ($row['harga_satuan'] ?? 0);
                        $subtotal     = $qty * $unitPrice;

                        if (empty($materialName) || $qty <= 0) continue;

                        $material = Material::where('name', $materialName)->first();

                        $txItems[] = [
                            'material_id'            => $material?->id,
                            'material_name_snapshot' => $materialName,
                            'unit_name_snapshot'     => $unitName,
                            'qty'                    => $qty,
                            'unit_price'             => $unitPrice,
                            'subtotal'               => $subtotal,
                        ];
                        $totalAmt += $subtotal;
                    }

                    if (empty($txItems)) {
                        $this->skipped++;
                        return;
                    }

                    $tx = PurchaseTransaction::create([
                        'transaction_no'   => $autoNo,
                        'transaction_date' => $txData['transaction_date'],
                        'supplier_id'      => $supplierId,
                        'project_id'       => $projectId,
                        'user_id'          => $this->userId,
                        'grand_total'      => $totalAmt,
                        'notes'            => $txData['notes'] ?: null,
                        'status'           => 'completed',
                        'approval_status'  => 'approved',
                    ]);

                    foreach ($txItems as $item) {
                        PurchaseTransactionItem::create(array_merge($item, ['purchase_transaction_id' => $tx->id]));
                    }

                    $this->imported++;
                });
            } catch (\Throwable $e) {
                $this->errors[] = "Transaksi \"{$txNo}\": " . $e->getMessage();
                $this->skipped++;
            }
        }
    }

    private function generateTxNo(): string
    {
        $prefix = 'TRX-' . now()->format('Ym') . '-';
        $last   = PurchaseTransaction::where('transaction_no', 'like', $prefix . '%')
            ->orderByDesc('transaction_no')
            ->value('transaction_no');
        $seq    = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
