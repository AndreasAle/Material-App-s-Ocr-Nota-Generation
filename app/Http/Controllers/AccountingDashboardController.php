<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Services\Accounting\AccountBalanceService;
use App\Services\Reports\CashFlowReportService;
use App\Services\Reports\ProfitLossReportService;
use App\Services\Reports\ReceivableReportService;
use App\Services\Reports\PayableReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingDashboardController extends Controller
{
    public function __construct(
        protected CashFlowReportService   $cashFlow,
        protected ProfitLossReportService $profitLoss,
        protected ReceivableReportService $receivable,
        protected PayableReportService    $payable,
        protected AccountBalanceService   $balance,
    ) {}

    public function index(Request $request): View
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $dateFrom = sprintf('%04d-%02d-01', $year, $month);
        $dateTo   = date('Y-m-t', strtotime($dateFrom));

        // Cash Flow this month
        $cashFlow = $this->cashFlow->buildMonthly($year, $month);

        // P&L this month
        $pl = $this->profitLoss->build($dateFrom, $dateTo);

        // Receivable summary
        $receivableSummary = $this->receivable->getSummary();

        // Payable summary
        $payableSummary = $this->payable->getSummary();

        // Recent journal entries
        $recentJournals = JournalEntry::with('creator')
            ->orderByDesc('entry_date')
            ->take(10)
            ->get();

        // 12-month trend
        $cashFlowTrend = $this->cashFlow->buildYearly($year);
        $plTrend       = $this->profitLoss->monthly($year);

        return view('accounting.dashboard', compact(
            'cashFlow', 'pl', 'receivableSummary', 'payableSummary',
            'recentJournals', 'cashFlowTrend', 'plTrend', 'year', 'month'
        ));
    }
}
