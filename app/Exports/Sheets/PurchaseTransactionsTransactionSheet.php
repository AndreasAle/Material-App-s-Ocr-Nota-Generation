<?php

namespace App\Exports\Sheets;

use App\Exports\Sheets\Concerns\HasProfessionalSheetStyling;
use App\Models\PurchaseTransaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseTransactionsTransactionSheet implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    ShouldAutoSize,
    WithStyles,
    WithEvents,
    WithColumnFormatting
{
    use HasProfessionalSheetStyling;

    protected int $rowCount = 0;

    public function __construct(
        protected ?string $status = null,
        protected ?string $startDate = null,
        protected ?string $endDate = null,
    ) {
    }

    public function collection(): Collection
    {
        $rows = PurchaseTransaction::query()
            ->with(['supplier', 'project', 'user', 'items'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $this->endDate))
            ->latest('transaction_date')
            ->get()
            ->values();

        $this->rowCount = $rows->count();

        return $rows->map(function ($row, $index) {
            $row->export_no = $index + 1;
            return $row;
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'No Transaksi',
            'Tanggal',
            'Supplier',
            'Proyek',
            'No Nota',
            'Jumlah Item',
            'Total Qty',
            'Grand Total',
            'Status',
            'Diinput Oleh',
            'Catatan',
            'Dibuat Pada',
        ];
    }

    public function map($row): array
    {
        return [
            $row->export_no,
            $row->transaction_no,
            optional($row->transaction_date)->format('Y-m-d'),
            $row->supplier?->name ?: '-',
            $row->project?->name ?: '-',
            $row->note_number ?: '-',
            $row->items->count(),
            (float) $row->items->sum('qty'),
            (float) $row->grand_total,
            ucfirst((string) $row->status),
            $row->user?->name ?: '-',
            $row->notes ?: '-',
            optional($row->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    public function title(): string
    {
        return 'Transaksi';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => $this->headerStyle(),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '#,##0.00',
            'I' => '"Rp" #,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $dataStartRow = 2;
                $dataEndRow = $this->rowCount > 0 ? $this->rowCount + 1 : 2;
                $totalRow = $dataEndRow + 2;

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:M{$dataEndRow}");

                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->mergeCells("A{$totalRow}:G{$totalRow}");
                $sheet->setCellValue("H{$totalRow}", "=SUM(H{$dataStartRow}:H{$dataEndRow})");
                $sheet->setCellValue("I{$totalRow}", "=SUM(I{$dataStartRow}:I{$dataEndRow})");

                $sheet->getStyle("A{$totalRow}:G{$totalRow}")->applyFromArray($this->totalLabelStyle());
                $sheet->getStyle("H{$totalRow}:I{$totalRow}")->applyFromArray($this->totalValueStyle());

                $sheet->getStyle("H{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("I{$totalRow}")->getNumberFormat()->setFormatCode('"Rp" #,##0');

                $sheet->getColumnDimension('L')->setWidth(30);
                $sheet->getStyle("L2:L{$dataEndRow}")->getAlignment()->setWrapText(true);
            },
        ];
    }
}