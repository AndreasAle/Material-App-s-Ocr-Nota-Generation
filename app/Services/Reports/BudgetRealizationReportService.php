<?php

namespace App\Services\Reports;

use App\Models\RabPlan;
use App\Models\RabPlanItem;
use App\Models\PurchaseTransaction;
use Illuminate\Support\Facades\DB;

class BudgetRealizationReportService
{
    /**
     * Build budget vs realization report for a RAB Plan.
     */
    public function build(RabPlan $rabPlan): array
    {
        $items = RabPlanItem::where('rab_plan_id', $rabPlan->id)
            ->with('material.unit')
            ->orderBy('material_name_snapshot')
            ->get();

        $periodStart = $rabPlan->period_start;
        $periodEnd   = $rabPlan->period_end ?? now()->toDateString();

        // Aggregate actual purchases for the same project + period
        $actuals = DB::table('purchase_transaction_items as pti')
            ->join('purchase_transactions as pt', 'pt.id', '=', 'pti.purchase_transaction_id')
            ->where('pt.project_id', $rabPlan->project_id)
            ->where('pt.status', '!=', 'rejected')
            ->whereBetween('pt.transaction_date', [$periodStart, $periodEnd])
            ->selectRaw('
                pti.material_id,
                SUM(pti.qty) AS total_qty,
                SUM(pti.qty * pti.price) AS total_amount
            ')
            ->groupBy('pti.material_id')
            ->get()
            ->keyBy('material_id');

        $rows       = [];
        $totalBudget = 0;
        $totalActual = 0;

        foreach ($items as $item) {
            $actual    = $actuals->get($item->material_id);
            $actualQty = $actual ? (float) $actual->total_qty   : 0;
            $actualAmt = $actual ? (float) $actual->total_amount : 0;

            $budgetAmt  = (float) $item->subtotal_planned;
            $variance   = $budgetAmt - $actualAmt;
            $utilPct    = $budgetAmt > 0 ? min(999, ($actualAmt / $budgetAmt) * 100) : 0;

            $rows[] = [
                'item'              => $item,
                'budget_qty'        => (float) $item->qty_planned,
                'budget_price'      => (float) $item->price_planned,
                'budget_amount'     => $budgetAmt,
                'actual_qty'        => $actualQty,
                'actual_amount'     => $actualAmt,
                'variance'          => $variance,
                'utilization_pct'   => round($utilPct, 1),
                'status'            => $this->utilizationStatus($utilPct),
            ];

            $totalBudget += $budgetAmt;
            $totalActual += $actualAmt;
        }

        $overallUtilPct = $totalBudget > 0
            ? min(999, ($totalActual / $totalBudget) * 100)
            : 0;

        return [
            'rab_plan'          => $rabPlan,
            'rows'              => $rows,
            'total_budget'      => $totalBudget,
            'total_actual'      => $totalActual,
            'total_variance'    => $totalBudget - $totalActual,
            'utilization_pct'   => round($overallUtilPct, 1),
            'status'            => $this->utilizationStatus($overallUtilPct),
        ];
    }

    private function utilizationStatus(float $pct): string
    {
        return match(true) {
            $pct > 90 => 'danger',
            $pct > 70 => 'warning',
            default   => 'safe',
        };
    }
}
