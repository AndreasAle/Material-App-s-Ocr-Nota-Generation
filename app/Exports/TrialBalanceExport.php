<?php

namespace App\Exports;

use App\Services\Accounting\AccountBalanceService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TrialBalanceExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        protected string $dateFrom,
        protected string $dateTo,
    ) {}

    public function collection()
    {
        $service = app(AccountBalanceService::class);
        $rows    = $service->getTrialBalance($this->dateFrom, $this->dateTo);

        return $rows->map(fn ($row) => [
            $row->account->code,
            $row->account->name,
            $row->account->getTypeLabel(),
            (float) $row->total_debit,
            (float) $row->total_credit,
            (float) $row->balance,
        ]);
    }

    public function headings(): array
    {
        return ['Kode', 'Nama Akun', 'Tipe', 'Total Debit (Rp)', 'Total Kredit (Rp)', 'Saldo (Rp)'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Neraca Saldo';
    }
}
