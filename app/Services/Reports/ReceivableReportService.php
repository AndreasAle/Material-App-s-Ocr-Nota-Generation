<?php

namespace App\Services\Reports;

use App\Models\Invoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReceivableReportService
{
    /**
     * Build aging receivables report.
     * Buckets: current (not due), 1-30, 31-60, 61-90, >90 days
     */
    public function getAging(?int $projectId = null): array
    {
        $today = now()->toDateString();

        $query = Invoice::with('project')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->selectRaw('
                id, invoice_no, project_id, client_name, invoice_date,
                due_date, grand_total, paid_amount,
                (grand_total - paid_amount) AS outstanding
            ');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $invoices = $query->orderBy('due_date')->get();

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

        foreach ($invoices as $inv) {
            $outstanding = (float) $inv->outstanding;
            $dueDate     = $inv->due_date ? Carbon::parse($inv->due_date) : null;

            if (! $dueDate || $dueDate->isFuture()) {
                $bucket = 'current';
            } else {
                $days = $dueDate->diffInDays($today);
                $bucket = match(true) {
                    $days <= 30  => '1_30',
                    $days <= 60  => '31_60',
                    $days <= 90  => '61_90',
                    default      => 'over_90',
                };
            }

            $buckets[$bucket][] = $inv;
            $totals[$bucket]   += $outstanding;
            $totals['grand_total'] += $outstanding;
        }

        return compact('buckets', 'totals');
    }

    /**
     * Summary stats for receivables.
     */
    public function getSummary(): array
    {
        $unpaidTotal    = Invoice::where('payment_status', 'unpaid')->sum(Invoice::raw('grand_total - paid_amount'));
        $partialTotal   = Invoice::where('payment_status', 'partial')->sum(Invoice::raw('grand_total - paid_amount'));
        $overdueCount   = Invoice::whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->count();
        $overdueAmount  = Invoice::whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->sum(Invoice::raw('grand_total - paid_amount'));

        return [
            'total_outstanding' => (float) ($unpaidTotal + $partialTotal),
            'unpaid_total'      => (float) $unpaidTotal,
            'partial_total'     => (float) $partialTotal,
            'overdue_count'     => (int) $overdueCount,
            'overdue_amount'    => (float) $overdueAmount,
        ];
    }

    /**
     * List of all outstanding invoices with aging info.
     */
    public function getOutstandingList(?int $projectId = null): Collection
    {
        $query = Invoice::with('project')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->orderBy('due_date');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->get()->map(function ($inv) {
            $outstanding = (float) $inv->grand_total - (float) $inv->paid_amount;
            $dueDate     = $inv->due_date;
            $daysOverdue = $dueDate && $dueDate->isPast()
                ? $dueDate->diffInDays(now())
                : 0;

            $inv->outstanding   = $outstanding;
            $inv->days_overdue  = $daysOverdue;
            $inv->is_overdue    = $daysOverdue > 0;

            return $inv;
        });
    }
}
