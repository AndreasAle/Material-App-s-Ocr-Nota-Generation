<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialCategoryRequest;
use App\Models\MaterialCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MaterialCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->toString();

        $categories = MaterialCategory::query()
            ->when($q, fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('master.categories.index', compact('categories', 'q'));
    }

    public function create(): View
    {
        return view('master.categories.create');
    }

    public function store(MaterialCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['is_active'] = (bool) $request->boolean('is_active', true);

        MaterialCategory::create($data);

        return redirect()->route('material-categories.index')->with('success', 'Kategori material berhasil ditambahkan.');
    }

    public function show(MaterialCategory $materialCategory): View
    {
        return view('master.categories.show', compact('materialCategory'));
    }

    public function edit(MaterialCategory $materialCategory): View
    {
        return view('master.categories.edit', compact('materialCategory'));
    }

    public function update(MaterialCategoryRequest $request, MaterialCategory $materialCategory): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['is_active'] = (bool) $request->boolean('is_active', false);

        $materialCategory->update($data);

        return redirect()->route('material-categories.index')->with('success', 'Kategori material berhasil diperbarui.');
    }

    public function destroy(MaterialCategory $materialCategory): RedirectResponse
    {
        if ($materialCategory->materials()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena sudah dipakai material.');
        }

        $materialCategory->delete();

        return redirect()->route('material-categories.index')->with('success', 'Kategori material berhasil dihapus.');
    }
}