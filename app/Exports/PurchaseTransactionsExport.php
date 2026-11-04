<?php

namespace App\Exports;

use App\Exports\Sheets\PurchaseTransactionsDetailSheet;
use App\Exports\Sheets\PurchaseTransactionsSummarySheet;
use App\Exports\Sheets\PurchaseTransactionsTransactionSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PurchaseTransactionsExport implements WithMultipleSheets
{
    public function __construct(
        protected ?string $status = null,
        protected ?string $startDate = null,
        protected ?string $endDate = null,
    ) {
    }

    public function sheets(): array
    {
        return [
            new PurchaseTransactionsSummarySheet(
                status: $this->status,
                startDate: $this->startDate,
                endDate: $this->endDate,
            ),
            new PurchaseTransactionsTransactionSheet(
                status: $this->status,
                startDate: $this->startDate,
                endDate: $this->endDate,
            ),
            new PurchaseTransactionsDetailSheet(
                status: $this->status,
                startDate: $this->startDate,
                endDate: $this->endDate,
            ),
        ];
    }
}