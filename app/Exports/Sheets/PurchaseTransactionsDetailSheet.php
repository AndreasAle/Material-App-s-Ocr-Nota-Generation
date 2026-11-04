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

class PurchaseTransactionsDetailSheet implements
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
            ->with([
                'supplier',
                'project',
                'user',
                'items.material.unit',
            ])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $this->endDate))
            ->latest('transaction_date')
            ->get()
            ->flatMap(function ($transaction) {
                return $transaction->items->map(function ($item) use ($transaction) {
                    return [
                        'transaction' => $transaction,
                        'item' => $item,
                    ];
                });
            })
            ->values();

        $this->rowCount = $rows->count();

        return $rows->map(function ($row, $index) {
            $row['export_no'] = $index + 1;
            return $row;
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'No Transaksi',
            'No Nota',
            'Supplier',
            'Proyek',
            'Status',
            'Kode Material',
            'Nama Material',
            'Satuan',
            'Qty',
            'Harga',
            'Subtotal',
            'Catatan Item',
            'Diinput Oleh',
        ];
    }

    public function map($row): array
    {
        $transaction = $row['transaction'];
        $item = $row['item'];

        return [
            $row['export_no'],
            optional($transaction->transaction_date)->format('Y-m-d'),
            $transaction->transaction_no,
            $transaction->note_number ?: '-',
            $transaction->supplier?->name ?: '-',
            $transaction->project?->name ?: '-',
            ucfirst((string) $transaction->status),
            $item->material?->code ?: '-',
            $item->material_name_snapshot ?: $item->material?->name ?: '-',
            $item->unit_name_snapshot ?: $item->material?->unit?->name ?: '-',
            (float) $item->qty,
            (float) $item->price,
            (float) $item->subtotal,
            $item->notes ?: '-',
            $transaction->user?->name ?: '-',
        ];
    }

    public function title(): string
    {
        return 'Detail Item';
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
            'K' => '#,##0.00',
            'L' => '"Rp" #,##0',
            'M' => '"Rp" #,##0',
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
                $sheet->setAutoFilter("A1:O{$dataEndRow}");

                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->mergeCells("A{$totalRow}:J{$totalRow}");
                $sheet->setCellValue("K{$totalRow}", "=SUM(K{$dataStartRow}:K{$dataEndRow})");
                $sheet->setCellValue("L{$totalRow}", "=SUM(L{$dataStartRow}:L{$dataEndRow})");
                $sheet->setCellValue("M{$totalRow}", "=SUM(M{$dataStartRow}:M{$dataEndRow})");

                $sheet->getStyle("A{$totalRow}:J{$totalRow}")->applyFromArray($this->totalLabelStyle());
                $sheet->getStyle("K{$totalRow}:M{$totalRow}")->applyFromArray($this->totalValueStyle());

                $sheet->getStyle("K{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("L{$totalRow}:M{$totalRow}")->getNumberFormat()->setFormatCode('"Rp" #,##0');

                $sheet->getColumnDimension('I')->setWidth(30);
                $sheet->getColumnDimension('N')->setWidth(28);

                $sheet->getStyle("I2:I{$dataEndRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("N2:N{$dataEndRow}")->getAlignment()->setWrapText(true);
            },
        ];
    }
}