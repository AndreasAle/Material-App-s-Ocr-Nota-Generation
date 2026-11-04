<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\PurchaseReturn;
use App\Models\PurchaseTransaction;
use App\Services\Purchases\PurchaseReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected PurchaseReturnService $service,
    ) {}

    public function index(Request $request): View
    {
        $returns = PurchaseReturn::with(['purchaseTransaction.supplier', 'creator', 'approvedBy'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('reason'), fn ($q) => $q->where('reason', $request->reason))
            ->when($request->filled('search'), fn ($q) => $q->where('return_no', 'like', '%'.$request->search.'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'     => PurchaseReturn::count(),
            'draft'     => PurchaseReturn::where('status', 'draft')->count(),
            'submitted' => PurchaseReturn::where('status', 'submitted')->count(),
            'returned'  => PurchaseReturn::where('status', 'returned')->count(),
            'credited'  => PurchaseReturn::where('status', 'credited')->count(),
        ];

        return view('purchase-returns.index', compact('returns', 'stats'));
    }

    public function create(PurchaseTransaction $purchaseTransaction): View
    {
        $purchaseTransaction->load(['supplier', 'project', 'items.material', 'goodsReceipts.items']);

        return view('purchase-returns.create', compact('purchaseTransaction'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_transaction_id'              => 'required|exists:purchase_transactions,id',
            'reason'                               => 'required|in:wrong_item,damaged,quantity_excess,quality_reject,spec_mismatch,other',
            'description'                          => 'nullable|string|max:1000',
            'credit_note_no'                       => 'nullable|string|max:100',
            'notes'                                => 'nullable|string|max:1000',
            'items'                                => 'required|array|min:1',
            'items.*.purchase_transaction_item_id' => 'nullable|exists:purchase_transaction_items,id',
            'items.*.material_name_snapshot'       => 'required|string|max:255',
            'items.*.unit_name_snapshot'           => 'nullable|string|max:100',
            'items.*.qty_returned'                 => 'required|numeric|min:0',
            'items.*.unit_price'                   => 'required|numeric|min:0',
            'items.*.notes'                        => 'nullable|string|max:255',
        ]);

        // Filter out items with zero qty
        $validated['items'] = array_filter($validated['items'], fn ($i) => (float)$i['qty_returned'] > 0);

        if (empty($validated['items'])) {
            return back()->withErrors(['items' => 'Isi qty return minimal satu item.'])->withInput();
        }

        $purchaseTransaction = PurchaseTransaction::find($validated['purchase_transaction_id']);
        $validated['supplier_id'] = $purchaseTransaction->supplier_id;
        $validated['project_id']  = $purchaseTransaction->project_id;

        $return = $this->service->store($validated, auth()->id());

        return redirect()->route('purchase-returns.show', $return)
            ->with('success', "Return {$return->return_no} berhasil dibuat.");
    }

    public function show(PurchaseReturn $purchaseReturn): View
    {
        $purchaseReturn->load(['purchaseTransaction.supplier', 'goodsReceipt', 'items', 'creator', 'approvedBy', 'project']);
        return view('purchase-returns.show', compact('purchaseReturn'));
    }

    public function submit(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        if (!$purchaseReturn->isDraft()) {
            return back()->with('error', 'Hanya return berstatus Draft yang dapat diajukan.');
        }
        $this->service->submit($purchaseReturn);
        return back()->with('success', 'Return berhasil diajukan untuk persetujuan.');
    }

    public function approve(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        if (!in_array(auth()->user()->role, ['admin', 'pimpinan'])) abort(403);
        if (!$purchaseReturn->isSubmitted()) {
            return back()->with('error', 'Hanya return berstatus Menunggu yang dapat disetujui.');
        }
        $this->service->approve($purchaseReturn, auth()->id());
        return back()->with('success', 'Return disetujui. Proses pengembalian barang dapat dilakukan.');
    }

    public function reject(Request $request, PurchaseReturn $purchaseReturn): RedirectResponse
    {
        if (!in_array(auth()->user()->role, ['admin', 'pimpinan'])) abort(403);
        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $purchaseReturn->update([
            'status'           => 'draft',
            'rejection_reason' => $request->rejection_reason,
        ]);
        return back()->with('success', 'Return dikembalikan ke draft.');
    }

    public function markReturned(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        if (!$purchaseReturn->isApproved()) {
            return back()->with('error', 'Return belum disetujui.');
        }
        $this->service->markReturned($purchaseReturn);
        return back()->with('success', 'Status diperbarui: Barang dikembalikan ke supplier.');
    }

    public function markCredited(Request $request, PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $validated = $request->validate([
            'credit_note_no'   => 'nullable|string|max:100',
            'credit_note_date' => 'nullable|date',
            'credit_amount'    => 'required|numeric|min:0',
        ]);

        if (! in_array($purchaseReturn->status, ['approved', 'returned'], true)) {
            return back()->with('error', 'Nota kredit hanya bisa dicatat untuk retur yang sudah disetujui.');
        }

        $tx = $purchaseReturn->purchaseTransaction;
        if ($tx) {
            $remaining = (float) $tx->grand_total - (float) $tx->paid_amount
                - app(\App\Services\Accounting\SupplierPaymentService::class)->creditedAmount($tx);
            if ((float) $validated['credit_amount'] > $remaining + 0.01) {
                return back()->withInput()->with('error', 'Nilai nota kredit melebihi sisa hutang transaksi (Rp ' . number_format(max(0, $remaining), 0, ',', '.') . ').');
            }
        }

        $this->service->markCredited($purchaseReturn, $validated);
        return back()->with('success', 'Credit note dari supplier berhasil dicatat.');
    }
}
