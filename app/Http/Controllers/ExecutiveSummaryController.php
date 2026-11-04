<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Project;
use App\Models\PurchaseTransaction;
use App\Models\Invoice;
use App\Models\RabPlan;
use App\Models\PurchaseOrder;
use App\Models\MaterialRequest;
use App\Models\StockMovement;
use App\Models\Subcontract;

class ExecutiveSummaryController extends Controller
{
    public function buildData(int $year, int $month): array
    {
        $startOfMonth = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endOfMonth   = now()->setYear($year)->setMonth($month)->endOfMonth();
        $startOfYear  = now()->setYear($year)->startOfYear();

        // ─── Keuangan Bulan Ini ────────────────────────────────────────────
        $spendingMonth = PurchaseTransaction::whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('grand_total');
        $revenueMonth  = Invoice::whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->sum('paid_amount');
        $netMonth      = $revenueMonth - $spendingMonth;

        // ─── Keuangan YTD ─────────────────────────────────────────────────
        $spendingYtd = PurchaseTransaction::whereBetween('transaction_date', [$startOfYear, $endOfMonth])
            ->sum('grand_total');
        $revenueYtd  = Invoice::whereBetween('invoice_date', [$startOfYear, $endOfMonth])
            ->sum('paid_amount');

        // ─── RAB Utilization ──────────────────────────────────────────────
        $activeRabs = RabPlan::where('status', 'active')->with('project')->get();
        $rabSummary = $activeRabs->map(function ($rab) {
            $budget  = (float) $rab->items()->sum('subtotal_planned');
            $actual  = PurchaseTransaction::where('project_id', $rab->project_id)
                ->where('transaction_date', '>=', $rab->period_start)
                ->sum('grand_total');
            $pct = $budget > 0 ? round(($actual / $budget) * 100, 1) : 0;
            return ['name' => $rab->name, 'project' => $rab->project->name ?? '-', 'budget' => $budget, 'actual' => $actual, 'pct' => $pct];
        });

        // ─── Status PO ────────────────────────────────────────────────────
        $poStats = DB::table('purchase_orders')
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(grand_total) as total'))
            ->groupBy('status')->get()->keyBy('status');

        // ─── Stok Kritis ──────────────────────────────────────────────────
        $lowStock = DB::table('materials as m')
            ->leftJoin(DB::raw('(SELECT material_id, SUM(CASE WHEN movement_type IN (\'in\',\'adjustment\') THEN qty ELSE -qty END) AS balance FROM stock_movements WHERE material_id IS NOT NULL GROUP BY material_id) AS s'), 'm.id', '=', 's.material_id')
            ->where('m.is_active', true)->whereNotNull('m.minimum_stock')
            ->whereRaw('COALESCE(s.balance, 0) <= m.minimum_stock')
            ->select('m.id', 'm.name', 'm.minimum_stock', DB::raw('COALESCE(s.balance,0) as stock'))
            ->orderByRaw('COALESCE(s.balance,0) ASC')
            ->limit(5)->get();

        // ─── SPK Aktif ────────────────────────────────────────────────────
        $activeSpk = Subcontract::where('status', 'active')->with(['subcontractor', 'project'])
            ->orderBy('end_date')->limit(5)->get();

        // ─── Invoice Jatuh Tempo ──────────────────────────────────────────
        $overdueInvoices = Invoice::where('payment_status', '!=', 'paid')
            ->whereNotNull('due_date')->whereDate('due_date', '<', today())
            ->with('project')->orderBy('due_date')->limit(5)->get();

        // ─── Chart Data (12 bulan) ────────────────────────────────────────
        $chartMonths  = [];
        $chartSpend   = [];
        $chartRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->setYear($year)->setMonth($month)->subMonths($i);
            $chartMonths[]  = $date->format('M Y');
            $chartSpend[]   = (float) PurchaseTransaction::whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)->sum('grand_total');
            $chartRevenue[] = (float) Invoice::whereYear('invoice_date', $date->year)
                ->whereMonth('invoice_date', $date->month)->sum('paid_amount');
        }

        // ─── Top Supplier by Spend (bulan ini) ───────────────────────────
        $topSuppliers = DB::table('purchase_transactions as pt')
            ->join('suppliers as s', 's.id', '=', 'pt.supplier_id')
            ->whereBetween('pt.transaction_date', [$startOfMonth, $endOfMonth])
            ->select('s.name', DB::raw('SUM(pt.grand_total) as total'), DB::raw('COUNT(*) as tx_count'))
            ->groupBy('s.id', 's.name')->orderByDesc('total')->limit(5)->get();

        return compact(
            'year', 'month',
            'spendingMonth', 'revenueMonth', 'netMonth',
            'spendingYtd', 'revenueYtd',
            'rabSummary', 'poStats',
            'lowStock', 'activeSpk', 'overdueInvoices',
            'chartMonths', 'chartSpend', 'chartRevenue',
            'topSuppliers',
            'startOfMonth', 'endOfMonth'
        );
    }

    public function index(Request $request): View
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $data  = $this->buildData($year, $month);

        $years = range(now()->year - 2, now()->year + 1);
        return view('reports.executive-summary.index', array_merge($data, compact('years')));
    }

    public function exportPdf(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $data  = $this->buildData($year, $month);

        $pdf = Pdf::loadView('reports.executive-summary.pdf', $data)
            ->setPaper('a4', 'portrait');

        $monthName = now()->setYear($year)->setMonth($month)->translatedFormat('F_Y');
        return $pdf->download("Executive_Summary_{$monthName}.pdf");
    }
}
