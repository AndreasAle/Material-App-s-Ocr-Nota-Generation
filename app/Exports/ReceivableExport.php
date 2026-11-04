<?php

namespace App\Exports;

use App\Services\Reports\ReceivableReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReceivableExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        protected ?int $projectId = null,
    ) {}

    public function collection()
    {
        $service = app(ReceivableReportService::class);
        $list    = $service->getOutstandingList($this->projectId);

        return $list->map(fn ($inv) => [
            $inv->invoice_no,
            $inv->client_name,
            $inv->project?->name ?? '—',
            $inv->invoice_date?->format('d/m/Y'),
            $inv->due_date?->format('d/m/Y') ?? '—',
            (float) $inv->grand_total,
            (float) $inv->paid_amount,
            (float) $inv->outstanding,
            $inv->is_overdue ? $inv->days_overdue : 0,
            $inv->payment_status,
        ]);
    }

    public function headings(): array
    {
        return [
            'No. Invoice', 'Klien', 'Proyek', 'Tgl Invoice', 'Jatuh Tempo',
            'Total (Rp)', 'Terbayar (Rp)', 'Outstanding (Rp)', 'Hari Lewat', 'Status',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Piutang';
    }
}
