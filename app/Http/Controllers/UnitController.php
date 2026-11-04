<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->toString();

        $units = Unit::query()
            ->when($q, fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('master.units.index', compact('units', 'q'));
    }

    public function create(): View
    {
        return view('master.units.create');
    }

    public function store(UnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return redirect()->route('units.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function show(Unit $unit): View
    {
        return view('master.units.show', compact('unit'));
    }

    public function edit(Unit $unit): View
    {
        return view('master.units.edit', compact('unit'));
    }

    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()->route('units.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->materials()->exists()) {
            return back()->with('error', 'Satuan tidak bisa dihapus karena sudah dipakai material.');
        }

        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Satuan berhasil dihapus.');
    }
}