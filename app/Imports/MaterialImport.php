<?php

namespace App\Imports;

use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class MaterialImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $errors   = [];
    public int   $imported = 0;
    public int   $skipped  = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $rowIndex => $row) {
            $line = $rowIndex + 2; // +2 because row 1 is heading

            $data = [
                'name'          => trim($row['nama_material'] ?? ''),
                'unit_name'     => trim($row['satuan'] ?? ''),
                'category_name' => trim($row['kategori'] ?? ''),
                'default_price' => $row['harga_default'] ?? null,
                'description'   => trim($row['deskripsi'] ?? ''),
                'is_active'     => true,
            ];

            // Validate
            $validator = Validator::make($data, [
                'name'      => 'required|string|max:255',
                'unit_name' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                $this->errors[] = "Baris {$line}: " . implode(', ', $validator->errors()->all());
                $this->skipped++;
                continue;
            }

            // Skip duplicate names
            if (Material::where('name', $data['name'])->exists()) {
                $this->errors[] = "Baris {$line}: Material \"{$data['name']}\" sudah ada, dilewati.";
                $this->skipped++;
                continue;
            }

            // Find or create Unit
            $unit = Unit::firstOrCreate(
                ['name' => $data['unit_name']],
                ['is_active' => true]
            );

            // Find or create Category (optional)
            $categoryId = null;
            if (! empty($data['category_name'])) {
                $category   = MaterialCategory::firstOrCreate(
                    ['name' => $data['category_name']],
                    ['is_active' => true]
                );
                $categoryId = $category->id;
            }

            Material::create([
                'name'          => $data['name'],
                'unit_id'       => $unit->id,
                'category_id'   => $categoryId,
                'default_price' => is_numeric($data['default_price']) ? $data['default_price'] : null,
                'description'   => $data['description'] ?: null,
                'is_active'     => true,
            ]);

            $this->imported++;
        }
    }
}
