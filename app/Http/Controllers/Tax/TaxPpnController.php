<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use App\Models\TaxPpnKeluaran;
use App\Models\TaxPpnMasukan;
use App\Services\Tax\TaxCalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaxPpnController extends Controller
{
    // ─────────────────────────────────────────────────
    //  INDEX — tab masukan / keluaran
    // ─────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $tab     = $request->get('tab', 'masukan');
        $periode = $request->get('periode', now()->format('Y-m'));

        $masukan = TaxPpnMasukan::with(['purchaseTransaction', 'supplier', 'creator'])
            ->when($periode, fn ($q) => $q->where('periode', $periode))
            ->orderByRaw('CASE WHEN no_faktur IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('tanggal_faktur')
            ->paginate(20, ['*'], 'pm')
            ->withQueryString();

        $keluaran = TaxPpnKeluaran::with(['invoice', 'creator'])
            ->when($periode, fn ($q) => $q->where('periode', $periode))
            ->orderByDesc('tanggal_faktur')
            ->paginate(20, ['*'], 'pk')
            ->withQueryString();

        // Ringkasan periode (hanya faktur yang sudah lengkap / tidak draft)
        $sumMasukan  = TaxPpnMasukan::where('periode', $periode)->where('dapat_dikreditkan', true)->whereNotNull('no_faktur')->sum('ppn');
        $sumKeluaran = TaxPpnKeluaran::where('periode', $periode)->sum('ppn');
        $selisih     = $sumKeluaran - $sumMasukan;

        // Jumlah draft (no_faktur belum diisi) di periode ini
        $draftMasukanCount = TaxPpnMasukan::where('periode', $periode)->whereNull('no_faktur')->count();

        // Daftar periode yang sudah ada data
        $periodeList = TaxPpnMasukan::selectRaw('DISTINCT periode')->pluck('periode')
            ->merge(TaxPpnKeluaran::selectRaw('DISTINCT periode')->pluck('periode'))
            ->unique()->sortDesc()->values();

        return view('tax.ppn.index', compact(
            'tab', 'periode',
            'masukan', 'keluaran',
            'sumMasukan', 'sumKeluaran', 'selisih',
            'draftMasukanCount',
            'periodeList',
        ));
    }

    // ─────────────────────────────────────────────────
    //  PPN MASUKAN — create / store
    // ─────────────────────────────────────────────────

    public function createMasukan(Request $request): View
    {
        $suppliers    = Supplier::where('is_active', true)->orderBy('name')->get();
        $transactions = PurchaseTransaction::with('supplier')
            ->orderByDesc('transaction_date')->limit(100)->get();

        // Auto-fill dari transaksi jika ada query param
        $prefill = null;
        if ($request->filled('from_transaction')) {
            $prefill = PurchaseTransaction::with('supplier')->find($request->from_transaction);
        }

        $tarifPpn = TaxCalculatorService::TARIF_PPN;

        return view('tax.ppn.create-masukan', compact('suppliers', 'transactions', 'prefill', 'tarifPpn'));
    }

    public function storeMasukan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'purchase_transaction_id' => 'nullable|exists:purchase_transactions,id',
            'supplier_id'             => 'nullable|exists:suppliers,id',
            'no_faktur'               => 'required|string|max:30',
            'tanggal_faktur'          => 'required|date',
            'npwp_penjual'            => 'nullable|string|max:30',
            'nama_penjual'            => 'required|string|max:255',
            'dpp'                     => 'required|numeric|min:0',
            'ppn'                     => 'required|numeric|min:0',
            'periode'                 => 'required|regex:/^\d{4}-\d{2}$/',
            'status'                  => 'required|in:normal,retur',
            'dapat_dikreditkan'       => 'nullable|boolean',
            'keterangan'              => 'nullable|string|max:500',
        ]);

        $data['dapat_dikreditkan'] = $request->boolean('dapat_dikreditkan');
        $data['created_by']        = auth()->id();

        TaxPpnMasukan::create($data);

        return redirect()
            ->route('tax.ppn.index', ['tab' => 'masukan', 'periode' => $data['periode']])
            ->with('success', 'Faktur Pajak Masukan berhasil dicatat.');
    }

    // ─────────────────────────────────────────────────
    //  PPN KELUARAN — create / store
    // ─────────────────────────────────────────────────

    public function createKeluaran(Request $request): View
    {
        $invoices = Invoice::with('project')
            ->orderByDesc('invoice_date')->limit(100)->get();

        $prefill = null;
        if ($request->filled('from_invoice')) {
            $prefill = Invoice::with('project')->find($request->from_invoice);
        }

        $tarifPpn = TaxCalculatorService::TARIF_PPN;

        return view('tax.ppn.create-keluaran', compact('invoices', 'prefill', 'tarifPpn'));
    }

    public function storeKeluaran(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'invoice_id'     => 'nullable|exists:invoices,id',
            'no_faktur'      => 'required|string|max:30',
            'tanggal_faktur' => 'required|date',
            'npwp_pembeli'   => 'nullable|string|max:30',
            'nama_pembeli'   => 'required|string|max:255',
            'alamat_pembeli' => 'nullable|string|max:500',
            'dpp'            => 'required|numeric|min:0',
            'ppn'            => 'required|numeric|min:0',
            'periode'        => 'required|regex:/^\d{4}-\d{2}$/',
            'status'         => 'required|in:normal,retur',
            'keterangan'     => 'nullable|string|max:500',
        ]);

        $data['created_by'] = auth()->id();

        TaxPpnKeluaran::create($data);

        return redirect()
            ->route('tax.ppn.index', ['tab' => 'keluaran', 'periode' => $data['periode']])
            ->with('success', 'Faktur Pajak Keluaran berhasil dicatat.');
    }

    // ─────────────────────────────────────────────────
    //  PPN MASUKAN — edit / update (untuk draft otomatis)
    // ─────────────────────────────────────────────────

    public function editMasukan(TaxPpnMasukan $masukan): View
    {
        $suppliers    = Supplier::where('is_active', true)->orderBy('name')->get();
        $transactions = PurchaseTransaction::with('supplier')
            ->orderByDesc('transaction_date')->limit(100)->get();
        $tarifPpn = TaxCalculatorService::TARIF_PPN;

        return view('tax.ppn.edit-masukan', compact('masukan', 'suppliers', 'transactions', 'tarifPpn'));
    }

    public function updateMasukan(Request $request, TaxPpnMasukan $masukan): RedirectResponse
    {
        $data = $request->validate([
            'purchase_transaction_id' => 'nullable|exists:purchase_transactions,id',
            'supplier_id'             => 'nullable|exists:suppliers,id',
            'no_faktur'               => 'required|string|max:30',
            'tanggal_faktur'          => 'required|date',
            'npwp_penjual'            => 'nullable|string|max:30',
            'nama_penjual'            => 'required|string|max:255',
            'dpp'                     => 'required|numeric|min:0',
            'ppn'                     => 'required|numeric|min:0',
            'periode'                 => 'required|regex:/^\d{4}-\d{2}$/',
            'status'                  => 'required|in:normal,retur',
            'dapat_dikreditkan'       => 'nullable|boolean',
            'keterangan'              => 'nullable|string|max:500',
        ]);

        $data['dapat_dikreditkan'] = $request->boolean('dapat_dikreditkan');

        $masukan->update($data);

        return redirect()
            ->route('tax.ppn.index', ['tab' => 'masukan', 'periode' => $masukan->periode])
            ->with('success', 'Faktur Pajak Masukan berhasil dilengkapi.');
    }

    // ─────────────────────────────────────────────────
    //  Hapus
    // ─────────────────────────────────────────────────

    public function destroyMasukan(TaxPpnMasukan $masukan): RedirectResponse
    {
        $periode = $masukan->periode;
        $masukan->delete();

        return redirect()
            ->route('tax.ppn.index', ['tab' => 'masukan', 'periode' => $periode])
            ->with('success', 'Faktur Masukan berhasil dihapus.');
    }

    public function destroyKeluaran(TaxPpnKeluaran $keluaran): RedirectResponse
    {
        $periode = $keluaran->periode;
        $keluaran->delete();

        return redirect()
            ->route('tax.ppn.index', ['tab' => 'keluaran', 'periode' => $periode])
            ->with('success', 'Faktur Keluaran berhasil dihapus.');
    }
}
