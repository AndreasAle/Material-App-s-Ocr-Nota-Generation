<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Material;
use App\Services\Stock\StockMovementService;
use App\Services\Store\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        protected SaleService $sales,
        protected StockMovementService $stock,
    ) {}

    public function index(): View
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'phone', 'credit_limit']);

        return view('store.pos.index', compact('customers'));
    }

    /**
     * Cari barang untuk kasir: nama, kode, atau barcode. Mengembalikan stok & harga per satuan.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));
        $stockMap = $this->stock->currentStockAll();

        $materials = Material::with(['unit', 'units'])
            ->where('is_active', true)
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('code', 'like', "%{$q}%")
                ->orWhere('barcode', $q)))
            ->orderBy('name')
            ->limit(30)
            ->get();

        return response()->json($materials->map(fn (Material $m) => [
            'id'        => $m->id,
            'code'      => $m->code,
            'barcode'   => $m->barcode,
            'name'      => $m->name,
            'stock'     => round($stockMap[$m->id] ?? 0, 2),
            'base_unit' => $m->baseUnitName(),
            'wholesale_price'   => $m->wholesale_price ? (float) $m->wholesale_price : null,
            'wholesale_min_qty' => $m->wholesale_min_qty ? (float) $m->wholesale_min_qty : null,
            'units'     => collect([['name' => $m->baseUnitName(), 'factor' => 1, 'price' => (float) $m->sell_price]])
                ->merge($m->units->map(fn ($u) => ['name' => $u->unit_name, 'factor' => (float) $u->factor, 'price' => $m->sellPriceFor($u->unit_name)]))
                ->values(),
        ]));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'customer_id'          => ['nullable', 'exists:customers,id'],
            'payment_method'       => ['required', 'in:cash,transfer,bon'],
            'discount_amount'      => ['nullable', 'numeric', 'min:0'],
            'cash_received'        => ['nullable', 'numeric', 'min:0'],
            'down_payment'         => ['nullable', 'numeric', 'min:0'],
            'notes'                => ['nullable', 'string', 'max:500'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.material_id'  => ['required', 'exists:materials,id'],
            'items.*.unit_name'    => ['nullable', 'string', 'max:50'],
            'items.*.qty'          => ['required', 'numeric', 'gt:0'],
            'items.*.price'        => ['nullable', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Keranjang masih kosong.',
        ]);

        $sale = $this->sales->store($validated, (int) auth()->id());

        if ($request->expectsJson()) {
            return response()->json([
                'sale_no'     => $sale->sale_no,
                'receipt_url' => route('store.sales.receipt', $sale),
                'show_url'    => route('store.sales.show', $sale),
            ]);
        }

        return redirect()->route('store.sales.receipt', $sale)->with('success', "Penjualan {$sale->sale_no} tersimpan.");
    }
}
