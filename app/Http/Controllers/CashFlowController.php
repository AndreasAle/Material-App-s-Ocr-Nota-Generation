<?php

namespace App\Http\Controllers;

use App\Exports\CashFlowExport;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class CashFlowController extends Controller
{
    // -------------------------------------------------------------------------
    // index — Laporan Arus Kas 12 bulan
    // -------------------------------------------------------------------------
    public function index(Request $request): View
    {
        $projectId = $request->input('project_id');
        $startDate = now()->subMonths(11)->startOfMonth()->toDateString();

        // Build period labels
        $periods = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $periods[$d->format('Y-m')] = $d->translatedFormat('M Y');
        }

        // Cash OUT = total purchase transactions per bulan
        $cashOutRaw = DB::table('purchase_transactions')
            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as period, SUM(grand_total) as total')
            ->whereDate('transaction_date', '>=', $startDate)
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->groupBy('period')
            ->get()
            ->keyBy('period');

        // Cash IN = invoice paid_amount per bulan
        $cashInRaw = DB::table('invoices')
            ->selectRaw('DATE_FORMAT(invoice_date, "%Y-%m") as period, SUM(paid_amount) as total')
            ->whereDate('invoice_date', '>=', $startDate)
            ->where('payment_status', '!=', 'unpaid')
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->groupBy('period')
            ->get()
            ->keyBy('period');

        // Assemble table rows & chart data
        $chartLabels  = [];
        $chartCashIn  = [];
        $chartCashOut = [];
        $chartNet     = [];
        $tableRows    = [];
        $runningBal   = 0;

        foreach ($periods as $key => $label) {
            $in  = (float) ($cashInRaw[$key]->total  ?? 0);
            $out = (float) ($cashOutRaw[$key]->total ?? 0);
            $net = $in - $out;
            $runningBal += $net;

            $chartLabels[]  = $label;
            $chartCashIn[]  = $in;
            $chartCashOut[] = $out;
            $chartNet[]     = $net;

            $tableRows[] = [
                'period'          => $label,
                'cash_in'         => $in,
                'cash_out'        => $out,
                'net'             => $net,
                'running_balance' => $runningBal,
            ];
        }

        $totalCashIn  = array_sum($chartCashIn);
        $totalCashOut = array_sum($chartCashOut);
        $netFlow      = $totalCashIn - $totalCashOut;
        $projects     = Project::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $selectedProject = $projectId ? Project::find($projectId) : null;

        return view('reports.cash-flow.index', compact(
            'chartLabels', 'chartCashIn', 'chartCashOut', 'chartNet',
            'tableRows', 'totalCashIn', 'totalCashOut', 'netFlow',
            'projects', 'projectId', 'selectedProject',
        ));
    }

    // -------------------------------------------------------------------------
    // exportPdf — PDF Laporan Arus Kas
    // -------------------------------------------------------------------------
    public function exportPdf(Request $request)
    {
        $projectId = $request->input('project_id');
        $startDate = now()->subMonths(11)->startOfMonth()->toDateString();

        $periods = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $periods[$d->format('Y-m')] = $d->translatedFormat('M Y');
        }

        $cashOutRaw = DB::table('purchase_transactions')
            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as period, SUM(grand_total) as total')
            ->whereDate('transaction_date', '>=', $startDate)
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->groupBy('period')
            ->get()
            ->keyBy('period');

        $cashInRaw = DB::table('invoices')
            ->selectRaw('DATE_FORMAT(invoice_date, "%Y-%m") as period, SUM(paid_amount) as total')
            ->whereDate('invoice_date', '>=', $startDate)
            ->where('payment_status', '!=', 'unpaid')
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->groupBy('period')
            ->get()
            ->keyBy('period');

        $tableRows  = [];
        $runningBal = 0;
        foreach ($periods as $key => $label) {
            $in  = (float) ($cashInRaw[$key]->total  ?? 0);
            $out = (float) ($cashOutRaw[$key]->total ?? 0);
            $net = $in - $out;
            $runningBal += $net;
            $tableRows[] = compact('label', 'in', 'out', 'net', 'runningBal');
        }

        $totalIn  = array_sum(array_column($tableRows, 'in'));
        $totalOut = array_sum(array_column($tableRows, 'out'));
        $project  = $projectId ? Project::find($projectId) : null;

        $pdf = Pdf::loadView('reports.cash-flow.export-pdf', compact(
            'tableRows', 'totalIn', 'totalOut', 'project'
        ));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('laporan-arus-kas-' . now()->format('Ymd') . '.pdf');
    }

    // -------------------------------------------------------------------------
    // exportExcel — Excel Laporan Arus Kas
    // -------------------------------------------------------------------------
    public function exportExcel(Request $request)
    {
        $projectId = $request->input('project_id');
        $filename  = 'arus-kas-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new CashFlowExport($projectId), $filename);
    }
}
