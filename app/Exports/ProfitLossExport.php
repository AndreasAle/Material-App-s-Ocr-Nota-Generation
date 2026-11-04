<?php

namespace App\Exports;

use App\Services\Reports\ProfitLossReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        protected int $year,
    ) {}

    public function collection()
    {
        $service = app(ProfitLossReportService::class);
        $data    = $service->byProject(
            "{$this->year}-01-01",
            "{$this->year}-12-31"
        );

        return collect($data)->map(fn ($row) => [
            $row['project_name'],
            (float) $row['revenue'],
            (float) $row['material_cost'],
            (float) $row['subcontract_cost'],
            (float) $row['total_cogs'],
            (float) $row['gross_profit'],
            round($row['gross_margin_pct'], 2) . '%',
        ]);
    }

    public function headings(): array
    {
        return [
            'Proyek', 'Pendapatan (Rp)', 'Biaya Material (Rp)', 'Biaya Subkontrak (Rp)',
            'Total HPP (Rp)', 'Laba Kotor (Rp)', 'Margin (%)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return "L&R {$this->year}";
    }
}
