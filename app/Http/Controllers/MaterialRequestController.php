<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\Project;
use App\Services\MaterialRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialRequestController extends Controller
{
    public function __construct(
        protected MaterialRequestService $service,
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = MaterialRequest::query()
            ->with(['project', 'requester', 'approver'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('urgency'), fn ($q) => $q->where('urgency', $request->urgency))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->project_id))
            ->when($user->role === 'staff', fn ($q) => $q->where('requested_by', $user->id));

        $materialRequests = $query->latest()->paginate(15)->withQueryString();
        $projects         = Project::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total'     => MaterialRequest::when($user->role === 'staff', fn ($q) => $q->where('requested_by', $user->id))->count(),
            'submitted' => MaterialRequest::when($user->role === 'staff', fn ($q) => $q->where('requested_by', $user->id))->where('status', 'submitted')->count(),
            'approved'  => MaterialRequest::when($user->role === 'staff', fn ($q) => $q->where('requested_by', $user->id))->where('status', 'approved')->count(),
            'converted' => MaterialRequest::when($user->role === 'staff', fn ($q) => $q->where('requested_by', $user->id))->where('status', 'converted')->count(),
        ];

        return view('material-requests.index', compact('materialRequests', 'projects', 'stats'));
    }

    public function create(): View
    {
        $projects  = Project::where('is_active', true)->orderBy('name')->get();
        $materials = Material::with('unit')->where('is_active', true)->orderBy('name')->get();
        return view('material-requests.create', compact('projects', 'materials'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id'              => 'nullable|exists:projects,id',
            'urgency'                 => 'required|in:low,normal,high,critical',
            'needed_by'               => 'nullable|date|after_or_equal:today',
            'notes'                   => 'nullable|string|max:1000',
            'items'                   => 'required|array|min:1',
            'items.*.material_id'     => 'nullable|exists:materials,id',
            'items.*.material_name'   => 'required|string|max:255',
            'items.*.unit_name'       => 'required|string|max:100',
            'items.*.qty_requested'   => 'required|numeric|min:0.01',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
            'items.*.notes'           => 'nullable|string|max:255',
        ]);

        $mr = $this->service->store($validated, auth()->id());

        return redirect()->route('material-requests.show', $mr)
            ->with('success', "Permintaan Material {$mr->request_no} berhasil dibuat.");
    }

    public function show(MaterialRequest $materialRequest): View
    {
        $this->authorizeView($materialRequest);
        $materialRequest->load(['project', 'requester', 'approver', 'items.material', 'convertedPo']);
        return view('material-requests.show', compact('materialRequest'));
    }

    public function submit(MaterialRequest $materialRequest): RedirectResponse
    {
        $user = auth()->user();
        if ($user->role === 'staff' && $materialRequest->requested_by !== $user->id) {
            abort(403);
        }
        if (! $materialRequest->isDraft()) {
            return back()->with('error', 'Hanya MR berstatus Draft yang dapat diajukan.');
        }
        $materialRequest->loadMissing(['items', 'project', 'requester']);
        $this->service->submit($materialRequest);
        return back()->with('success', 'Permintaan material berhasil diajukan untuk persetujuan.');
    }

    public function approve(MaterialRequest $materialRequest): RedirectResponse
    {
        $user = auth()->user();
        if (! in_array($user->role, ['admin', 'pimpinan'])) abort(403);
        if (! $materialRequest->isSubmitted()) {
            return back()->with('error', 'Hanya MR berstatus Menunggu yang dapat disetujui.');
        }
        $materialRequest->loadMissing(['requester']);
        $this->service->approve($materialRequest, $user->id);
        return back()->with('success', 'Permintaan material disetujui.');
    }

    public function reject(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $user = auth()->user();
        if (! in_array($user->role, ['admin', 'pimpinan'])) abort(403);
        if (! $materialRequest->isSubmitted()) {
            return back()->with('error', 'Hanya MR berstatus Menunggu yang dapat ditolak.');
        }
        $request->validate(['rejection_reason' => 'required|string|max:500']);
        $materialRequest->loadMissing(['requester']);
        $this->service->reject($materialRequest, $user->id, $request->rejection_reason);
        return back()->with('success', 'Permintaan material ditolak.');
    }

    public function convertToPo(MaterialRequest $materialRequest): RedirectResponse
    {
        $user = auth()->user();
        if (! in_array($user->role, ['admin', 'staff', 'purchasing'])) abort(403);
        if (! $materialRequest->isApproved()) {
            return back()->with('error', 'Hanya MR yang sudah disetujui yang dapat dikonversi ke PO.');
        }
        $materialRequest->loadMissing(['items', 'project']);
        $po = $this->service->convertToPo($materialRequest, $user->id);
        return redirect()->route('purchase-orders.show', $po)
            ->with('success', "MR {$materialRequest->request_no} berhasil dikonversi ke PO {$po->po_no}.");
    }

    public function destroy(MaterialRequest $materialRequest): RedirectResponse
    {
        $user = auth()->user();
        if ($user->role === 'staff' && $materialRequest->requested_by !== $user->id) abort(403);
        if (! $materialRequest->isDraft()) {
            return back()->with('error', 'Hanya MR berstatus Draft yang dapat dihapus.');
        }
        $no = $materialRequest->request_no;
        $materialRequest->items()->delete();
        $materialRequest->delete();
        return redirect()->route('material-requests.index')
            ->with('success', "MR {$no} berhasil dihapus.");
    }

    private function authorizeView(MaterialRequest $mr): void
    {
        $user = auth()->user();
        if ($user->role === 'staff' && $mr->requested_by !== $user->id) {
            abort(403);
        }
    }
}
