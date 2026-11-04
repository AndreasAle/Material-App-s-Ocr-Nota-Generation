<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Project;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseReportController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $baseQuery = PurchaseTransaction::query()
            ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $endDate));

        $summary = [
            'total_transactions' => (clone $baseQuery)->count(),
            'total_grand_total' => (float) (clone $baseQuery)->sum('grand_total'),
            'draft_count' => (clone $baseQuery)->where('status', 'draft')->count(),
            'submitted_count' => (clone $baseQuery)->where('status', 'submitted')->count(),
            'posted_count' => (clone $baseQuery)->where('status', 'posted')->count(),
            'supplier_count' => Supplier::where('is_active', true)->count(),
            'project_count' => Project::where('is_active', true)->count(),
            'material_count' => Material::where('is_active', true)->count(),
        ];

        $topSuppliers = PurchaseTransaction::query()
            ->select('supplier_id', DB::raw('COUNT(*) as total_transactions'), DB::raw('SUM(grand_total) as total_amount'))
            ->with('supplier')
            ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->whereNotNull('supplier_id')
            ->groupBy('supplier_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        $topProjects = PurchaseTransaction::query()
            ->select('project_id', DB::raw('COUNT(*) as total_transactions'), DB::raw('SUM(grand_total) as total_amount'))
            ->with('project')
            ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->whereNotNull('project_id')
            ->groupBy('project_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        $topMaterials = DB::table('purchase_transaction_items')
            ->join('purchase_transactions', 'purchase_transactions.id', '=', 'purchase_transaction_items.purchase_transaction_id')
            ->select(
                'purchase_transaction_items.material_name_snapshot',
                'purchase_transaction_items.unit_name_snapshot',
                DB::raw('SUM(purchase_transaction_items.qty) as total_qty'),
                DB::raw('SUM(purchase_transaction_items.subtotal) as total_amount'),
                DB::raw('COUNT(*) as total_rows')
            )
            ->when($startDate, fn ($q) => $q->whereDate('purchase_transactions.transaction_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('purchase_transactions.transaction_date', '<=', $endDate))
            ->groupBy(
                'purchase_transaction_items.material_name_snapshot',
                'purchase_transaction_items.unit_name_snapshot'
            )
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        $attachmentInsights = [
            'with_attachment' => PurchaseTransaction::query()
                ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
                ->where('attachment_count', '>', 0)
                ->count(),

            'without_attachment' => PurchaseTransaction::query()
                ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
                ->where(function ($q) {
                    $q->whereNull('attachment_count')->orWhere('attachment_count', 0);
                })
                ->count(),
        ];

        return view('purchases.reports.index', compact(
            'summary',
            'topSuppliers',
            'topProjects',
            'topMaterials',
            'attachmentInsights',
            'startDate',
            'endDate',
        ));
    }
}