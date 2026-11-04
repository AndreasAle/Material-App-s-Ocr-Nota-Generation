<?php

namespace App\Services;

use App\Models\PurchaseTransactionItem;
use App\Models\RabPlan;
use App\Models\RabPlanItem;

class BudgetCheckService
{
    /**
     * Cek ketersediaan budget RAB untuk proyek + estimasi nilai.
     *
     * @return array{status: string, available: float, planned: float, utilized: float, pct: float, rab_name: ?string}
     */
    public function check(int $projectId, float $estimatedAmount, ?string $period = null): array
    {
        $period = $period ?? now()->format('Y-m');

        // Cari RAB aktif
        $rab = RabPlan::where('project_id', $projectId)
            ->where('status', 'active')
            ->where('period_start', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('period_end')
                  ->orWhere('period_end', '>=', now()->toDateString());
            })
            ->first();

        if (! $rab) {
            return [
                'status'    => 'no_rab',
                'available' => 0,
                'planned'   => 0,
                'utilized'  => 0,
                'pct'       => 0,
                'rab_name'  => null,
            ];
        }

        $totalBudget = (float) $rab->total_budget;

        $utilized = (float) PurchaseTransactionItem::query()
            ->whereHas('purchaseTransaction', function ($q) use ($rab) {
                $q->where('project_id', $rab->project_id)
                  ->whereDate('transaction_date', '>=', $rab->period_start)
                  ->when($rab->period_end, fn ($q) => $q->whereDate('transaction_date', '<=', $rab->period_end));
            })
            ->sum('subtotal');

        $available = $totalBudget - $utilized;
        $pct       = $totalBudget > 0 ? ($utilized / $totalBudget) * 100 : 0;

        $status = match (true) {
            ($available - $estimatedAmount) < 0 => 'over_budget',
            default                              => 'within_budget',
        };

        return [
            'status'     => $status,
            'available'  => $available,
            'planned'    => $totalBudget,
            'utilized'   => $utilized,
            'pct'        => $pct,
            'rab_name'   => $rab->name,
            'rab_id'     => $rab->id,
        ];
    }

    /**
     * Cek apakah boleh submit (tidak melebihi budget tanpa override).
     */
    public function canSubmit(int $projectId, float $amount): bool
    {
        $result = $this->check($projectId, $amount);
        return in_array($result['status'], ['within_budget', 'no_rab']);
    }
}
