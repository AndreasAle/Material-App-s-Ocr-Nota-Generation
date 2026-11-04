<?php

namespace App\Http\Controllers;

use App\Services\Reports\ReceivableReportService;
use App\Exports\ReceivableExport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReceivableController extends Controller
{
    public function __construct(
        protected ReceivableReportService $service,
    ) {}

    public function index(Request $request): View
    {
        $projectId = $request->get('project_id');

        $aging   = $this->service->getAging($projectId);
        $summary = $this->service->getSummary();
        $list    = $this->service->getOutstandingList($projectId);

        $projects = \App\Models\Project::where('is_active', true)->orderBy('name')->get();

        return view('receivables.index', compact('aging', 'summary', 'list', 'projects', 'projectId'));
    }

    public function exportExcel(Request $request)
    {
        $projectId = $request->get('project_id');
        $filename  = 'piutang-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new ReceivableExport($projectId), $filename);
    }
}
