<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRabPlanRequest;
use App\Http\Requests\UpdateRabPlanRequest;
use App\Models\Material;
use App\Models\Project;
use App\Models\PurchaseTransactionItem;
use App\Models\RabPlan;
use App\Services\Rab\RabPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RabPlanController extends Controller
{
    public function __construct(
        protected RabPlanService $rabPlanService,
    ) {
    }

    public function index(Request $request): View
    {
        $plans = RabPlan::query()
            ->with(['project', 'creator'])
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->project_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $projects = Project::query()->where('is_active', true)->orderBy('name')->get();

        return view('rab-plans.index', compact('plans', 'projects'));
    }

    public function create(): View
    {
        $projects  = Project::query()->where('is_active', true)->orderBy('name')->get();
        $materials = Material::query()->with(['unit', 'category'])->where('is_active', true)->orderBy('name')->get();

        return view('rab-plans.create', compact('projects', 'materials'));
    }

    public function store(StoreRabPlanRequest $request): RedirectResponse
    {
        $plan = $this->rabPlanService->store($request->validated(), (int) auth()->id());

        return redirect()
            ->route('rab-plans.show', $plan)
            ->with('success', 'RAB berhasil dibuat.');
    }

    public function show(RabPlan $rabPlan): View
    {
        $rabPlan->load(['project', 'creator', 'items.material.unit']);

        // Realisasi: ambil purchase_transaction_items yang sesuai proyek + periode
        $realizationQuery = PurchaseTransactionItem::query()
            ->select([
                'material_id',
                'material_name_snapshot',
                'unit_name_snapshot',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(subtotal) as total_subtotal'),
                DB::raw('AVG(price) as avg_price'),
            ])
            ->whereHas('purchaseTransaction', function ($q) use ($rabPlan) {
                $q->where('project_id', $rabPlan->project_id)
                  ->whereDate('transaction_date', '>=', $rabPlan->period_start)
                  ->when(
                      $rabPlan->period_end,
                      fn ($q) => $q->whereDate('transaction_date', '<=', $rabPlan->period_end)
                  );
            })
            ->groupBy('material_id', 'material_name_snapshot', 'unit_name_snapshot')
            ->get()
            ->keyBy('material_id');

        // Gabungkan plan items dengan realisasi
        $summaryItems = $rabPlan->items->map(function ($item) use ($realizationQuery) {
            $realization = $item->material_id
                ? ($realizationQuery->get($item->material_id) ?? null)
                : null;

            $realQty      = $realization ? (float) $realization->total_qty : 0;
            $realSubtotal = $realization ? (float) $realization->total_subtotal : 0;
            $budgetTotal  = (float) $item->subtotal_planned;
            $pct          = $budgetTotal > 0 ? min(($realSubtotal / $budgetTotal) * 100, 999) : 0;

            return [
                'item'          => $item,
                'real_qty'      => $realQty,
                'real_subtotal' => $realSubtotal,
                'sisa'          => $budgetTotal - $realSubtotal,
                'pct'           => round($pct, 1),
            ];
        });

        $grandBudget      = $rabPlan->total_budget;
        $grandRealization = $summaryItems->sum('real_subtotal');
        $grandSisa        = $grandBudget - $grandRealization;
        $grandPct         = $grandBudget > 0 ? min(round(($grandRealization / $grandBudget) * 100, 1), 999) : 0;

        return view('rab-plans.show', compact(
            'rabPlan',
            'summaryItems',
            'grandBudget',
            'grandRealization',
            'grandSisa',
            'grandPct',
        ));
    }

    public function edit(RabPlan $rabPlan): View
    {
        if ($rabPlan->isClosed()) {
            abort(403, 'RAB yang sudah ditutup tidak dapat diedit.');
        }

        $rabPlan->load(['project', 'items.material.unit']);
        $projects  = Project::query()->where('is_active', true)->orderBy('name')->get();
        $materials = Material::query()->with(['unit', 'category'])->where('is_active', true)->orderBy('name')->get();

        return view('rab-plans.edit', compact('rabPlan', 'projects', 'materials'));
    }

    public function update(UpdateRabPlanRequest $request, RabPlan $rabPlan): RedirectResponse
    {
        if ($rabPlan->isClosed()) {
            return back()->with('error', 'RAB yang sudah ditutup tidak dapat diedit.');
        }

        $this->rabPlanService->update($rabPlan, $request->validated());

        return redirect()
            ->route('rab-plans.show', $rabPlan)
            ->with('success', 'RAB berhasil diperbarui.');
    }

    public function destroy(RabPlan $rabPlan): RedirectResponse
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $this->rabPlanService->delete($rabPlan);

        return redirect()
            ->route('rab-plans.index')
            ->with('success', 'RAB berhasil dihapus.');
    }

    public function activate(RabPlan $rabPlan): RedirectResponse
    {
        if ($rabPlan->isClosed()) {
            return back()->with('error', 'RAB yang sudah ditutup tidak dapat diaktifkan kembali.');
        }

        $this->rabPlanService->activate($rabPlan);

        return back()->with('success', 'RAB berhasil diaktifkan.');
    }

    public function close(RabPlan $rabPlan): RedirectResponse
    {
        $this->rabPlanService->close($rabPlan);

        return back()->with('success', 'RAB berhasil ditutup.');
    }

    // -------------------------------------------------------------------------
    // templates — daftar template RAB
    // -------------------------------------------------------------------------
    public function templates(Request $request): View
    {
        $templates = RabPlan::where('is_template', true)
            ->with(['project', 'creator', 'items'])
            ->latest()->paginate(15)->withQueryString();

        return view('rab-plans.templates', compact('templates'));
    }

    // saveAsTemplate — simpan RAB aktif sebagai template
    public function saveAsTemplate(Request $request, RabPlan $rabPlan): RedirectResponse
    {
        $data = $request->validate([
            'template_name' => 'required|string|max:255',
        ]);

        $rabPlan->load('items');

        DB::transaction(function () use ($rabPlan, $data) {
            $template = RabPlan::create([
                'project_id'       => $rabPlan->project_id,
                'created_by'       => auth()->id(),
                'name'             => $rabPlan->name,
                'period_start'     => $rabPlan->period_start ?? today(),
                'description'      => $rabPlan->description,
                'status'           => 'draft',
                'total_budget'     => $rabPlan->total_budget,
                'is_template'      => true,
                'template_name'    => $data['template_name'],
                'is_latest_version'=> true,
                'version'          => 1,
            ]);

            foreach ($rabPlan->items as $item) {
                $template->items()->create([
                    'material_id'            => $item->material_id,
                    'material_name_snapshot' => $item->material_name_snapshot,
                    'unit_name_snapshot'     => $item->unit_name_snapshot,
                    'qty_planned'            => $item->qty_planned,
                    'price_planned'          => $item->price_planned,
                    'subtotal_planned'       => $item->subtotal_planned,
                    'notes'                  => $item->notes,
                ]);
            }
        });

        return back()->with('success', 'RAB berhasil disimpan sebagai template "' . $data['template_name'] . '".');
    }

    // createFromTemplate — buat RAB baru dari template
    public function createFromTemplate(Request $request, RabPlan $template): View
    {
        if (! $template->is_template) {
            abort(404);
        }
        $template->load('items');
        $projects  = Project::where('is_active', true)->orderBy('name')->get();
        $materials = Material::with(['unit', 'category'])->where('is_active', true)->orderBy('name')->get();

        return view('rab-plans.create-from-template', compact('template', 'projects', 'materials'));
    }

    // -------------------------------------------------------------------------
    // revise — buat revisi RAB (salin ke versi baru)
    // -------------------------------------------------------------------------
    public function revise(RabPlan $rabPlan): RedirectResponse
    {
        if (! ($rabPlan->is_latest_version ?? true)) {
            return back()->with('error', 'Hanya versi RAB terbaru yang dapat direvisi.');
        }

        $rabPlan->load('items');

        $originalId = $rabPlan->original_plan_id ?? $rabPlan->id;
        $newVersion = ($rabPlan->version ?? 1) + 1;
        $newPlan    = null;

        DB::transaction(function () use ($rabPlan, $originalId, $newVersion, &$newPlan) {
            $newPlan = RabPlan::create([
                'project_id'        => $rabPlan->project_id,
                'created_by'        => auth()->id(),
                'name'              => $rabPlan->name,
                'period_start'      => $rabPlan->period_start,
                'period_end'        => $rabPlan->period_end,
                'description'       => $rabPlan->description,
                'status'            => 'draft',
                'total_budget'      => $rabPlan->total_budget,
                'version'           => $newVersion,
                'original_plan_id'  => $originalId,
                'is_latest_version' => true,
            ]);

            // Salin semua items
            foreach ($rabPlan->items as $item) {
                $newPlan->items()->create([
                    'material_id'            => $item->material_id,
                    'material_name_snapshot' => $item->material_name_snapshot,
                    'unit_name_snapshot'     => $item->unit_name_snapshot,
                    'qty_planned'            => $item->qty_planned,
                    'price_planned'          => $item->price_planned,
                    'subtotal_planned'       => $item->subtotal_planned,
                    'notes'                  => $item->notes,
                ]);
            }

            // Tandai semua versi lain bukan latest
            RabPlan::where(function ($q) use ($originalId, $rabPlan) {
                    $q->where('id', $rabPlan->id)
                      ->orWhere('original_plan_id', $originalId);
                })
                ->where('id', '!=', $newPlan->id)
                ->update(['is_latest_version' => false]);
        });

        return redirect()
            ->route('rab-plans.show', $newPlan)
            ->with('success', "Revisi RAB v{$newVersion} berhasil dibuat. Silakan review & edit sebelum diaktifkan.");
    }
}
