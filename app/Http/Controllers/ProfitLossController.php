<?php

namespace App\Http\Controllers;

use App\Exports\ProfitLossExport;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\PurchaseTransaction;
use App\Models\RabPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ProfitLossController extends Controller
{
    /**
     * Overview semua proyek: revenue (invoice) vs cost (transaksi pembelian).
     */
    public function index(Request $request): View
    {
        $year = (int) ($request->get('year', now()->year));

        $projects = Project::where('is_active', true)->orderBy('name')->get();

        // Revenue per project (from paid invoices)
        $revenueRaw = Invoice::whereYear('invoice_date', $year)
            ->whereIn('payment_status', ['partial', 'paid'])
            ->selectRaw('project_id, SUM(paid_amount) as total_revenue, COUNT(*) as invoice_count')
            ->groupBy('project_id')
            ->get()
            ->keyBy('project_id');

        // Cost per project (from approved/completed purchase transactions)
        $costRaw = PurchaseTransaction::whereYear('transaction_date', $year)
            ->notRejected()
            ->selectRaw('project_id, SUM(grand_total) as total_cost, COUNT(*) as tx_count')
            ->groupBy('project_id')
            ->get()
            ->keyBy('project_id');

        // Cost per project (all transactions regardless of approval)
        $costAll = PurchaseTransaction::whereYear('transaction_date', $year)
            ->selectRaw('project_id, SUM(grand_total) as total_cost, COUNT(*) as tx_count')
            ->groupBy('project_id')
            ->get()
            ->keyBy('project_id');

        // RAB budget per project
        $budgetRaw = RabPlan::whereYear('period_start', $year)
            ->where('status', '!=', 'draft')
            ->with('items')
            ->get()
            ->groupBy('project_id');

        $summaryRows = $projects->map(function (Project $p) use ($revenueRaw, $costAll, $budgetRaw) {
            $revenue = (float) ($revenueRaw[$p->id]?->total_revenue ?? 0);
            $cost    = (float) ($costAll[$p->id]?->total_cost ?? 0);
            $budget  = collect($budgetRaw[$p->id] ?? [])
                ->flatMap(fn ($rab) => $rab->items)
                ->sum('subtotal_planned');

            return [
                'project'       => $p,
                'revenue'       => $revenue,
                'cost'          => $cost,
                'gross_profit'  => $revenue - $cost,
                'margin_pct'    => $revenue > 0 ? (($revenue - $cost) / $revenue) * 100 : null,
                'budget'        => (float) $budget,
                'invoice_count' => (int) ($revenueRaw[$p->id]?->invoice_count ?? 0),
                'tx_count'      => (int) ($costAll[$p->id]?->tx_count ?? 0),
            ];
        })->filter(fn ($row) => $row['revenue'] > 0 || $row['cost'] > 0);

        $totalRevenue = $summaryRows->sum('revenue');
        $totalCost    = $summaryRows->sum('cost');
        $totalProfit  = $totalRevenue - $totalCost;
        $avgMargin    = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        // Monthly chart data (revenue vs cost)
        $monthlyRevenue = Invoice::whereYear('invoice_date', $year)
            ->whereIn('payment_status', ['partial', 'paid'])
            ->selectRaw('MONTH(invoice_date) as month, SUM(paid_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthlyCost = PurchaseTransaction::whereYear('transaction_date', $year)
            ->selectRaw('MONTH(transaction_date) as month, SUM(grand_total) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $chartMonths  = [];
        $chartRevenue = [];
        $chartCost    = [];
        $chartProfit  = [];
        for ($m = 1; $m <= 12; $m++) {
            $r = (float) ($monthlyRevenue[$m] ?? 0);
            $c = (float) ($monthlyCost[$m] ?? 0);
            $chartMonths[]  = \Carbon\Carbon::create($year, $m, 1)->locale('id')->monthName;
            $chartRevenue[] = $r;
            $chartCost[]    = $c;
            $chartProfit[]  = $r - $c;
        }

        $availableYears = collect();
        $minYear = (int) max(now()->year - 4, 2020);
        for ($y = $minYear; $y <= now()->year + 1; $y++) {
            $availableYears->push($y);
        }

        return view('reports.profit-loss.index', compact(
            'summaryRows', 'totalRevenue', 'totalCost', 'totalProfit', 'avgMargin',
            'chartMonths', 'chartRevenue', 'chartCost', 'chartProfit',
            'year', 'availableYears'
        ));
    }

    /**
     * Detail P&L untuk satu proyek: breakdown per bulan + invoice list + cost list.
     */
    public function show(Request $request, Project $project): View
    {
        $year = (int) ($request->get('year', now()->year));

        // Invoices for this project
        $invoices = Invoice::where('project_id', $project->id)
            ->whereYear('invoice_date', $year)
            ->with('creator')
            ->latest('invoice_date')
            ->get();

        // Purchase transactions for this project
        $transactions = PurchaseTransaction::where('project_id', $project->id)
            ->whereYear('transaction_date', $year)
            ->with(['supplier', 'user'])
            ->latest('transaction_date')
            ->get();

        // RAB plans for this project
        $rabPlans = RabPlan::where('project_id', $project->id)
            ->with('items')
            ->get();

        $totalRevenue = $invoices->sum('paid_amount');
        $totalBilled  = $invoices->sum('grand_total');
        $totalCost    = $transactions->sum('grand_total');
        $totalBudget  = $rabPlans->flatMap->items->sum('subtotal_planned');
        $grossProfit  = $totalRevenue - $totalCost;
        $marginPct    = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $budgetVsCost = $totalBudget > 0 ? ($totalCost / $totalBudget) * 100 : 0;

        // Monthly breakdown
        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthInvoices = $invoices->filter(fn ($i) => $i->invoice_date->month === $m);
            $monthTx       = $transactions->filter(fn ($t) => $t->transaction_date->month === $m);
            $rev           = $monthInvoices->sum('paid_amount');
            $cost          = $monthTx->sum('grand_total');
            $monthly[$m] = [
                'month'         => \Carbon\Carbon::create($year, $m, 1)->locale('id')->monthName,
                'revenue'       => (float) $rev,
                'cost'          => (float) $cost,
                'profit'        => (float) ($rev - $cost),
                'invoice_count' => $monthInvoices->count(),
                'tx_count'      => $monthTx->count(),
            ];
        }

        $availableYears = collect();
        $minYear = (int) max(now()->year - 4, 2020);
        for ($y = $minYear; $y <= now()->year + 1; $y++) {
            $availableYears->push($y);
        }

        return view('reports.profit-loss.show', compact(
            'project', 'invoices', 'transactions', 'rabPlans',
            'totalRevenue', 'totalBilled', 'totalCost', 'totalBudget',
            'grossProfit', 'marginPct', 'budgetVsCost',
            'monthly', 'year', 'availableYears'
        ));
    }

    /**
     * Export P&L proyek ke PDF.
     */
    public function exportPdf(Request $request, Project $project)
    {
        $year         = (int) ($request->get('year', now()->year));
        $invoices     = Invoice::where('project_id', $project->id)->whereYear('invoice_date', $year)->get();
        $transactions = PurchaseTransaction::where('project_id', $project->id)->whereYear('transaction_date', $year)->get();
        $rabPlans     = RabPlan::where('project_id', $project->id)->with('items')->get();

        $totalRevenue = $invoices->sum('paid_amount');
        $totalCost    = $transactions->sum('grand_total');
        $totalBudget  = $rabPlans->flatMap->items->sum('subtotal_planned');
        $grossProfit  = $totalRevenue - $totalCost;
        $marginPct    = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        $pdf = Pdf::loadView('reports.profit-loss.export-pdf', compact(
            'project', 'invoices', 'transactions',
            'totalRevenue', 'totalCost', 'totalBudget', 'grossProfit', 'marginPct', 'year'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("pl-{$project->id}-{$year}.pdf");
    }

    /**
     * Export P&L summary all projects to Excel.
     */
    public function exportExcel(Request $request)
    {
        $year     = (int) ($request->get('year', now()->year));
        $filename = "laba-rugi-{$year}.xlsx";

        return Excel::download(new ProfitLossExport($year), $filename);
    }
}
