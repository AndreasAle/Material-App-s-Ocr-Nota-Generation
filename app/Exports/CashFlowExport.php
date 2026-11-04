<?php

namespace App\Exports;

use App\Services\Reports\CashFlowReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashFlowExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        protected ?int $projectId = null,
        protected ?int $year      = null,
    ) {
        $this->year ??= (int) now()->year;
    }

    public function collection()
    {
        $service = app(CashFlowReportService::class);
        $monthly = $service->buildYearly($this->year);

        $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        return collect($monthly)->map(fn ($m, $idx) => [
            $months[$idx - 1],
            (float) $m['cash_in'],
            (float) $m['cash_out_purchase'],
            (float) $m['cash_out_subcontract'],
            (float) $m['cash_out'],
            (float) $m['net_flow'],
        ]);
    }

    public function headings(): array
    {
        return ['Bulan', 'Cash In (Rp)', 'Pembelian (Rp)', 'Subkontrak (Rp)', 'Total Cash Out (Rp)', 'Net Flow (Rp)'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Arus Kas';
    }
}
