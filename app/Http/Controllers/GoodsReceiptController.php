<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGoodsReceiptRequest;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseTransaction;
use App\Services\Purchases\GoodsReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    public function __construct(
        protected GoodsReceiptService $goodsReceiptService,
    ) {}

    public function index(Request $request): View
    {
        $receipts = GoodsReceipt::query()
            ->with(['purchaseTransaction.supplier', 'purchaseTransaction.project',
                    'purchaseOrder.supplier', 'purchaseOrder.project', 'creator'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), fn ($q) => $q->where('receipt_no', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'    => GoodsReceipt::count(),
            'pending'  => GoodsReceipt::where('status', 'pending')->count(),
            'partial'  => GoodsReceipt::where('status', 'partial')->count(),
            'complete' => GoodsReceipt::where('status', 'complete')->count(),
        ];

        return view('goods-receipts.index', compact('receipts', 'stats'));
    }

    public function create(PurchaseTransaction $purchaseTransaction): View
    {
        if (auth()->user()->isStaff() && $purchaseTransaction->user_id !== auth()->id()) {
            abort(403);
        }

        $purchaseTransaction->load(['supplier', 'project', 'items.material.unit']);

        $receiptNo = $this->goodsReceiptService->generateReceiptNo($purchaseTransaction);
        $receivedQty = $this->goodsReceiptService->receivedQtyPerTransactionItem($purchaseTransaction);

        return view('goods-receipts.create', compact('purchaseTransaction', 'receiptNo', 'receivedQty'));
    }

    public function store(StoreGoodsReceiptRequest $request, PurchaseTransaction $purchaseTransaction): RedirectResponse
    {
        if (auth()->user()->isStaff() && $purchaseTransaction->user_id !== auth()->id()) {
            abort(403);
        }

        $receipt = $this->goodsReceiptService->store(
            $purchaseTransaction,
            $request->validated(),
            (int) auth()->id(),
        );

        return redirect()
            ->route('goods-receipts.show', $receipt)
            ->with('success', 'Surat jalan berhasil dibuat.');
    }

    public function show(GoodsReceipt $goodsReceipt): View
    {
        $goodsReceipt->load([
            'purchaseTransaction.supplier',
            'purchaseTransaction.project',
            'purchaseOrder.supplier',
            'purchaseOrder.project',
            'items',
            'creator',
        ]);

        return view('goods-receipts.show', compact('goodsReceipt'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // [NEW] GR dari Purchase Order (sebelum ada invoice/transaction)
    // ─────────────────────────────────────────────────────────────────────────

    public function createFromPo(PurchaseOrder $purchaseOrder): View|RedirectResponse
    {
        if (!$purchaseOrder->isApproved() && !$purchaseOrder->isConverted()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Hanya PO yang sudah disetujui yang bisa dibuat surat jalan.');
        }

        $purchaseOrder->load(['supplier', 'project', 'items.material']);
        $receiptNo = $this->goodsReceiptService->generateReceiptNoFromPo($purchaseOrder);

        return view('goods-receipts.create-from-po', compact('purchaseOrder', 'receiptNo'));
    }

    public function storeFromPo(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $validated = $request->validate([
            'receipt_no'        => 'required|string|max:100',
            'supplier_do_no'    => 'nullable|string|max:100',
            'supplier_do_date'  => 'nullable|date',
            'receipt_date'      => 'required|date',
            'received_by'       => 'required|string|max:255',
            'inspector_name'    => 'nullable|string|max:255',
            'inspection_status' => 'nullable|in:pending,passed,failed,partial_pass',
            'inspection_notes'  => 'nullable|string|max:1000',
            'notes'             => 'nullable|string|max:1000',
            'items'             => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'nullable|exists:purchase_order_items,id',
            'items.*.material_name_snapshot' => 'required|string|max:255',
            'items.*.unit_name_snapshot'     => 'required|string|max:100',
            'items.*.qty_ordered'            => 'required|numeric|min:0',
            'items.*.qty_received'           => 'required|numeric|min:0',
            'items.*.quality_status'         => 'nullable|in:ok,partial_reject,full_reject',
            'items.*.notes'                  => 'nullable|string|max:255',
        ]);

        $receipt = $this->goodsReceiptService->storeFromPo($purchaseOrder, $validated, auth()->id());

        return redirect()->route('goods-receipts.show', $receipt)
            ->with('success', "Surat jalan {$receipt->receipt_no} berhasil dibuat dari PO {$purchaseOrder->po_no}.");
    }

    public function confirmInspection(Request $request, GoodsReceipt $goodsReceipt): RedirectResponse
    {
        $request->validate([
            'inspection_status' => 'required|in:pending,passed,failed,partial_pass',
            'inspection_notes'  => 'nullable|string|max:1000',
        ]);

        $this->goodsReceiptService->confirmInspection(
            $goodsReceipt,
            $request->inspection_status,
            $request->inspection_notes ?? ''
        );

        return back()->with('success', 'Hasil inspeksi berhasil disimpan.');
    }

    public function exportPdf(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load([
            'purchaseTransaction.supplier',
            'purchaseTransaction.project',
            'purchaseOrder.supplier',
            'purchaseOrder.project',
            'items',
            'creator',
        ]);

        $pdf = Pdf::loadView('goods-receipts.exports.pdf', compact('goodsReceipt'))
            ->setPaper('a4', 'portrait');

        $fileName = 'surat-jalan-' . $goodsReceipt->receipt_no . '.pdf';

        return $pdf->download($fileName);
    }

    public function previewPdf(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load([
            'purchaseTransaction.supplier',
            'purchaseTransaction.project',
            'purchaseOrder.supplier',
            'purchaseOrder.project',
            'items',
            'creator',
        ]);

        $pdf = Pdf::loadView('goods-receipts.exports.pdf', compact('goodsReceipt'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('surat-jalan-' . $goodsReceipt->id . '.pdf');
    }
}
