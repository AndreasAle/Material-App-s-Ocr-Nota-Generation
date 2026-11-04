<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierPriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupplierPriceHistoryController extends Controller
{
    public function show(Request $request, Supplier $supplier): View
    {
        // Ringkasan per material: harga terbaru, min, max, jumlah catatan
        $summary = SupplierPriceHistory::query()
            ->where('supplier_id', $supplier->id)
            ->select([
                'material_id',
                'material_name_snapshot',
                'unit_name_snapshot',
                DB::raw('MAX(price) as price_max'),
                DB::raw('MIN(price) as price_min'),
                DB::raw('COUNT(*) as record_count'),
                DB::raw('MAX(recorded_at) as last_recorded_at'),
            ])
            ->groupBy('material_id', 'material_name_snapshot', 'unit_name_snapshot')
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where('material_name_snapshot', 'like', '%' . $request->q . '%');
            })
            ->orderBy('material_name_snapshot')
            ->paginate(15)
            ->withQueryString();

        // Untuk setiap material dalam halaman ini, ambil harga terbaru
        $latestPrices = SupplierPriceHistory::query()
            ->where('supplier_id', $supplier->id)
            ->whereIn('material_name_snapshot', $summary->pluck('material_name_snapshot'))
            ->select('material_name_snapshot', 'price', 'recorded_at')
            ->orderByDesc('recorded_at')
            ->get()
            ->groupBy('material_name_snapshot')
            ->map(fn ($group) => $group->first());

        // Tren harga 6 bulan terakhir untuk setiap material (untuk sparkline)
        $trends = SupplierPriceHistory::query()
            ->where('supplier_id', $supplier->id)
            ->whereIn('material_name_snapshot', $summary->pluck('material_name_snapshot'))
            ->whereDate('recorded_at', '>=', now()->subMonths(5)->startOfMonth())
            ->select('material_name_snapshot', 'price', 'recorded_at')
            ->orderBy('recorded_at')
            ->get()
            ->groupBy('material_name_snapshot');

        return view('suppliers.price-history', compact('supplier', 'summary', 'latestPrices', 'trends'));
    }
}
