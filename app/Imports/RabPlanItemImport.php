<?php

namespace App\Imports;

use App\Models\Material;
use App\Models\RabPlan;
use App\Models\RabPlanItem;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class RabPlanItemImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $errors   = [];
    public int   $imported = 0;
    public int   $skipped  = 0;

    public function __construct(
        protected RabPlan $rabPlan,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $rowIndex => $row) {
            $line = $rowIndex + 2;

            $data = [
                'material_name' => trim($row['nama_material'] ?? ''),
                'unit_name'     => trim($row['satuan'] ?? ''),
                'qty_planned'   => $row['qty_rencana'] ?? null,
                'price_planned' => $row['harga_rencana'] ?? null,
                'notes'         => trim($row['catatan'] ?? ''),
            ];

            $validator = Validator::make($data, [
                'material_name' => 'required|string|max:255',
                'unit_name'     => 'required|string|max:100',
                'qty_planned'   => 'required|numeric|min:0.01',
                'price_planned' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                $this->errors[] = "Baris {$line}: " . implode(', ', $validator->errors()->all());
                $this->skipped++;
                continue;
            }

            // Try to match material
            $material = Material::where('name', $data['material_name'])->first();

            // Get unit snapshot
            $unitName = $data['unit_name'];
            if ($material && $material->unit) {
                $unitName = $material->unit->name;
            }

            $subtotal = (float) $data['qty_planned'] * (float) $data['price_planned'];

            RabPlanItem::create([
                'rab_plan_id'             => $this->rabPlan->id,
                'material_id'             => $material?->id,
                'material_name_snapshot'  => $data['material_name'],
                'unit_name_snapshot'      => $unitName,
                'qty_planned'             => $data['qty_planned'],
                'price_planned'           => $data['price_planned'],
                'subtotal_planned'        => $subtotal,
                'notes'                   => $data['notes'] ?: null,
            ]);

            $this->imported++;
        }
    }
}
