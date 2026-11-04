<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Project;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\Stock\StockMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(
        protected StockMovementService $stockService,
    ) {}

    // -------------------------------------------------------------------------
    // index — Ringkasan stok semua material
    // -------------------------------------------------------------------------
    public function index(Request $request): View
    {
        $search    = $request->input('search');
        $categoryId = $request->input('category_id');
        $alert      = $request->input('alert'); // '1' = hanya yang di bawah minimum

        // Ambil semua stok sekaligus (satu query)
        $stockMap = $this->stockService->currentStockAll();

        $materials = Material::query()
            ->with(['unit', 'category'])
            ->when($search, fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")
                   ->orWhere('code', 'like', "%{$search}%");
            }))
            ->when($categoryId, fn ($q) => $q->where('material_category_id', $categoryId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($m) use ($stockMap) {
                $balance = $stockMap[$m->id] ?? 0.0;
                $m->current_stock = $balance;
                $m->is_low_stock  = $m->minimum_stock !== null && $balance <= (float) $m->minimum_stock;
                return $m;
            });

        if ($alert === '1') {
            $materials = $materials->filter(fn ($m) => $m->is_low_stock);
        }

        // Stats
        $totalMaterials = $materials->count();
        $lowStockCount  = $materials->filter(fn ($m) => $m->is_low_stock)->count();
        $zeroStockCount = $materials->filter(fn ($m) => $m->current_stock <= 0)->count();
        $totalMovements = StockMovement::whereDate('movement_date', today())->count();

        // Kategoris untuk filter
        $categories = \App\Models\MaterialCategory::orderBy('name')->get(['id', 'name']);

        // Paginasi manual
        $page     = $request->input('page', 1);
        $perPage  = 25;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $materials->forPage($page, $perPage)->values(),
            $materials->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('stock.index', compact(
            'paginated',
            'totalMaterials', 'lowStockCount', 'zeroStockCount', 'totalMovements',
            'categories',
            'search', 'categoryId', 'alert',
        ));
    }

    // -------------------------------------------------------------------------
    // show — Riwayat gerakan stok per material
    // -------------------------------------------------------------------------
    public function show(Material $material, Request $request): View
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $movements = StockMovement::where('material_id', $material->id)
            ->when($startDate, fn ($q) => $q->whereDate('movement_date', '>=', $startDate))
            ->when($endDate,   fn ($q) => $q->whereDate('movement_date', '<=', $endDate))
            ->with(['project:id,name', 'creator:id,name'])
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $currentStock = $this->stockService->currentStock($material->id);

        // Ringkasan
        $totalIn  = StockMovement::where('material_id', $material->id)
            ->whereIn('movement_type', ['in', 'adjustment'])
            ->sum('qty');
        $totalOut = StockMovement::where('material_id', $material->id)
            ->where('movement_type', 'out')
            ->sum('qty');

        // Chart gerakan 6 bulan terakhir (IN vs OUT)
        $monthlyIn  = StockMovement::where('material_id', $material->id)
            ->whereIn('movement_type', ['in', 'adjustment'])
            ->where('movement_date', '>=', now()->subMonths(5)->startOfMonth())
            ->select(DB::raw('YEAR(movement_date) as yr'), DB::raw('MONTH(movement_date) as mo'), DB::raw('SUM(qty) as total'))
            ->groupBy('yr', 'mo')->orderBy('yr')->orderBy('mo')->get();

        $monthlyOut = StockMovement::where('material_id', $material->id)
            ->where('movement_type', 'out')
            ->where('movement_date', '>=', now()->subMonths(5)->startOfMonth())
            ->select(DB::raw('YEAR(movement_date) as yr'), DB::raw('MONTH(movement_date) as mo'), DB::raw('SUM(qty) as total'))
            ->groupBy('yr', 'mo')->orderBy('yr')->orderBy('mo')->get();

        $chartLabels = [];
        $chartIn     = [];
        $chartOut    = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $chartLabels[] = $d->translatedFormat('M Y');
            $fi = $monthlyIn->first(fn ($r) => $r->yr == $d->year && $r->mo == $d->month);
            $fo = $monthlyOut->first(fn ($r) => $r->yr == $d->year && $r->mo == $d->month);
            $chartIn[]  = $fi ? (float) $fi->total : 0;
            $chartOut[] = $fo ? (float) $fo->total : 0;
        }

        return view('stock.show', compact(
            'material', 'movements', 'currentStock',
            'totalIn', 'totalOut',
            'chartLabels', 'chartIn', 'chartOut',
            'startDate', 'endDate',
        ));
    }

    // -------------------------------------------------------------------------
    // create — form tambah gerakan manual
    // -------------------------------------------------------------------------
    public function create(Request $request): View
    {
        $materialId = $request->input('material_id');
        $material   = $materialId ? Material::with('unit')->find($materialId) : null;

        $materials  = Material::with('unit')->where('is_active', true)->orderBy('name')->get();
        $projects   = Project::orderBy('name')->get(['id', 'name', 'code']);
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);

        return view('stock.create', compact('materials', 'projects', 'material', 'warehouses'));
    }

    // -------------------------------------------------------------------------
    // store — simpan gerakan manual
    // -------------------------------------------------------------------------
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'material_id'   => ['required', 'exists:materials,id'],
            'movement_type' => ['required', 'in:in,out,adjustment'],
            'qty'           => ['required', 'numeric', 'min:0.01'],
            'unit_price'    => ['nullable', 'numeric', 'min:0'],
            'project_id'    => ['nullable', 'exists:projects,id'],
            'warehouse'     => ['nullable', 'string', 'max:100'],
            'warehouse_id'  => ['nullable', 'exists:warehouses,id'],
            'notes'         => ['nullable', 'string', 'max:500'],
            'movement_date' => ['required', 'date'],
        ], [
            'material_id.required'   => 'Material wajib dipilih.',
            'material_id.exists'     => 'Material tidak ditemukan.',
            'movement_type.required' => 'Jenis gerakan wajib dipilih.',
            'movement_type.in'       => 'Jenis gerakan tidak valid.',
            'qty.required'           => 'Jumlah wajib diisi.',
            'qty.min'                => 'Jumlah harus lebih dari 0.',
            'movement_date.required' => 'Tanggal wajib diisi.',
        ]);

        // Cegah stok negatif untuk gerakan keluar
        if ($validated['movement_type'] === 'out') {
            $current = $this->stockService->currentStock((int) $validated['material_id']);
            if ($current < (float) $validated['qty']) {
                return back()->withInput()->with('error',
                    "Stok tidak mencukupi. Stok saat ini: {$current} — dicoba ambil: {$validated['qty']}."
                );
            }
        }

        $movement = $this->stockService->storeManual($validated, auth()->id());

        return redirect()->route('stock.show', $movement->material_id)
            ->with('success', 'Gerakan stok berhasil dicatat.');
    }

    // -------------------------------------------------------------------------
    // scanQr — halaman scanner kamera QR untuk stock movement
    // -------------------------------------------------------------------------
    public function scanQr(): \Illuminate\View\View
    {
        return view('stock.scan-qr');
    }

    // -------------------------------------------------------------------------
    // setMinimum — update minimum_stock dari halaman show
    // -------------------------------------------------------------------------
    public function setMinimum(Request $request, Material $material): RedirectResponse
    {
        $request->validate([
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
        ], [
            'minimum_stock.min' => 'Stok minimum tidak boleh negatif.',
        ]);

        $material->update(['minimum_stock' => $request->input('minimum_stock') ?: null]);

        return back()->with('success', "Stok minimum untuk {$material->name} berhasil diperbarui.");
    }
}
