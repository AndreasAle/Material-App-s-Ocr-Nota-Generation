<?php

namespace App\Exports\Sheets;

use App\Models\PurchaseTransaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class PurchaseTransactionsSummarySheet implements FromArray, ShouldAutoSize, WithTitle, WithEvents
{
    public function __construct(
        protected ?string $status = null,
        protected ?string $startDate = null,
        protected ?string $endDate = null,
    ) {
    }

    public function array(): array
    {
        $transactions = PurchaseTransaction::query()
            ->with(['items', 'supplier', 'project'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $this->endDate))
            ->get();

        $totalTransactions = $transactions->count();
        $totalItems = $transactions->sum(fn ($trx) => $trx->items->count());
        $totalQty = (float) $transactions->sum(fn ($trx) => $trx->items->sum('qty'));
        $totalGrandTotal = (float) $transactions->sum('grand_total');
        $avgTransaction = $totalTransactions > 0 ? ($totalGrandTotal / $totalTransactions) : 0;

        $topSupplier = $transactions
            ->groupBy(fn ($trx) => $trx->supplier?->name ?: 'Tanpa Supplier')
            ->map(fn (Collection $rows) => (float) $rows->sum('grand_total'))
            ->sortDesc();

        $topProject = $transactions
            ->groupBy(fn ($trx) => $trx->project?->name ?: 'Tanpa Proyek')
            ->map(fn (Collection $rows) => (float) $rows->sum('grand_total'))
            ->sortDesc();

        return [
            ['LAPORAN PEMBELIAN MATERIAL'],
            ['Periode', ($this->startDate ?: '-') . ' s/d ' . ($this->endDate ?: '-')],
            ['Status Filter', $this->status ?: 'Semua'],
            [''],
            ['KPI', 'Nilai'],
            ['Total Transaksi', $totalTransactions],
            ['Total Item', $totalItems],
            ['Total Qty', $totalQty],
            ['Total Grand Total', $totalGrandTotal],
            ['Rata-rata per Transaksi', $avgTransaction],
            [''],
            ['Supplier Tertinggi', 'Nilai'],
            [$topSupplier->keys()->first() ?: '-', (float) ($topSupplier->first() ?: 0)],
            [''],
            ['Proyek Tertinggi', 'Nilai'],
            [$topProject->keys()->first() ?: '-', (float) ($topProject->first() ?: 0)],
        ];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:B1');
                $sheet->freezePane('A5');

                $sheet->getStyle('A1:B1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '0F172A'],
                    ],
                ]);

                $sheet->getStyle('A5:B5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '1E3A8A'],
                    ],
                ]);

                $sheet->getStyle('A12:B12')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '1E3A8A'],
                    ],
                ]);

                $sheet->getStyle('A15:B15')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '1E3A8A'],
                    ],
                ]);

                $sheet->setAutoFilter('A5:B10');

                $sheet->getStyle('B8')->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('B9')->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle('B10')->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle('B13')->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle('B16')->getNumberFormat()->setFormatCode('"Rp" #,##0');
            },
        ];
    }
}