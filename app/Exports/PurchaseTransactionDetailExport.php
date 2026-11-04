<?php

namespace App\Exports;

use App\Models\PurchaseTransaction;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class PurchaseTransactionDetailExport implements FromView, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(
        protected PurchaseTransaction $purchaseTransaction
    ) {
    }

    public function title(): string
    {
        return 'Laporan Transaksi';
    }

    public function view(): View
    {
        return view('purchase-transactions.exports.excel-detail', [
            'purchaseTransaction' => $this->purchaseTransaction,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $transaction = $this->purchaseTransaction;

                $itemCount = $transaction->items->count();
                $attachmentCount = $transaction->attachments->count();

                $itemHeaderRow = 15;
                $itemStartRow = 16;
                $itemEndRow = max($itemStartRow, $itemStartRow + $itemCount - 1);

                $attachmentHeaderRow = $itemEndRow + 4;
                $attachmentStartRow = $attachmentHeaderRow + 1;
                $attachmentEndRow = max($attachmentStartRow, $attachmentStartRow + $attachmentCount - 1);

                $lastRow = max($attachmentEndRow, 28);

                /*
                |--------------------------------------------------------------------------
                | Page setup
                |--------------------------------------------------------------------------
                */
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);

                $sheet->getPageMargins()
                    ->setTop(0.4)
                    ->setRight(0.25)
                    ->setLeft(0.25)
                    ->setBottom(0.4);

                $sheet->freezePane('A16');

                /*
                |--------------------------------------------------------------------------
                | Column widths
                |--------------------------------------------------------------------------
                */
                $sheet->getColumnDimension('A')->setWidth(28);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(16);
                $sheet->getColumnDimension('E')->setWidth(16);
                $sheet->getColumnDimension('F')->setWidth(42);

                /*
                |--------------------------------------------------------------------------
                | Title
                |--------------------------------------------------------------------------
                */
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');

                $sheet->setCellValue('A1', 'LAPORAN DETAIL TRANSAKSI PEMBELIAN');
                $sheet->setCellValue('A2', 'Dokumen transaksi pembelian material');

                $sheet->getStyle('A1:F2')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A1:F1')->getFont()
                    ->setBold(true)
                    ->setSize(16);

                $sheet->getStyle('A2:F2')->getFont()
                    ->setItalic(true)
                    ->setSize(10)
                    ->getColor()->setRGB('64748B');

                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(20);

                /*
                |--------------------------------------------------------------------------
                | Header info block
                |--------------------------------------------------------------------------
                */
                $sheet->mergeCells('A4:A11');
                $sheet->setCellValue('A4', 'INFORMASI TRANSAKSI');

                $sheet->getStyle('A4:A11')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'textRotation' => 90,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '0F172A'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                $sheet->getStyle('B4:C11')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('D4:F11')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('B4:B11')->getFont()->setBold(true);
                $sheet->getStyle('D4:D11')->getFont()->setBold(true);

                /*
                |--------------------------------------------------------------------------
                | Item section title
                |--------------------------------------------------------------------------
                */
                $sheet->mergeCells("A13:F13");
                $sheet->setCellValue('A13', 'RINCIAN MATERIAL');

                $sheet->getStyle('A13:F13')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E3A8A'],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Item table header
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle("A{$itemHeaderRow}:F{$itemHeaderRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '334155'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Item table body
                |--------------------------------------------------------------------------
                */
                if ($itemCount > 0) {
                    $sheet->getStyle("A{$itemStartRow}:F{$itemEndRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'E2E8F0'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_TOP,
                        ],
                    ]);

                    for ($row = $itemStartRow; $row <= $itemEndRow; $row++) {
                        if (($row - $itemStartRow) % 2 === 0) {
                            $sheet->getStyle("A{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID);
                            $sheet->getStyle("A{$row}:F{$row}")->getFill()->getStartColor()->setRGB('F8FAFC');
                        }
                    }

                    $sheet->getStyle("C{$itemStartRow}:C{$itemEndRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle("D{$itemStartRow}:E{$itemEndRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    $sheet->getStyle("D{$itemStartRow}:E{$itemEndRow}")
                        ->getNumberFormat()->setFormatCode('#,##0');
                }

                /*
                |--------------------------------------------------------------------------
                | Grand total
                |--------------------------------------------------------------------------
                */
                $grandTotalRow = $itemEndRow + 2;

                $sheet->mergeCells("A{$grandTotalRow}:D{$grandTotalRow}");
                $sheet->setCellValue("A{$grandTotalRow}", 'GRAND TOTAL');
                $sheet->setCellValue("E{$grandTotalRow}", $transaction->grand_total);
                $sheet->mergeCells("E{$grandTotalRow}:F{$grandTotalRow}");

                $sheet->getStyle("A{$grandTotalRow}:F{$grandTotalRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF3C7'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D6D3D1'],
                        ],
                    ],
                ]);

                $sheet->getStyle("A{$grandTotalRow}:D{$grandTotalRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("E{$grandTotalRow}:F{$grandTotalRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("E{$grandTotalRow}:F{$grandTotalRow}")
                    ->getNumberFormat()->setFormatCode('#,##0');

                /*
                |--------------------------------------------------------------------------
                | Attachment section
                |--------------------------------------------------------------------------
                */
                $sheet->mergeCells("A{$attachmentHeaderRow}:F{$attachmentHeaderRow}");
                $sheet->setCellValue("A{$attachmentHeaderRow}", 'LAMPIRAN DOKUMEN');

                $sheet->getStyle("A{$attachmentHeaderRow}:F{$attachmentHeaderRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '166534'],
                    ],
                ]);

                $sheet->getStyle("A" . ($attachmentHeaderRow + 1) . ":C" . ($attachmentHeaderRow + 1))->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '475569'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                if ($attachmentCount > 0) {
                    $sheet->getStyle("A{$attachmentStartRow}:C{$attachmentEndRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'E2E8F0'],
                            ],
                        ],
                    ]);

                    $sheet->getStyle("C{$attachmentStartRow}:C{$attachmentEndRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    $sheet->getStyle("C{$attachmentStartRow}:C{$attachmentEndRow}")
                        ->getNumberFormat()->setFormatCode('#,##0.0');
                }

                /*
                |--------------------------------------------------------------------------
                | General font + wrap
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle("A1:F{$lastRow}")->getFont()->setName('Calibri')->setSize(11);
                $sheet->getStyle("A1:F{$lastRow}")->getAlignment()->setWrapText(true);

                /*
                |--------------------------------------------------------------------------
                | Print area
                |--------------------------------------------------------------------------
                */
                $sheet->getPageSetup()->setPrintArea("A1:F{$lastRow}");
            },
        ];
    }
}