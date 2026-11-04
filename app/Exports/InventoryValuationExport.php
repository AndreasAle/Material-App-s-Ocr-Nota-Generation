<?php

namespace App\Exports;

use App\Services\Reports\InventoryValuationService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryValuationExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        protected ?int    $warehouseId = null,
        protected ?string $asOfDate    = null,
    ) {}

    public function collection()
    {
        $service = app(InventoryValuationService::class);
        $items   = $service->calculate($this->warehouseId, $this->asOfDate);

        return $items->map(fn ($row) => [
            $row->material_name_snapshot,
            $row->unit_name_snapshot,
            (float) $row->qty_on_hand,
            (float) $row->avg_cost,
            (float) $row->total_value,
        ]);
    }

    public function headings(): array
    {
        return ['Material', 'Satuan', 'Qty On Hand', 'Harga Rata-rata (Rp)', 'Total Nilai (Rp)'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Nilai Stok';
    }
}
