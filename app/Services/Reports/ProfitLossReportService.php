<?php

namespace App\Services\Reports;

use App\Models\Invoice;
use App\Models\PurchaseTransaction;
use App\Models\SubcontractPayment;
use Illuminate\Support\Facades\DB;

class ProfitLossReportService
{
    /**
     * Build Profit & Loss for a given period (cash basis).
     */
    public function build(string $dateFrom, string $dateTo, ?int $projectId = null): array
    {
        // ── Revenue ──────────────────────────────────────────────────────────
        $revenueQuery = Invoice::whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->whereIn('payment_status', ['paid', 'partial']);

        if ($projectId) {
            $revenueQuery->where('project_id', $projectId);
        }

        $revenue = (float) $revenueQuery->sum('paid_amount');

        // ── COGS: Material Purchase ──────────────────────────────────────────
        $purchaseQuery = PurchaseTransaction::whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->notRejected();

        if ($projectId) {
            $purchaseQuery->where('project_id', $projectId);
        }

        $materialCost = (float) $purchaseQuery->sum('grand_total');

        // ── COGS: Subcontract Cost ───────────────────────────────────────────
        $subcontractQuery = SubcontractPayment::join('subcontracts', 'subcontracts.id', '=', 'subcontract_payments.subcontract_id')
            ->whereBetween('subcontract_payments.payment_date', [$dateFrom, $dateTo]);

        if ($projectId) {
            $subcontractQuery->where('subcontracts.project_id', $projectId);
        }

        $subcontractCost = (float) $subcontractQuery->sum('subcontract_payments.amount');

        // ── Summary ──────────────────────────────────────────────────────────
        $totalCogs    = $materialCost + $subcontractCost;
        $grossProfit  = $revenue - $totalCogs;
        $grossMargin  = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;

        return [
            'date_from'        => $dateFrom,
            'date_to'          => $dateTo,
            'project_id'       => $projectId,
            'revenue'          => $revenue,
            'material_cost'    => $materialCost,
            'subcontract_cost' => $subcontractCost,
            'total_cogs'       => $totalCogs,
            'gross_profit'     => $grossProfit,
            'gross_margin_pct' => round($grossMargin, 2),
        ];
    }

    /**
     * Build P&L by project for a period.
     */
    public function byProject(string $dateFrom, string $dateTo): array
    {
        $projects = DB::table('projects')
            ->where('is_active', true)
            ->pluck('name', 'id');

        $result = [];
        foreach ($projects as $id => $name) {
            $data           = $this->build($dateFrom, $dateTo, $id);
            $data['project_name'] = $name;
            $result[]       = $data;
        }

        usort($result, fn ($a, $b) => $b['revenue'] <=> $a['revenue']);

        return $result;
    }

    /**
     * Build monthly P&L for a full year.
     */
    public function monthly(int $year): array
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $from    = sprintf('%04d-%02d-01', $year, $m);
            $to      = date('Y-m-t', strtotime($from));
            $months[$m] = $this->build($from, $to);
            $months[$m]['month'] = $m;
        }
        return $months;
    }
}
