<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProjectComparisonController extends Controller
{
    // -------------------------------------------------------------------------
    // index — form pilih proyek
    // -------------------------------------------------------------------------
    public function index(): View
    {
        $projects = Project::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);

        return view('reports.project-comparison.index', compact('projects'));
    }

    // -------------------------------------------------------------------------
    // compare — tampilkan tabel perbandingan
    // -------------------------------------------------------------------------
    public function compare(Request $request): View|RedirectResponse
    {
        $projectIds = array_filter((array) $request->input('project_ids', []));

        if (count($projectIds) < 2) {
            return redirect()->route('project-comparison.index')
                ->with('error', 'Pilih minimal 2 proyek untuk dibandingkan.');
        }

        $projectIds = array_slice(array_values($projectIds), 0, 4); // max 4

        $projects = Project::whereIn('id', $projectIds)->orderBy('name')->get();

        $data = [];
        foreach ($projects as $project) {
            // RAB total budget
            $rabBudget = DB::table('rab_plan_items')
                ->join('rab_plans', 'rab_plan_items.rab_plan_id', '=', 'rab_plans.id')
                ->where('rab_plans.project_id', $project->id)
                ->sum('rab_plan_items.subtotal_planned');

            // Total pengeluaran (semua transaksi)
            $totalSpending = DB::table('purchase_transactions')
                ->where('project_id', $project->id)
                ->sum('grand_total');

            // Total revenue (invoice paid)
            $totalRevenue = DB::table('invoices')
                ->where('project_id', $project->id)
                ->sum('paid_amount');

            // Total tagihan invoice (grand_total)
            $totalBilled = DB::table('invoices')
                ->where('project_id', $project->id)
                ->sum('grand_total');

            // Jumlah transaksi
            $txCount = DB::table('purchase_transactions')
                ->where('project_id', $project->id)
                ->count();

            // Jumlah material unik
            $materialCount = DB::table('purchase_transaction_items')
                ->join('purchase_transactions', 'purchase_transaction_items.purchase_transaction_id', '=', 'purchase_transactions.id')
                ->where('purchase_transactions.project_id', $project->id)
                ->whereNotNull('purchase_transaction_items.material_id')
                ->distinct()
                ->count('purchase_transaction_items.material_id');

            // Jumlah RAB
            $rabCount = DB::table('rab_plans')
                ->where('project_id', $project->id)
                ->count();

            // Jumlah Purchase Order
            $poCount = DB::table('purchase_orders')
                ->where('project_id', $project->id)
                ->count();

            $budgetUtil = (float) $rabBudget > 0 ? min(((float) $totalSpending / (float) $rabBudget) * 100, 999) : 0;
            $profit     = (float) $totalRevenue - (float) $totalSpending;
            $margin     = (float) $totalRevenue > 0 ? ($profit / (float) $totalRevenue) * 100 : 0;

            $data[] = [
                'project'        => $project,
                'rab_budget'     => (float) $rabBudget,
                'spending'       => (float) $totalSpending,
                'revenue'        => (float) $totalRevenue,
                'billed'         => (float) $totalBilled,
                'profit'         => $profit,
                'margin'         => round($margin, 1),
                'budget_util'    => round($budgetUtil, 1),
                'tx_count'       => $txCount,
                'material_count' => $materialCount,
                'rab_count'      => $rabCount,
                'po_count'       => $poCount,
            ];
        }

        return view('reports.project-comparison.result', compact('data', 'projects'));
    }
}
