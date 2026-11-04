<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialRequest;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->toString();

        $materials = Material::query()
            ->with(['category', 'unit'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('material_category_id', $request->category_id))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->status === '1'))
            // Filter dari Beranda: barang tanpa harga jual / harga jual di bawah harga beli
            ->when($request->get('harga') === 'kosong', fn ($query) => $query->where('is_active', true)
                ->where(fn ($w) => $w->whereNull('sell_price')->orWhere('sell_price', '<=', 0)))
            ->when($request->get('harga') === 'rugi', fn ($query) => $query->where('sell_price', '>', 0)
                ->whereNotNull('last_purchase_price')->whereColumn('last_purchase_price', '>=', 'sell_price'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = \App\Models\MaterialCategory::orderBy('name')->get(['id', 'name']);
        $stockMap = app(\App\Services\Stock\StockMovementService::class)->currentStockAll();
        $counts = [
            'kosong' => Material::where('is_active', true)->where(fn ($w) => $w->whereNull('sell_price')->orWhere('sell_price', '<=', 0))->count(),
            'rugi'   => Material::where('sell_price', '>', 0)->whereNotNull('last_purchase_price')->whereColumn('last_purchase_price', '>=', 'sell_price')->count(),
        ];

        return view('master.materials.index', compact('materials', 'q', 'categories', 'stockMap', 'counts'));
    }

    public function create(): View
    {
        $categories = MaterialCategory::query()->where('is_active', true)->orderBy('name')->get();
        $units = Unit::query()->orderBy('name')->get();

        return view('master.materials.create', compact('categories', 'units'));
    }

    public function store(MaterialRequest $request): RedirectResponse
    {
        $material = Material::create([
            ...collect($request->validated())->except('units')->all(),
            'default_price' => $request->input('default_price', 0),
            'sell_price' => $request->input('sell_price', 0) ?: 0,
            'is_active' => (bool) $request->boolean('is_active', true),
        ]);
        $this->syncUnits($material, $request->input('units', []));

        return redirect()->route('materials.index')->with('success', 'Material berhasil ditambahkan.');
    }

    public function show(Material $material): View
    {
        $material->load(['category', 'unit']);

        return view('master.materials.show', compact('material'));
    }

    public function edit(Material $material): View
    {
        $categories = MaterialCategory::query()->where('is_active', true)->orderBy('name')->get();
        $units = Unit::query()->orderBy('name')->get();

        return view('master.materials.edit', compact('material', 'categories', 'units'));
    }

    public function update(MaterialRequest $request, Material $material): RedirectResponse
    {
        $material->update([
            ...collect($request->validated())->except('units')->all(),
            'default_price' => $request->input('default_price', 0),
            'sell_price' => $request->input('sell_price', 0) ?: 0,
            'is_active' => (bool) $request->boolean('is_active', false),
        ]);
        $this->syncUnits($material, $request->input('units', []));

        return redirect()->route('materials.index')->with('success', 'Material berhasil diperbarui.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        if ($material->purchaseItems()->exists()) {
            return back()->with('error', 'Material tidak bisa dihapus karena sudah dipakai transaksi.');
        }

        $material->delete();

        return redirect()->route('materials.index')->with('success', 'Material berhasil dihapus.');
    }

    /**
     * Simpan satuan tambahan (konversi ke satuan dasar). Baris kosong diabaikan.
     */
    private function syncUnits(Material $material, array $units): void
    {
        $rows = collect($units)
            ->filter(fn ($u) => trim((string) ($u['unit_name'] ?? '')) !== '' && (float) ($u['factor'] ?? 0) > 0)
            ->reject(fn ($u) => mb_strtolower(trim($u['unit_name'])) === mb_strtolower((string) $material->unit?->name));

        $material->units()->delete();
        foreach ($rows as $u) {
            $material->units()->create([
                'unit_name'  => trim($u['unit_name']),
                'factor'     => (float) $u['factor'],
                'sell_price' => ($u['sell_price'] ?? '') !== '' ? (float) $u['sell_price'] : null,
            ]);
        }
    }
}
