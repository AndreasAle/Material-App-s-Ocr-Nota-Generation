<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Services\Reports\InventoryValuationService;
use App\Exports\InventoryValuationExport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class InventoryValuationController extends Controller
{
    public function __construct(
        protected InventoryValuationService $service,
    ) {}

    public function index(Request $request): View
    {
        $warehouseId = $request->get('warehouse_id');
        $asOfDate    = $request->get('as_of', now()->toDateString());

        $items      = $this->service->calculate($warehouseId, $asOfDate);
        $summary    = $this->service->getSummary($warehouseId, $asOfDate);
        $lowStock   = $this->service->getLowStockItems();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('reports.inventory-valuation', compact(
            'items', 'summary', 'lowStock', 'warehouses', 'warehouseId', 'asOfDate'
        ));
    }

    public function exportExcel(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $asOfDate    = $request->get('as_of', now()->toDateString());
        $filename    = 'nilai-stok-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new InventoryValuationExport($warehouseId, $asOfDate), $filename);
    }
}
