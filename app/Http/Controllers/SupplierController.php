<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->toString();

        $suppliers = Supplier::query()
            ->when($q, fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('city', 'like', "%{$q}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('master.suppliers.index', compact('suppliers', 'q'));
    }

    public function create(): View
    {
        return view('master.suppliers.create');
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        Supplier::create([
            ...$request->validated(),
            'is_active' => (bool) $request->boolean('is_active', true),
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->loadCount('purchaseTransactions');

        return view('master.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('master.suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update([
            ...$request->validated(),
            'is_active' => (bool) $request->boolean('is_active', false),
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchaseTransactions()->exists()) {
            return back()->with('error', 'Supplier tidak bisa dihapus karena sudah memiliki transaksi.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }

    public function quickStoreFromScan(Request $request): JsonResponse
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
    ]);

    $supplierName = trim($validated['name']);

    $normalizedIncoming = strtoupper(preg_replace('/[^A-Z0-9]+/', '', $supplierName));

    $existing = Supplier::query()
        ->where('is_active', true)
        ->get()
        ->first(function ($supplier) use ($normalizedIncoming) {
            $normalizedExisting = strtoupper(preg_replace('/[^A-Z0-9]+/', '', $supplier->name));
            return $normalizedExisting === $normalizedIncoming;
        });

    if ($existing) {
        return response()->json([
            'success' => true,
            'message' => 'Supplier sudah tersedia.',
            'supplier' => [
                'id' => $existing->id,
                'name' => $existing->name,
            ],
            'created' => false,
        ]);
    }

    $supplier = Supplier::create([
        'name' => $supplierName,
        'slug' => Str::slug($supplierName),
        'phone' => null,
        'email' => null,
        'address' => null,
        'city' => null,
        'is_active' => true,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Supplier berhasil ditambahkan.',
        'supplier' => [
            'id' => $supplier->id,
            'name' => $supplier->name,
        ],
        'created' => true,
    ]);
}
}