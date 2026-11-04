<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MaterialRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        if ($user->isKasir()) {
            return redirect()->route('store.pos');
        }
        if (config('store.mode') && in_array($user->role, ['admin', 'pimpinan'], true)) {
            return redirect()->route('store.dashboard');
        }
        if (config('store.mode') && $user->role === 'staff') {
            return redirect()->route('store.pos');
        }

        $now = now();
        $today = today();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $baseQuery = PurchaseTransaction::query();

        $monthlyTotal = (clone $baseQuery)
            ->whereBetween('transaction_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->sum('grand_total');

        $monthlyCount = (clone $baseQuery)
            ->whereBetween('transaction_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->count();

        $todayCount = (clone $baseQuery)
            ->whereDate('transaction_date', $today->toDateString())
            ->count();

        $allTimeTotal = (clone $baseQuery)->sum('grand_total');
        $allTimeCount = (clone $baseQuery)->count();

        $supplierCount = Supplier::query()
            ->where('is_active', true)
            ->count();

        $latestTransactions = PurchaseTransaction::query()
            ->with(['supplier', 'project', 'user'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $monthlyChart = PurchaseTransaction::query()
            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as period, SUM(grand_total) as total')
            ->whereDate('transaction_date', '>=', $now->copy()->subMonths(5)->startOfMonth()->toDateString())
            ->whereDate('transaction_date', '<=', $now->copy()->endOfMonth()->toDateString())
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $topMaterials = DB::table('purchase_transaction_items')
            ->selectRaw('material_name_snapshot, SUM(qty) as total_qty')
            ->groupBy('material_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // ── Dashboard Pro: Alert KPIs ─────────────────────────────────────────
        // Low stock (materials with stock ≤ minimum)
        $lowStockCount = DB::table('materials as m')
            ->leftJoin(
                DB::raw('(SELECT material_id, SUM(CASE WHEN movement_type IN (\'in\',\'adjustment\') THEN qty ELSE -qty END) AS balance FROM stock_movements WHERE material_id IS NOT NULL GROUP BY material_id) AS s'),
                'm.id', '=', 's.material_id'
            )
            ->where('m.is_active', true)
            ->whereNotNull('m.minimum_stock')
            ->whereRaw('COALESCE(s.balance, 0) <= m.minimum_stock')
            ->count();

        $pendingApprovalCount = PurchaseTransaction::where('approval_status', 'pending')->count();
        $pendingPoCount       = PurchaseOrder::where('status', 'submitted')->count();
        $pendingMrCount       = MaterialRequest::where('status', 'submitted')->count();
        $overdueInvoiceCount  = Invoice::where('payment_status', '!=', 'paid')
                                    ->whereNotNull('due_date')
                                    ->whereDate('due_date', '<', today())
                                    ->count();

        // ── Dashboard Pro: Chart.js monthly — proper labels + values ─────────
        $chartMonths  = [];
        $chartSpend   = [];
        $chartInvoice = [];
        for ($i = 5; $i >= 0; $i--) {
            $d   = now()->subMonths($i);
            $key = $d->format('Y-m');
            $chartMonths[] = $d->translatedFormat('M Y');

            $chartSpend[] = (float) PurchaseTransaction::selectRaw('SUM(grand_total) as t')
                ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$key])
                ->value('t');

            $chartInvoice[] = (float) Invoice::selectRaw('SUM(paid_amount) as t')
                ->where('payment_status', '!=', 'unpaid')
                ->whereRaw('DATE_FORMAT(invoice_date, "%Y-%m") = ?', [$key])
                ->value('t');
        }

        // ── Dashboard Pro: Recent Activity Feed ───────────────────────────────
        $recentTx = PurchaseTransaction::select('id', DB::raw('"transaction" as activity_type'),
                        'transaction_no as label', 'grand_total as amount', 'status', 'created_at')
                    ->latest()->limit(4)->get();

        $recentPo = PurchaseOrder::select('id', DB::raw('"purchase_order" as activity_type'),
                        'po_no as label', 'grand_total as amount', 'status', 'created_at')
                    ->latest()->limit(3)->get();

        $recentMr = MaterialRequest::select('id', DB::raw('"material_request" as activity_type'),
                        'request_no as label', DB::raw('NULL as amount'), 'status', 'created_at')
                    ->latest()->limit(3)->get();

        $recentActivities = $recentTx->concat($recentPo)->concat($recentMr)
            ->sortByDesc('created_at')->take(8)->values();

        return view('dashboard.index', compact(
            'monthlyTotal',
            'monthlyCount',
            'todayCount',
            'allTimeTotal',
            'allTimeCount',
            'supplierCount',
            'latestTransactions',
            'monthlyChart',
            'topMaterials',
            // Pro
            'lowStockCount',
            'pendingApprovalCount',
            'pendingPoCount',
            'pendingMrCount',
            'overdueInvoiceCount',
            'chartMonths',
            'chartSpend',
            'chartInvoice',
            'recentActivities',
        ));
    }
}