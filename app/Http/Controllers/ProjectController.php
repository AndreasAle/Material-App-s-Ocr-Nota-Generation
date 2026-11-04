<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->toString();

        $projects = Project::query()
            ->when($q, fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('master.projects.index', compact('projects', 'q'));
    }

    public function create(): View
    {
        return view('master.projects.create');
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        Project::create([
            ...$request->validated(),
            'is_active' => (bool) $request->boolean('is_active', true),
        ]);

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil ditambahkan.');
    }

    public function show(Project $project): View
    {
        $project->loadCount('purchaseTransactions');

        return view('master.projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        return view('master.projects.edit', compact('project'));
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update([
            ...$request->validated(),
            'is_active' => (bool) $request->boolean('is_active', false),
        ]);

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil diperbarui.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        if ($project->purchaseTransactions()->exists()) {
            return back()->with('error', 'Proyek tidak bisa dihapus karena sudah dipakai transaksi.');
        }

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dihapus.');
    }
}