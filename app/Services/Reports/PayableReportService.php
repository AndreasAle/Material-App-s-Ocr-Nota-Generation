<?php

namespace App\Services\Reports;

use App\Models\PurchaseTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PayableReportService
{
    /**
     * Build aging payables report.
     * Buckets: current, 1-30, 31-60, 61-90, >90 days overdue
     */
    public function getAging(?int $supplierId = null): array
    {
        $query = PurchaseTransaction::with('supplier')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->notRejected();

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $transactions = $query->orderBy('due_date')->get();

        $buckets = [
            'current' => [],
            '1_30'    => [],
            '31_60'   => [],
            '61_90'   => [],
            'over_90' => [],
        ];

        $totals = [
            'current' => 0, '1_30' => 0, '31_60' => 0,
            '61_90'   => 0, 'over_90' => 0, 'grand_total' => 0,
        ];

        foreach ($transactions as $tx) {
            $outstanding = (float) $tx->remaining_amount;
            $dueDate     = $tx->due_date ? Carbon::parse($tx->due_date) : null;

            if (! $dueDate || $dueDate->isFuture()) {
                $bucket = 'current';
            } else {
                $days = $dueDate->diffInDays(now());
                $bucket = match(true) {
                    $days <= 30  => '1_30',
                    $days <= 60  => '31_60',
                    $days <= 90  => '61_90',
                    default      => 'over_90',
                };
            }

            $tx->outstanding = $outstanding;
            $buckets[$bucket][] = $tx;
            $totals[$bucket]   += $outstanding;
            $totals['grand_total'] += $outstanding;
        }

        return compact('buckets', 'totals');
    }

    /**
     * Summary stats for payables.
     */
    public function getSummary(): array
    {
        $base = PurchaseTransaction::notRejected()
            ->whereIn('payment_status', ['unpaid', 'partial']);

        $total      = (float) $base->clone()->selectRaw('SUM(remaining_amount) as t')->value('t');
        $overdueAmt = (float) PurchaseTransaction::notRejected()
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->selectRaw('SUM(remaining_amount) as t')
            ->value('t');

        return [
            'total_outstanding' => $total,
            'overdue_amount'    => $overdueAmt,
        ];
    }

    /**
     * List outstanding payables with aging info.
     */
    public function getOutstandingList(?int $supplierId = null): Collection
    {
        $query = PurchaseTransaction::with('supplier')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->notRejected()
            ->orderBy('due_date');

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query->get()->map(function ($tx) {
            $outstanding    = (float) $tx->remaining_amount;
            $dueDate        = $tx->due_date;
            $daysOverdue    = $dueDate && $dueDate->isPast()
                ? $dueDate->diffInDays(now())
                : 0;

            $tx->outstanding  = $outstanding;
            $tx->days_overdue = $daysOverdue;
            $tx->is_overdue   = $daysOverdue > 0;

            return $tx;
        });
    }
}
