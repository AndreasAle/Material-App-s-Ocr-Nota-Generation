<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\Project;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionItem;
use App\Models\RabPlan;
use App\Models\RabPlanItem;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    // -------------------------------------------------------------------------
    // index — Ringkasan Keuangan
    // -------------------------------------------------------------------------
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $projectId = $request->input('project_id');

        // Total pengeluaran keseluruhan (semua kecuali yang ditolak)
        $totalPengeluaran = PurchaseTransaction::query()
            ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate,   fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->notRejected()
            ->sum('grand_total');

        // Total pengeluaran semua status
        $totalPengeluaranAll = PurchaseTransaction::query()
            ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate,   fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->sum('grand_total');

        // Total anggaran RAB (active plans)
        $totalBudgetRab = RabPlan::query()
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->where('status', 'active')
            ->sum('total_budget');

        // Utilization rate
        $utilizationRate = $totalBudgetRab > 0
            ? round(($totalPengeluaran / $totalBudgetRab) * 100, 1)
            : null;

        // Jumlah transaksi bulan ini
        $txThisMonth = PurchaseTransaction::query()
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->count();

        // Transaksi pending approval
        $txPendingApproval = PurchaseTransaction::query()
            ->where('approval_status', 'pending')
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->count();

        // Chart: pengeluaran per bulan (12 bulan terakhir)
        $monthlySpend = PurchaseTransaction::query()
            ->select(
                DB::raw('YEAR(transaction_date) as year'),
                DB::raw('MONTH(transaction_date) as month'),
                DB::raw('SUM(grand_total) as total')
            )
            ->where('transaction_date', '>=', now()->subMonths(11)->startOfMonth())
            ->notRejected()
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Build chart labels (12 bulan terakhir)
        $chartLabels = [];
        $chartData   = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $chartLabels[] = $d->translatedFormat('M Y');
            $key = $d->year . '-' . $d->month;
            $found = $monthlySpend->first(fn ($r) => $r->year == $d->year && $r->month == $d->month);
            $chartData[] = $found ? (float) $found->total : 0;
        }

        // Per-project spending (top 8)
        $projectSpend = PurchaseTransaction::query()
            ->select('project_id', DB::raw('SUM(grand_total) as total'), DB::raw('COUNT(*) as tx_count'))
            ->notRejected()
            ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate,   fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->with('project:id,name,code')
            ->groupBy('project_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // RAB utilization summary (active plans, max 6)
        $rabSummary = RabPlan::query()
            ->where('status', 'active')
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->with('project:id,name,code')
            ->get()
            ->map(function ($plan) {
                $realisasi = PurchaseTransaction::where('project_id', $plan->project_id)
                    ->notRejected()
                    ->whereBetween('transaction_date', [
                        $plan->period_start,
                        $plan->period_end ?? now(),
                    ])
                    ->sum('grand_total');

                $pct = $plan->total_budget > 0
                    ? round(($realisasi / $plan->total_budget) * 100, 1)
                    : 0;

                return [
                    'plan'       => $plan,
                    'realisasi'  => $realisasi,
                    'pct'        => $pct,
                    'sisa'       => max(0, $plan->total_budget - $realisasi),
                ];
            })
            ->sortByDesc('pct')
            ->take(6)
            ->values();

        $projects = Project::orderBy('name')->get(['id', 'name', 'code']);

        return view('reports.financial.index', compact(
            'totalPengeluaran',
            'totalPengeluaranAll',
            'totalBudgetRab',
            'utilizationRate',
            'txThisMonth',
            'txPendingApproval',
            'chartLabels',
            'chartData',
            'projectSpend',
            'rabSummary',
            'projects',
            'startDate',
            'endDate',
            'projectId',
        ));
    }

    // -------------------------------------------------------------------------
    // rabRealisasi — RAB vs Realisasi
    // -------------------------------------------------------------------------
    public function rabRealisasi(Request $request): View
    {
        $projectId = $request->input('project_id');
        $status    = $request->input('status', 'active');

        $plans = RabPlan::query()
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->with(['project:id,name,code', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Untuk setiap plan, hitung realisasi aktual per item
        $plansData = $plans->map(function ($plan) {
            // Realisasi transaksi pembelian per material dalam periode plan
            $realisasiPerMaterial = PurchaseTransactionItem::query()
                ->select('material_id', DB::raw('SUM(qty) as qty_actual'), DB::raw('SUM(subtotal) as total_actual'))
                ->whereHas('purchaseTransaction', function ($q) use ($plan) {
                    $q->where('project_id', $plan->project_id)
                      ->notRejected()
                      ->whereDate('transaction_date', '>=', $plan->period_start)
                      ->when($plan->period_end, fn ($q2) => $q2->whereDate('transaction_date', '<=', $plan->period_end));
                })
                ->whereIn('material_id', $plan->items->pluck('material_id')->filter())
                ->groupBy('material_id')
                ->get()
                ->keyBy('material_id');

            $itemsData = $plan->items->map(function ($item) use ($realisasiPerMaterial) {
                $actual = $realisasiPerMaterial->get($item->material_id);
                $qtyActual   = $actual ? (float) $actual->qty_actual   : 0;
                $totalActual = $actual ? (float) $actual->total_actual  : 0;
                $qtyPct   = $item->qty_planned > 0
                    ? round(($qtyActual / $item->qty_planned) * 100, 1)
                    : 0;
                $budgetPct = $item->subtotal_planned > 0
                    ? round(($totalActual / $item->subtotal_planned) * 100, 1)
                    : 0;

                return [
                    'item'         => $item,
                    'qty_actual'   => $qtyActual,
                    'total_actual' => $totalActual,
                    'sisa_qty'     => max(0, $item->qty_planned - $qtyActual),
                    'sisa_budget'  => max(0, $item->subtotal_planned - $totalActual),
                    'qty_pct'      => $qtyPct,
                    'budget_pct'   => $budgetPct,
                ];
            });

            $totalBudget     = $plan->items->sum('subtotal_planned');
            $totalRealisasi  = $itemsData->sum('total_actual');
            $totalPct        = $totalBudget > 0
                ? round(($totalRealisasi / $totalBudget) * 100, 1)
                : 0;

            return [
                'plan'           => $plan,
                'items'          => $itemsData,
                'total_budget'   => $totalBudget,
                'total_realisasi'=> $totalRealisasi,
                'total_sisa'     => max(0, $totalBudget - $totalRealisasi),
                'total_pct'      => $totalPct,
            ];
        });

        $projects = Project::orderBy('name')->get(['id', 'name', 'code']);

        return view('reports.financial.rab-realisasi', compact(
            'plansData',
            'projects',
            'projectId',
            'status',
        ));
    }

    // -------------------------------------------------------------------------
    // pengeluaran — Laporan Pengeluaran
    // -------------------------------------------------------------------------
    public function pengeluaran(Request $request): View
    {
        $startDate      = $request->input('start_date');
        $endDate        = $request->input('end_date');
        $projectId      = $request->input('project_id');
        $supplierId     = $request->input('supplier_id');
        $approvalStatus = $request->input('approval_status');

        $query = PurchaseTransaction::query()
            ->with(['project:id,name,code', 'supplier:id,name', 'user:id,name'])
            ->when($startDate,      fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate,        fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->when($projectId,      fn ($q) => $q->where('project_id', $projectId))
            ->when($supplierId,     fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($approvalStatus, fn ($q) => $q->where('approval_status', $approvalStatus))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        $transactions = $query->paginate(25)->withQueryString();

        // Summary totals (non-paginated)
        $summaryQuery = PurchaseTransaction::query()
            ->when($startDate,      fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate,        fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->when($projectId,      fn ($q) => $q->where('project_id', $projectId))
            ->when($supplierId,     fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($approvalStatus, fn ($q) => $q->where('approval_status', $approvalStatus));

        $totalAmount  = (clone $summaryQuery)->sum('grand_total');
        $totalCount   = (clone $summaryQuery)->count();
        $totalApproved = (clone $summaryQuery)->where('approval_status', 'approved')->sum('grand_total');

        // Per-project summary
        $perProject = (clone $summaryQuery)
            ->select('project_id', DB::raw('SUM(grand_total) as total'), DB::raw('COUNT(*) as cnt'))
            ->with('project:id,name,code')
            ->groupBy('project_id')
            ->orderByDesc('total')
            ->get();

        $projects  = Project::orderBy('name')->get(['id', 'name', 'code']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return view('reports.financial.pengeluaran', compact(
            'transactions',
            'totalAmount',
            'totalCount',
            'totalApproved',
            'perProject',
            'projects',
            'suppliers',
            'startDate',
            'endDate',
            'projectId',
            'supplierId',
            'approvalStatus',
        ));
    }

    // -------------------------------------------------------------------------
    // suratJalan — Rekap Surat Jalan
    // -------------------------------------------------------------------------
    public function suratJalan(Request $request): View
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $projectId = $request->input('project_id');
        $status    = $request->input('status');

        $receipts = GoodsReceipt::query()
            ->with([
                'purchaseTransaction.project:id,name,code',
                'purchaseTransaction.supplier:id,name',
                'items',
                'creator:id,name',
            ])
            ->when($startDate, fn ($q) => $q->whereDate('receipt_date', '>=', $startDate))
            ->when($endDate,   fn ($q) => $q->whereDate('receipt_date', '<=', $endDate))
            ->when($status,    fn ($q) => $q->where('status', $status))
            ->when($projectId, fn ($q) => $q->whereHas('purchaseTransaction', function ($q2) use ($projectId) {
                $q2->where('project_id', $projectId);
            }))
            ->orderByDesc('receipt_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        // Stats summary (non-paginated)
        $statsBase = GoodsReceipt::query()
            ->when($startDate, fn ($q) => $q->whereDate('receipt_date', '>=', $startDate))
            ->when($endDate,   fn ($q) => $q->whereDate('receipt_date', '<=', $endDate))
            ->when($status,    fn ($q) => $q->where('status', $status))
            ->when($projectId, fn ($q) => $q->whereHas('purchaseTransaction', function ($q2) use ($projectId) {
                $q2->where('project_id', $projectId);
            }));

        $stats = [
            'total'    => (clone $statsBase)->count(),
            'pending'  => (clone $statsBase)->where('status', 'pending')->count(),
            'partial'  => (clone $statsBase)->where('status', 'partial')->count(),
            'complete' => (clone $statsBase)->where('status', 'complete')->count(),
        ];

        $projects = Project::orderBy('name')->get(['id', 'name', 'code']);

        return view('reports.financial.surat-jalan', compact(
            'receipts',
            'stats',
            'projects',
            'startDate',
            'endDate',
            'projectId',
            'status',
        ));
    }

    // -------------------------------------------------------------------------
    // exportPengeluaranPdf — Export Laporan Pengeluaran ke PDF
    // -------------------------------------------------------------------------
    public function exportPengeluaranPdf(Request $request)
    {
        $startDate      = $request->input('start_date');
        $endDate        = $request->input('end_date');
        $projectId      = $request->input('project_id');
        $supplierId     = $request->input('supplier_id');
        $approvalStatus = $request->input('approval_status');

        $transactions = PurchaseTransaction::query()
            ->with(['project:id,name,code', 'supplier:id,name', 'user:id,name'])
            ->when($startDate,      fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate,        fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->when($projectId,      fn ($q) => $q->where('project_id', $projectId))
            ->when($supplierId,     fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($approvalStatus, fn ($q) => $q->where('approval_status', $approvalStatus))
            ->orderByDesc('transaction_date')
            ->get();

        $totalAmount   = $transactions->sum('grand_total');
        $totalApproved = $transactions->where('approval_status', 'approved')->sum('grand_total');

        $projectName  = $projectId  ? Project::find($projectId)?->name  : null;
        $supplierName = $supplierId ? Supplier::find($supplierId)?->name : null;

        $pdf = Pdf::loadView('reports.financial.exports.pengeluaran-pdf', compact(
            'transactions',
            'totalAmount',
            'totalApproved',
            'startDate',
            'endDate',
            'projectName',
            'supplierName',
            'approvalStatus',
        ))->setPaper('a4', 'landscape');

        $filename = 'laporan-pengeluaran-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}
