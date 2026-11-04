<?php

namespace App\Exports;

use App\Services\Reports\TaxReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TaxReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        protected string $dateFrom,
        protected string $dateTo,
    ) {}

    public function collection()
    {
        $service = app(TaxReportService::class);
        $report  = $service->build($this->dateFrom, $this->dateTo);

        $rows = collect();

        // PPN Masukan
        $rows->push(['== PPN MASUKAN ==', '', '', '', '', '', '']);
        foreach ($report['ppn_masukan'] as $r) {
            $rows->push([
                'PPN Masukan',
                $r->tax_date ?? '—',
                $r->faktur_no ?? '—',
                $r->purchaseTransaction?->supplier?->name ?? '—',
                (float) ($r->dpp ?? 0),
                (float) ($r->ppn_amount ?? 0),
                $r->status ?? '—',
            ]);
        }
        $rows->push(['Sub Total PPN Masukan', '', '', '', '', $report['ppn_masukan_total'], '']);
        $rows->push(['', '', '', '', '', '', '']);

        // PPN Keluaran
        $rows->push(['== PPN KELUARAN ==', '', '', '', '', '', '']);
        foreach ($report['ppn_keluaran'] as $r) {
            $rows->push([
                'PPN Keluaran',
                $r->tax_date ?? '—',
                $r->faktur_no ?? '—',
                $r->invoice?->client_name ?? '—',
                (float) ($r->dpp ?? 0),
                (float) ($r->ppn_amount ?? 0),
                $r->status ?? '—',
            ]);
        }
        $rows->push(['Sub Total PPN Keluaran', '', '', '', '', $report['ppn_keluaran_total'], '']);
        $rows->push(['Net PPN (Keluaran - Masukan)', '', '', '', '', $report['net_ppn'], '']);

        return $rows;
    }

    public function headings(): array
    {
        return ['Jenis', 'Tanggal', 'No. Faktur', 'Pihak', 'DPP (Rp)', 'Pajak (Rp)', 'Status'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Rekap Pajak';
    }
}
