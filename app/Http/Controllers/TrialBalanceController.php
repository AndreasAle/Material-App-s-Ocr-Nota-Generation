<?php

namespace App\Http\Controllers;

use App\Services\Accounting\AccountBalanceService;
use App\Exports\TrialBalanceExport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class TrialBalanceController extends Controller
{
    public function __construct(
        protected AccountBalanceService $service,
    ) {}

    public function index(Request $request): View
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $dateFrom = sprintf('%04d-%02d-01', $year, $month);
        $dateTo   = date('Y-m-t', strtotime($dateFrom));

        $rows = $this->service->getTrialBalance($dateFrom, $dateTo);

        $totalDebit  = $rows->sum('total_debit');
        $totalCredit = $rows->sum('total_credit');
        $isBalanced  = abs($totalDebit - $totalCredit) < 0.01;

        return view('accounting.trial-balance', compact(
            'rows', 'totalDebit', 'totalCredit', 'isBalanced', 'year', 'month', 'dateFrom', 'dateTo'
        ));
    }

    public function exportExcel(Request $request)
    {
        $year     = (int) $request->get('year', now()->year);
        $month    = (int) $request->get('month', now()->month);
        $dateFrom = sprintf('%04d-%02d-01', $year, $month);
        $dateTo   = date('Y-m-t', strtotime($dateFrom));
        $filename = "neraca-saldo-{$year}-{$month}.xlsx";

        return Excel::download(new TrialBalanceExport($dateFrom, $dateTo), $filename);
    }
}
