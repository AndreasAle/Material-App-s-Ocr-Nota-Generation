<?php

namespace App\Http\Controllers;

use App\Services\Reports\PayableReportService;
use App\Exports\PayableExport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class PayableController extends Controller
{
    public function __construct(
        protected PayableReportService $service,
    ) {}

    public function index(Request $request): View
    {
        $supplierId = $request->get('supplier_id');

        $aging   = $this->service->getAging($supplierId);
        $summary = $this->service->getSummary();
        $list    = $this->service->getOutstandingList($supplierId);

        $suppliers = \App\Models\Supplier::where('is_active', true)->orderBy('name')->get();

        return view('payables.index', compact('aging', 'summary', 'list', 'suppliers', 'supplierId'));
    }

    public function exportExcel(Request $request)
    {
        $supplierId = $request->get('supplier_id');
        $filename   = 'hutang-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new PayableExport($supplierId), $filename);
    }
}
