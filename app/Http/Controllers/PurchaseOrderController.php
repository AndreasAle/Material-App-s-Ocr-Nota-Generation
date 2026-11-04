<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRule;
use App\Models\Material;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\BudgetCheckService;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected PurchaseOrderService $service,
        protected BudgetCheckService   $budgetCheck,
    ) {}

    public function index(Request $request): View
    {
        $purchaseOrders = PurchaseOrder::query()
            ->with(['project', 'supplier', 'creator'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->project_id))
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('po_no', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $projects  = Project::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total'     => PurchaseOrder::count(),
            'draft'     => PurchaseOrder::where('status', 'draft')->count(),
            'pending'   => PurchaseOrder::where('status', 'submitted')->count(),
            'approved'  => PurchaseOrder::where('status', 'approved')->count(),
            'converted' => PurchaseOrder::where('status', 'converted')->count(),
        ];

        return view('purchase-orders.index', compact('purchaseOrders', 'projects', 'suppliers', 'stats'));
    }

    public function create(): View
    {
        $projects  = Project::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $materials = Material::with('unit')->where('is_active', true)->orderBy('name')->get();

        return view('purchase-orders.create', compact('projects', 'suppliers', 'materials'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id'             => 'nullable|exists:projects,id',
            'supplier_id'            => 'nullable|exists:suppliers,id',
            'expected_date'          => 'nullable|date',
            'payment_terms'          => 'nullable|in:cash,net7,net14,net30,net45,net60,dp50,dp30,custom',
            'dp_percent'             => 'nullable|integer|min:0|max:100',
            'payment_due_days'       => 'nullable|integer|min:0|max:365',
            'delivery_terms'         => 'nullable|string|max:255',
            'notes'                  => 'nullable|string|max:1000',
            'budget_override'        => 'nullable|boolean',
            'budget_override_reason' => 'nullable|string|max:500',
            'items'                  => 'required|array|min:1',
            'items.*.material_id'    => 'nullable|exists:materials,id',
            'items.*.material_name'  => 'required|string|max:255',
            'items.*.unit_name'      => 'required|string|max:100',
            'items.*.qty'            => 'required|numeric|min:0.01',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.notes'          => 'nullable|string|max:255',
        ]);

        $po = $this->service->store($validated, auth()->id());

        return redirect()->route('purchase-orders.show', $po)
            ->with('success', "Purchase Order {$po->po_no} berhasil dibuat.");
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load([
            'project', 'supplier', 'creator', 'approver', 'closedBy',
            'items.material',
            'goodsReceipts.items',
            'transactions',
            'materialRequest',
            'rfq',
        ]);

        $approvalRule = ApprovalRule::findRule('purchase_order', (float) $purchaseOrder->grand_total);

        return view('purchase-orders.show', compact('purchaseOrder', 'approvalRule'));
    }

    public function submit(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $user = auth()->user();

        // Staff & Purchasing hanya bisa submit PO milik sendiri
        if (in_array($user->role, ['staff', 'purchasing']) && $purchaseOrder->created_by !== $user->id) {
            abort(403);
        }

        if (! $purchaseOrder->isDraft()) {
            return back()->with('error', 'Hanya PO dengan status Draft yang dapat diajukan.');
        }

        $this->service->submit($purchaseOrder);

        return back()->with('success', 'Purchase Order berhasil diajukan untuk persetujuan.');
    }

    public function approve(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $user = auth()->user();

        if (! in_array($user->role, ['admin', 'pimpinan'])) {
            abort(403);
        }

        if (! $purchaseOrder->isSubmitted()) {
            return back()->with('error', 'Hanya PO dengan status Menunggu Persetujuan yang dapat disetujui.');
        }

        $this->service->approve($purchaseOrder, $user->id);

        return back()->with('success', 'Purchase Order berhasil disetujui.');
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $user = auth()->user();

        if (! in_array($user->role, ['admin', 'pimpinan'])) {
            abort(403);
        }

        if (! $purchaseOrder->isSubmitted()) {
            return back()->with('error', 'Hanya PO dengan status Menunggu Persetujuan yang dapat ditolak.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $this->service->reject($purchaseOrder, $user->id, $request->rejection_reason);

        return back()->with('success', 'Purchase Order berhasil ditolak.');
    }

    public function convert(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $user = auth()->user();

        if (! in_array($user->role, ['admin', 'staff', 'purchasing'])) {
            abort(403);
        }

        if (! $purchaseOrder->isApproved()) {
            return back()->with('error', 'Hanya PO yang sudah disetujui yang dapat dikonversi menjadi transaksi.');
        }

        $transaction = $this->service->convertToTransaction($purchaseOrder, $user->id);

        return redirect()->route('purchase-transactions.show', $transaction)
            ->with('success', "PO {$purchaseOrder->po_no} berhasil dikonversi menjadi transaksi {$transaction->transaction_no}.");
    }

    public function markSent(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (!$purchaseOrder->isApproved()) {
            return back()->with('error', 'Hanya PO yang sudah disetujui yang dapat dikirim.');
        }
        $this->service->markSent($purchaseOrder);
        return back()->with('success', 'PO ditandai sudah dikirim ke supplier.');
    }

    public function confirmSupplier(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $request->validate(['supplier_ref_no' => 'nullable|string|max:100']);
        $this->service->confirmBySupplier($purchaseOrder, $request->all());
        return back()->with('success', 'Konfirmasi supplier berhasil dicatat.');
    }

    public function close(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (!in_array(auth()->user()->role, ['admin', 'pimpinan'])) abort(403);
        $request->validate(['close_reason' => 'required|string|max:500']);
        $this->service->close($purchaseOrder, auth()->id(), $request->close_reason);
        return back()->with('success', 'PO ditutup.');
    }

    /**
     * Budget check AJAX
     */
    public function checkBudget(Request $request): \Illuminate\Http\JsonResponse
    {
        $projectId = $request->integer('project_id');
        $amount    = $request->float('amount');

        if (!$projectId || !$amount) {
            return response()->json(['status' => 'not_checked']);
        }

        return response()->json($this->budgetCheck->check($projectId, $amount));
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (! $purchaseOrder->isDraft()) {
            return back()->with('error', 'Hanya PO dengan status Draft yang dapat dihapus.');
        }

        $poNo = $purchaseOrder->po_no;
        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', "Purchase Order {$poNo} berhasil dihapus.");
    }
}
