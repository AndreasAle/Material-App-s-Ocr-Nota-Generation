<?php

namespace App\Http\Controllers;

use App\Services\Reports\TaxReportService;
use App\Exports\TaxReportExport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class TaxReportController extends Controller
{
    public function __construct(
        protected TaxReportService $service,
    ) {}

    public function index(Request $request): View
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $dateFrom    = sprintf('%04d-%02d-01', $year, $month);
        $dateTo      = date('Y-m-t', strtotime($dateFrom));

        $report       = $this->service->build($dateFrom, $dateTo);
        $monthlySummary = $this->service->buildMonthlySummary($year);

        return view('reports.tax', compact('report', 'monthlySummary', 'year', 'month', 'dateFrom', 'dateTo'));
    }

    public function exportExcel(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $dateFrom = sprintf('%04d-%02d-01', $year, $month);
        $dateTo   = date('Y-m-t', strtotime($dateFrom));

        $filename = "rekap-pajak-{$year}-{$month}.xlsx";

        return Excel::download(new TaxReportExport($dateFrom, $dateTo), $filename);
    }
}
