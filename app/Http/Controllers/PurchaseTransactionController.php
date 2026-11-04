<?php

namespace App\Http\Controllers;

use App\Exports\PurchaseTransactionsExport;
use App\Http\Requests\StorePurchaseTransactionRequest;
use App\Http\Requests\UpdatePurchaseTransactionRequest;
use App\Models\Material;
use App\Models\Project;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use App\Services\Purchases\PurchaseScanDraftService;
use App\Services\Purchases\PurchaseTransactionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Models\PurchaseAttachment;

// use Barryvdh\DomPDF\Facade\Pdf;
// use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PurchaseTransactionDetailExport;
use Illuminate\Support\Facades\DB;

class PurchaseTransactionController extends Controller
{
    public function __construct(
        protected PurchaseTransactionService $purchaseTransactionService,
        protected PurchaseScanDraftService $purchaseScanDraftService,
    ) {
    }

    public function index(Request $request): View
    {
        $transactions = PurchaseTransaction::query()
            ->with(['supplier', 'project', 'user'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('approval_status'), fn ($q) => $q->where('approval_status', $request->approval_status))
            ->when($request->filled('start_date'), fn ($q) => $q->whereDate('transaction_date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->whereDate('transaction_date', '<=', $request->end_date))
            ->latest('transaction_date')
            ->paginate(10)
            ->withQueryString();

        return view('purchases.index', compact('transactions'));
    }

    public function create(): View
    {
        $suppliers = Supplier::query()->where('is_active', true)->orderBy('name')->get();
        $projects = Project::query()->where('is_active', true)->orderBy('name')->get();
        $materials = Material::query()
            ->with(['unit', 'category'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $importDraft = session('purchase_scan_import');

        return view('purchases.create', compact('suppliers', 'projects', 'materials', 'importDraft'));
    }

    public function store(StorePurchaseTransactionRequest $request): RedirectResponse
    {
        $importDraft = session('purchase_scan_import');
        $data = $request->validated();
        $transaction = null;

        if (is_array($importDraft) && ! empty($importDraft['scan_draft_id'])) {
            $transaction = DB::transaction(function () use (&$data, $importDraft) {
                $draft = \App\Models\PurchaseScanDraft::query()
                    ->lockForUpdate()
                    ->findOrFail($importDraft['scan_draft_id']);

                abort_unless($draft->status === 'reviewed', 409, 'Draft scan sudah digunakan atau belum direview.');
                abort_unless(! auth()->user()->isStaff() || (int) $draft->created_by === (int) auth()->id(), 403);

                $data['input_source'] = 'scanner';
                $data['scan_source_type'] = 'single';
                $data['scan_reference_id'] = $draft->id;

                $created = $this->purchaseTransactionService->store($data, (int) auth()->id());
                $draft->update(['status' => 'imported']);

                return $created;
            });
        } elseif (is_array($importDraft) && ! empty($importDraft['scan_batch_id'])) {
            $transaction = DB::transaction(function () use (&$data, $importDraft) {
                $batch = \App\Models\PurchaseScanBatch::query()
                    ->lockForUpdate()
                    ->findOrFail($importDraft['scan_batch_id']);

                abort_unless($batch->status === 'reviewed', 409, 'Batch scan sudah digunakan atau belum direview.');
                abort_unless(! auth()->user()->isStaff() || (int) $batch->created_by === (int) auth()->id(), 403);

                $data['input_source'] = 'scanner';
                $data['scan_source_type'] = 'multiple';
                $data['scan_reference_id'] = $batch->id;

                $created = $this->purchaseTransactionService->store($data, (int) auth()->id());
                $batch->update(['status' => 'imported']);

                return $created;
            });
        } else {
            $transaction = $this->purchaseTransactionService->store($data, (int) auth()->id());
        }

        session()->forget('purchase_scan_import');

        $priceAlerts = app(\App\Services\Store\PurchasePriceAlertService::class)->check($transaction);
        $message = 'Transaksi pembelian berhasil dibuat.';
        if ($request->boolean('mark_received')) {
            $message .= ' Barang tercatat diterima dan stok sudah bertambah.';
        }

        return redirect()
            ->route('purchase-transactions.show', $transaction)
            ->with('success', $message)
            ->with('price_alerts', $priceAlerts);
    }

    public function show(PurchaseTransaction $purchaseTransaction): View
    {
        $purchaseTransaction->load(['supplier', 'project', 'user', 'items.material', 'attachments', 'goodsReceipts', 'returns.items', 'ppnMasukan']);

        return view('purchases.show', compact('purchaseTransaction'));
    }

    public function edit(PurchaseTransaction $purchaseTransaction): View|RedirectResponse
    {
        if (auth()->user()->isStaff() && $purchaseTransaction->user_id !== auth()->id()) {
            abort(403);
        }

        if ($error = $this->editBlockedReason($purchaseTransaction)) {
            return redirect()->route('purchase-transactions.show', $purchaseTransaction)->with('error', $error);
        }

        $purchaseTransaction->load(['items.material', 'attachments']);
        $suppliers = Supplier::query()->where('is_active', true)->orderBy('name')->get();
        $projects = Project::query()->where('is_active', true)->orderBy('name')->get();
        $materials = Material::query()
            ->with(['unit', 'category'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('purchases.edit', compact('purchaseTransaction', 'suppliers', 'projects', 'materials'));
    }

    public function update(UpdatePurchaseTransactionRequest $request, PurchaseTransaction $purchaseTransaction): RedirectResponse
    {
        if (auth()->user()->isStaff() && $purchaseTransaction->user_id !== auth()->id()) {
            abort(403);
        }

        if ($error = $this->editBlockedReason($purchaseTransaction)) {
            return back()->withInput()->with('error', $error);
        }

        $newTotal = collect($request->validated()['items'] ?? [])->sum(fn ($i) => (float) $i['qty'] * (float) $i['price']);
        if ($newTotal + 0.01 < (float) $purchaseTransaction->paid_amount) {
            return back()->withInput()->with('error', 'Total baru lebih kecil dari jumlah yang sudah dibayar ke supplier. Hapus/koreksi pembayaran terlebih dahulu.');
        }

        $wasApproved = in_array($purchaseTransaction->approval_status, ['pending', 'approved'], true);
        $transaction = $this->purchaseTransactionService->update($purchaseTransaction, $request->validated());

        if ($wasApproved) {
            return redirect()
                ->route('purchase-transactions.show', $transaction)
                ->with('success', 'Transaksi diperbarui. Karena isinya berubah, status persetujuan kembali ke Draft dan perlu diajukan ulang.');
        }

        return redirect()
            ->route('purchase-transactions.show', $transaction)
            ->with('success', 'Transaksi pembelian berhasil diperbarui.');
    }

    public function destroy(PurchaseTransaction $purchaseTransaction): RedirectResponse
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $blockers = array_filter([
            $purchaseTransaction->goodsReceipts()->exists() ? 'surat jalan' : null,
            $purchaseTransaction->supplierPayments()->exists() ? 'pembayaran supplier' : null,
            $purchaseTransaction->returns()->exists() ? 'retur' : null,
        ]);
        if ($blockers) {
            return back()->with('error', 'Transaksi tidak bisa dihapus karena sudah punya ' . implode(', ', $blockers) . '. Hapus data tersebut terlebih dahulu.');
        }

        $attachments = $purchaseTransaction->attachments()->get();
        $purchaseTransaction->delete();

        foreach ($attachments as $attachment) {
            Storage::disk($attachment->storageDisk())->delete($attachment->file_path);
        }

        return redirect()
            ->route('purchase-transactions.index')
            ->with('success', 'Transaksi pembelian berhasil dihapus.');
    }

    /**
     * Item transaksi tidak boleh diubah setelah barang diterima (surat jalan & stok mengacu ke item ini).
     */
    private function editBlockedReason(PurchaseTransaction $transaction): ?string
    {
        if ($transaction->goodsReceipts()->exists()) {
            return 'Transaksi sudah memiliki surat jalan sehingga item tidak bisa diubah lagi. Gunakan Retur untuk koreksi barang.';
        }

        return null;
    }

    public function lookupMaterialByCode(Request $request): JsonResponse
    {
        $code = trim((string) $request->get('code'));

        if ($code === '') {
            return response()->json([
                'success' => false,
                'message' => 'Kode material wajib diisi.',
            ], 422);
        }

        $material = Material::query()
            ->with(['unit', 'category'])
            ->where('is_active', true)
            ->where(function ($query) use ($code) {
                $query->where('code', $code)
                    ->orWhere('code', strtoupper($code))
                    ->orWhere('code', strtolower($code));
            })
            ->first();

        if (! $material) {
            return response()->json([
                'success' => false,
                'message' => 'Material dengan kode tersebut tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $material->id,
                'code' => $material->code,
                'name' => $material->name,
                'unit_name' => $material->unit?->name,
                'category_name' => $material->category?->name,
                'default_price' => (float) $material->default_price,
                'formatted_default_price' => number_format((float) $material->default_price, 0, ',', '.'),
            ],
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new PurchaseTransactionsExport(
                status: $request->string('status')->toString(),
                startDate: $request->string('start_date')->toString(),
                endDate: $request->string('end_date')->toString(),
            ),
            'rekap-pembelian-material.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $transactions = PurchaseTransaction::query()
            ->with(['supplier', 'project', 'user'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('start_date'), fn ($q) => $q->whereDate('transaction_date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->whereDate('transaction_date', '<=', $request->end_date))
            ->latest('transaction_date')
            ->get();

        $pdf = Pdf::loadView('purchases.export-pdf', [
            'transactions' => $transactions,
            'filters' => [
                'status' => $request->status,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rekap-pembelian-material.pdf');
    }


    public function showAttachment(PurchaseAttachment $attachment)
    {
        $disk = $attachment->storageDisk();
        abort_unless(Storage::disk($disk)->exists($attachment->file_path), 404, 'File lampiran tidak ditemukan.');

        $mime = strtolower((string) $attachment->mime_type);
        $absolute = Storage::disk($disk)->path($attachment->file_path);

        // Hanya PDF & gambar yang ditampilkan inline, sisanya diunduh
        if (! in_array($mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true)) {
            return response()->download($absolute, $attachment->file_name);
        }

        return response()->file($absolute, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($attachment->file_name) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroyAttachment(PurchaseAttachment $attachment): RedirectResponse
{
    $transaction = $attachment->purchaseTransaction;

    if (! $transaction) {
        abort(404);
    }

    if (auth()->user()->isStaff() && $transaction->user_id !== auth()->id()) {
        abort(403);
    }

    $this->purchaseTransactionService->deleteAttachment($attachment);

    return redirect()
        ->route('purchase-transactions.edit', $transaction)
        ->with('success', 'Lampiran berhasil dihapus.');
}


public function exportDetailPdf(PurchaseTransaction $purchaseTransaction)
{
    $purchaseTransaction->load([
        'supplier',
        'project',
        'user',
        'items',
        'attachments',
    ]);

    $pdf = Pdf::loadView('purchase-transactions.exports.pdf-detail', [
        'purchaseTransaction' => $purchaseTransaction,
    ])->setPaper('a4', 'portrait');

    $fileName = 'transaksi-pembelian-' . $purchaseTransaction->transaction_no . '.pdf';

    return $pdf->download($fileName);
}
public function exportDetailExcel(PurchaseTransaction $purchaseTransaction)
{
    $purchaseTransaction->load([
        'supplier',
        'project',
        'user',
        'items',
        'attachments',
    ]);

    $fileName = 'transaksi-pembelian-' . $purchaseTransaction->transaction_no . '.xlsx';

    return Excel::download(
        new PurchaseTransactionDetailExport($purchaseTransaction),
        $fileName
    );
}

public function previewDetailPdf(PurchaseTransaction $purchaseTransaction)
{
    $purchaseTransaction->load([
        'supplier',
        'project',
        'user',
        'items',
        'attachments',
    ]);

    $pdf = Pdf::loadView('purchase-transactions.exports.pdf-detail', [
        'purchaseTransaction' => $purchaseTransaction,
    ])->setPaper('a4', 'portrait');

    return $pdf->stream('pdf-detail-' . $purchaseTransaction->id . '.pdf');
}

}
