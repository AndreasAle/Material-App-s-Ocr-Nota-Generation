<?php

namespace App\Http\Controllers;

use App\Models\Subcontractor;
use App\Models\Subcontract;
use App\Models\SubcontractPayment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class SubcontractorController extends Controller
{
    // ─── Master Subkontraktor ────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Subcontractor::withCount('subcontracts')
            ->withSum('subcontracts', 'contract_value');

        if ($search = $request->search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('specialization', 'like', "%{$search}%"));
        }
        if ($request->has('active')) {
            $query->where('is_active', (bool) $request->active);
        }

        $subcontractors = $query->orderBy('name')->paginate(15)->withQueryString();
        return view('subcontractors.index', compact('subcontractors'));
    }

    public function create(): View
    {
        return view('subcontractors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:50|unique:subcontractors,code',
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string',
            'specialization' => 'nullable|string|max:150',
            'notes'          => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        Subcontractor::create($data);

        return redirect()->route('subcontractors.index')
            ->with('success', 'Subkontraktor berhasil ditambahkan.');
    }

    public function show(Subcontractor $subcontractor): View
    {
        $subcontractor->load(['subcontracts.project', 'subcontracts.payments']);

        $totalContract = $subcontractor->subcontracts->sum('contract_value');
        $totalPaid     = $subcontractor->subcontracts->sum('paid_amount');
        $totalSisa     = $totalContract - $totalPaid;

        return view('subcontractors.show', compact('subcontractor', 'totalContract', 'totalPaid', 'totalSisa'));
    }

    public function edit(Subcontractor $subcontractor): View
    {
        return view('subcontractors.edit', compact('subcontractor'));
    }

    public function update(Request $request, Subcontractor $subcontractor): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:50|unique:subcontractors,code,' . $subcontractor->id,
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string',
            'specialization' => 'nullable|string|max:150',
            'notes'          => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $subcontractor->update($data);

        return redirect()->route('subcontractors.show', $subcontractor)
            ->with('success', 'Data subkontraktor diperbarui.');
    }

    public function destroy(Subcontractor $subcontractor): RedirectResponse
    {
        if ($subcontractor->subcontracts()->exists()) {
            return back()->with('error', 'Subkontraktor memiliki SPK dan tidak bisa dihapus.');
        }
        $subcontractor->delete();
        return redirect()->route('subcontractors.index')
            ->with('success', 'Subkontraktor dihapus.');
    }

    // ─── SPK (Subcontract) ───────────────────────────────────────────────────

    public function spkIndex(Request $request): View
    {
        $query = Subcontract::with(['subcontractor', 'project']);

        if ($s = $request->search) {
            $query->where(fn($q) => $q->where('spk_no', 'like', "%{$s}%")
                ->orWhere('work_name', 'like', "%{$s}%")
                ->orWhereHas('subcontractor', fn($q2) => $q2->where('name', 'like', "%{$s}%")));
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        $subcontracts = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $projects     = Project::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('subcontractors.spk.index', compact('subcontracts', 'projects'));
    }

    public function spkCreate(Request $request): View
    {
        $subcontractors = Subcontractor::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $projects       = Project::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $selectedSubId  = $request->subcontractor_id;
        $selectedProjId = $request->project_id;

        return view('subcontractors.spk.create', compact('subcontractors', 'projects', 'selectedSubId', 'selectedProjId'));
    }

    public function spkStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'project_id'       => 'required|exists:projects,id',
            'work_name'        => 'required|string|max:255',
            'scope_of_work'    => 'nullable|string',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'contract_value'   => 'required|numeric|min:0',
            'notes'            => 'nullable|string',
        ]);

        $data['spk_no']     = Subcontract::generateSpkNo();
        $data['created_by'] = auth()->id();

        $spk = Subcontract::create($data);

        return redirect()->route('subcontractors.spk.show', $spk)
            ->with('success', 'SPK ' . $spk->spk_no . ' berhasil dibuat.');
    }

    public function spkShow(Subcontract $subcontract): View
    {
        $subcontract->load(['subcontractor', 'project', 'payments.creator', 'creator']);
        $totalPaid  = $subcontract->payments->sum('amount');
        // sync paid_amount
        if ((float) $subcontract->paid_amount !== $totalPaid) {
            $subcontract->update(['paid_amount' => $totalPaid]);
        }
        return view('subcontractors.spk.show', compact('subcontract', 'totalPaid'));
    }

    public function spkEdit(Subcontract $subcontract): View
    {
        $subcontractors = Subcontractor::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $projects       = Project::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return view('subcontractors.spk.edit', compact('subcontract', 'subcontractors', 'projects'));
    }

    public function spkUpdate(Request $request, Subcontract $subcontract): RedirectResponse
    {
        $data = $request->validate([
            'work_name'      => 'required|string|max:255',
            'scope_of_work'  => 'nullable|string',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'contract_value' => 'required|numeric|min:0',
            'progress_pct'   => 'required|integer|min:0|max:100',
            'status'         => 'required|in:draft,active,completed,cancelled',
            'notes'          => 'nullable|string',
        ]);

        $subcontract->update($data);

        return redirect()->route('subcontractors.spk.show', $subcontract)
            ->with('success', 'SPK diperbarui.');
    }

    // ─── Pembayaran SPK ──────────────────────────────────────────────────────

    public function paymentStore(Request $request, Subcontract $subcontract): RedirectResponse
    {
        $data = $request->validate([
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'nullable|string|max:50',
            'reference_no'   => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
        ]);

        DB::transaction(function () use ($subcontract, $data) {
            $data['subcontract_id'] = $subcontract->id;
            $data['created_by']     = auth()->id();
            SubcontractPayment::create($data);

            // recalculate paid_amount
            $total = $subcontract->payments()->sum('amount') + $data['amount'];
            $subcontract->update(['paid_amount' => $total]);
        });

        return back()->with('success', 'Pembayaran sebesar Rp ' . number_format($data['amount'], 0, ',', '.') . ' dicatat.');
    }

    public function paymentDestroy(SubcontractPayment $payment): RedirectResponse
    {
        $subcontract = $payment->subcontract;
        DB::transaction(function () use ($subcontract, $payment) {
            $payment->delete();
            $total = $subcontract->payments()->sum('amount');
            $subcontract->update(['paid_amount' => $total]);
        });

        return back()->with('success', 'Pembayaran dihapus.');
    }
}
