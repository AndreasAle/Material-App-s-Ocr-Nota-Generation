<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\Project;
use App\Models\Rfq;
use App\Models\RfqResponse;
use App\Models\Supplier;
use App\Services\RfqService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RfqController extends Controller
{
    public function __construct(
        protected RfqService $service,
    ) {}

    public function index(Request $request): View
    {
        $rfqs = Rfq::with(['project', 'creator', 'awardedSupplier'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->project_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $projects = Project::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total'    => Rfq::count(),
            'draft'    => Rfq::where('status', 'draft')->count(),
            'sent'     => Rfq::where('status', 'sent')->count(),
            'awarded'  => Rfq::where('status', 'awarded')->count(),
        ];

        return view('rfq.index', compact('rfqs', 'projects', 'stats'));
    }

    public function create(Request $request): View
    {
        $projects  = Project::where('is_active', true)->orderBy('name')->get();
        $materials = Material::with('unit')->where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        // Pre-fill dari MR jika ada
        $materialRequest = null;
        if ($request->filled('mr_id')) {
            $materialRequest = MaterialRequest::with('items.material.unit')->find($request->mr_id);
        }

        return view('rfq.create', compact('projects', 'materials', 'suppliers', 'materialRequest'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'material_request_id'    => 'nullable|exists:material_requests,id',
            'project_id'             => 'nullable|exists:projects,id',
            'deadline'               => 'required|date|after:today',
            'notes'                  => 'nullable|string|max:1000',
            'terms_and_conditions'   => 'nullable|string|max:2000',
            'items'                  => 'required|array|min:1',
            'items.*.material_id'    => 'nullable|exists:materials,id',
            'items.*.material_name'  => 'required|string|max:255',
            'items.*.unit_name'      => 'required|string|max:100',
            'items.*.qty_required'   => 'required|numeric|min:0.01',
            'items.*.budget_price'   => 'nullable|numeric|min:0',
            'items.*.specification'  => 'nullable|string|max:500',
        ]);

        $rfq = $this->service->store($validated, auth()->id());

        return redirect()->route('rfq.show', $rfq)
            ->with('success', "RFQ {$rfq->rfq_no} berhasil dibuat.");
    }

    public function show(Rfq $rfq): View
    {
        $rfq->load(['project', 'creator', 'items.material', 'responses.supplier', 'responses.items.rfqItem',
                    'awardedSupplier', 'awardedResponse', 'convertedPo']);

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('rfq.show', compact('rfq', 'suppliers'));
    }

    public function send(Request $request, Rfq $rfq): RedirectResponse
    {
        if (!$rfq->isDraft()) {
            return back()->with('error', 'RFQ sudah pernah dikirim.');
        }

        $request->validate([
            'supplier_ids'   => 'required|array|min:1',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        $this->service->send($rfq, $request->supplier_ids);

        return back()->with('success', "RFQ dikirim ke " . count($request->supplier_ids) . " supplier.");
    }

    public function editResponse(Rfq $rfq, RfqResponse $response): View
    {
        $response->load(['supplier', 'items.rfqItem']);
        return view('rfq.edit-response', compact('rfq', 'response'));
    }

    public function submitResponse(Request $request, Rfq $rfq, RfqResponse $response): RedirectResponse
    {
        $validated = $request->validate([
            'valid_until'     => 'nullable|date',
            'supplier_ref_no' => 'nullable|string|max:100',
            'payment_terms'   => 'required|in:cash,net7,net14,net30,net45,net60,dp50,dp30,custom',
            'delivery_days'   => 'nullable|integer|min:1',
            'notes'           => 'nullable|string|max:1000',
            'items'           => 'required|array',
            'items.*.rfq_item_id' => 'required|exists:rfq_items,id',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.notes'       => 'nullable|string|max:255',
        ]);

        $this->service->submitResponse($response, $validated);

        return redirect()->route('rfq.show', $rfq)
            ->with('success', "Penawaran dari {$response->supplier?->name} berhasil disimpan.");
    }

    public function compare(Rfq $rfq): View
    {
        $rfq->load(['items', 'responses' => fn ($q) => $q->where('status', 'submitted'),
                    'responses.supplier', 'responses.items.rfqItem']);

        return view('rfq.compare', compact('rfq'));
    }

    public function award(Request $request, Rfq $rfq): RedirectResponse
    {
        $request->validate([
            'response_id' => 'required|exists:rfq_responses,id',
        ]);

        $po = $this->service->award($rfq, (int) $request->response_id, auth()->id());

        return redirect()->route('purchase-orders.show', $po)
            ->with('success', "RFQ {$rfq->rfq_no} — pemenang dipilih. PO {$po->po_no} dibuat.");
    }

    public function close(Request $request, Rfq $rfq): RedirectResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $this->service->close($rfq, $request->reason ?? '');
        return back()->with('success', 'RFQ ditutup.');
    }
}
