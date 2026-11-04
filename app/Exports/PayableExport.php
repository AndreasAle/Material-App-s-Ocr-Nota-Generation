<?php

namespace App\Exports;

use App\Services\Reports\PayableReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayableExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        protected ?int $supplierId = null,
    ) {}

    public function collection()
    {
        $service = app(PayableReportService::class);
        $list    = $service->getOutstandingList($this->supplierId);

        return $list->map(fn ($tx) => [
            $tx->transaction_no,
            $tx->supplier?->name ?? '—',
            $tx->transaction_date?->format('d/m/Y'),
            isset($tx->due_date) ? \Carbon\Carbon::parse($tx->due_date)->format('d/m/Y') : '—',
            (float) $tx->grand_total,
            (float) ($tx->paid_amount ?? 0),
            (float) $tx->outstanding,
            $tx->is_overdue ? $tx->days_overdue : 0,
            $tx->payment_status ?? 'unpaid',
        ]);
    }

    public function headings(): array
    {
        return [
            'No. Transaksi', 'Supplier', 'Tgl Transaksi', 'Jatuh Tempo',
            'Total (Rp)', 'Terbayar (Rp)', 'Outstanding (Rp)', 'Hari Lewat', 'Status',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Hutang';
    }
}
