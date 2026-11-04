<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\Stock\StockMovementService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function __construct(
        protected StockMovementService $stockService,
    ) {}

    public function index(): View
    {
        $warehouses = Warehouse::withCount('stockMovements')
            ->orderBy('name')
            ->get();

        $stats = [
            'total'     => $warehouses->count(),
            'active'    => $warehouses->where('is_active', true)->count(),
            'inactive'  => $warehouses->where('is_active', false)->count(),
            'movements' => StockMovement::count(),
        ];

        return view('warehouses.index', compact('warehouses', 'stats'));
    }

    public function create(): View
    {
        return view('warehouses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'nullable|string|max:50|unique:warehouses,code',
            'location'     => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'description'  => 'nullable|string|max:1000',
            'is_active'    => 'boolean',
        ]);

        $warehouse = Warehouse::create($validated + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('warehouses.show', $warehouse)
            ->with('success', "Gudang {$warehouse->name} berhasil ditambahkan.");
    }

    public function show(Warehouse $warehouse): View
    {
        $movements = StockMovement::where('warehouse_id', $warehouse->id)
            ->with(['material', 'creator'])
            ->latest('movement_date')
            ->paginate(20);

        $materialStocks = StockMovement::where('warehouse_id', $warehouse->id)
            ->selectRaw('
                material_id,
                material_name_snapshot,
                unit_name_snapshot,
                SUM(CASE WHEN movement_type IN (\'in\',\'adjustment\') THEN qty ELSE 0 END) -
                SUM(CASE WHEN movement_type = \'out\' THEN qty ELSE 0 END) AS current_stock,
                MAX(movement_date) AS last_movement
            ')
            ->groupBy('material_id', 'material_name_snapshot', 'unit_name_snapshot')
            ->orderBy('material_name_snapshot')
            ->get();

        $summary = [
            'total_in'  => (float) StockMovement::where('warehouse_id', $warehouse->id)->where('movement_type', 'in')->sum('qty'),
            'total_out' => (float) StockMovement::where('warehouse_id', $warehouse->id)->where('movement_type', 'out')->sum('qty'),
            'materials' => $materialStocks->count(),
            'low_stock' => $materialStocks->where('current_stock', '<', 10)->count(),
        ];

        $allMaterials = Material::where('is_active', true)->orderBy('name')->get();

        return view('warehouses.show', compact('warehouse', 'movements', 'materialStocks', 'summary', 'allMaterials'));
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => "nullable|string|max:50|unique:warehouses,code,{$warehouse->id}",
            'location'     => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'description'  => 'nullable|string|max:1000',
        ]);

        $warehouse->update($validated + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('warehouses.show', $warehouse)
            ->with('success', "Gudang {$warehouse->name} berhasil diperbarui.");
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->stockMovements()->exists()) {
            return back()->with('error', 'Gudang ini memiliki riwayat pergerakan stok dan tidak dapat dihapus.');
        }
        $name = $warehouse->name;
        $warehouse->delete();
        return redirect()->route('warehouses.index')
            ->with('success', "Gudang {$name} berhasil dihapus.");
    }

    /**
     * Halaman QR Label — multi material, cetak batch per lembar A4.
     */
    public function qrLabel(Warehouse $warehouse, Request $request): View
    {
        $selectedIds = $request->get('material_ids', []);
        if (is_string($selectedIds) && $selectedIds !== '') {
            $selectedIds = array_filter(array_map('intval', explode(',', $selectedIds)));
        }
        $selectedIds = array_filter(array_map('intval', (array) $selectedIds));

        $allMaterials      = Material::where('is_active', true)->orderBy('name')->get();
        $selectedMaterials = $selectedIds
            ? Material::with('unit')->whereIn('id', $selectedIds)->get()
            : collect();

        // Generate QR data-URL untuk tiap material terpilih
        $qrImages = [];
        foreach ($selectedMaterials as $mat) {
            $qrImages[$mat->id] = $this->generateQrDataUrl($warehouse, $mat);
        }

        return view('warehouses.qr-label', compact(
            'warehouse', 'allMaterials', 'selectedMaterials', 'qrImages', 'selectedIds'
        ));
    }

    /**
     * Endpoint API: return QR sebagai PNG image (untuk <img src="...">).
     */
    public function qrImage(Warehouse $warehouse, Request $request): Response
    {
        $material = Material::findOrFail($request->get('material_id'));
        $png      = $this->generateQrPng($warehouse, $material);

        return response($png, 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function generateQrDataUrl(Warehouse $warehouse, Material $material): string
    {
        return 'data:image/png;base64,' . base64_encode($this->generateQrPng($warehouse, $material));
    }

    private function generateQrPng(Warehouse $warehouse, Material $material): string
    {
        // QR berisi URL langsung ke halaman gudang dengan info material
        $qrContent = url('/warehouses/' . $warehouse->id)
            . '?scan=1'
            . '&mid=' . $material->id
            . '&mname=' . urlencode($material->name)
            . '&unit=' . urlencode($material->unit?->name ?? '')
            . '&wcode=' . urlencode($warehouse->code ?? ('GDG-' . $warehouse->id));

        // v6.x: gunakan outputInterface (FQCN) & outputBase64=false agar return raw PNG binary
        $options = new QROptions;
        $options->outputInterface = \chillerlan\QRCode\Output\QRGdImagePNG::class;
        $options->eccLevel        = 'M';
        $options->scale           = 10;
        $options->addQuietzone    = true;
        $options->quietzoneSize   = 2;
        $options->outputBase64    = false;  // return raw binary, bukan base64 string

        return (new QRCode($options))->render($qrContent);
    }
}
