<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\PurchaseTransaction;
use App\Models\TaxPph23Record;
use App\Services\Tax\TaxCalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxPph23Controller extends Controller
{
    public function index(Request $request): View
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        $status  = $request->get('status', '');

        $records = TaxPph23Record::with(['purchaseTransaction', 'creator'])
            ->when($periode, fn ($q) => $q->where('periode', $periode))
            ->when($status,  fn ($q) => $q->where('status', $status))
            ->orderByDesc('tanggal_bukti_potong')
            ->paginate(20)
            ->withQueryString();

        $sumPph      = TaxPph23Record::where('periode', $periode)->sum('pph_dipotong');
        $sumBruto    = TaxPph23Record::where('periode', $periode)->sum('bruto');
        $belumSetor  = TaxPph23Record::where('status', 'belum_disetor')->count();

        $periodeList = TaxPph23Record::selectRaw('DISTINCT periode')->pluck('periode')
            ->sortDesc()->values();

        $jenisPenghasilan = TaxCalculatorService::daftarJenisPenghasilan();

        return view('tax.pph23.index', compact(
            'records', 'periode', 'status',
            'sumPph', 'sumBruto', 'belumSetor',
            'periodeList', 'jenisPenghasilan',
        ));
    }

    public function create(Request $request): View
    {
        $transactions = PurchaseTransaction::with('supplier')
            ->orderByDesc('transaction_date')->limit(100)->get();

        $prefill  = null;
        $tarif2   = TaxCalculatorService::getTarifPph23('ada');    // 2%
        $tarif4   = TaxCalculatorService::getTarifPph23(null);     // 4%

        if ($request->filled('from_transaction')) {
            $prefill = PurchaseTransaction::with('supplier')->find($request->from_transaction);
        }

        $jenisPenghasilan = TaxCalculatorService::daftarJenisPenghasilan();

        return view('tax.pph23.create', compact(
            'transactions', 'prefill',
            'tarif2', 'tarif4',
            'jenisPenghasilan',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'purchase_transaction_id' => 'nullable|exists:purchase_transactions,id',
            'nama_penerima'           => 'required|string|max:255',
            'npwp_penerima'           => 'nullable|string|max:30',
            'jenis_penghasilan'       => 'required|string|max:100',
            'bruto'                   => 'required|numeric|min:0',
            'tarif'                   => 'required|numeric|in:2,4',
            'pph_dipotong'            => 'required|numeric|min:0',
            'no_bukti_potong'         => 'nullable|string|max:50',
            'tanggal_bukti_potong'    => 'nullable|date',
            'periode'                 => 'required|regex:/^\d{4}-\d{2}$/',
            'status'                  => 'required|in:belum_disetor,sudah_disetor',
            'tanggal_setor'           => 'nullable|date',
            'ntpn'                    => 'nullable|string|max:30',
            'keterangan'              => 'nullable|string|max:500',
        ]);

        $data['created_by'] = auth()->id();

        TaxPph23Record::create($data);

        return redirect()
            ->route('tax.pph23.index', ['periode' => $data['periode']])
            ->with('success', 'Bukti Potong PPh Pasal 23 berhasil dicatat.');
    }

    public function konfirmSetor(TaxPph23Record $record, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal_setor' => 'required|date',
            'ntpn'          => 'nullable|string|max:30',
        ]);

        $record->update(array_merge($data, ['status' => 'sudah_disetor']));

        return back()->with('success', 'Status PPh 23 diperbarui: sudah disetor.');
    }

    public function destroy(TaxPph23Record $record): RedirectResponse
    {
        $periode = $record->periode;
        $record->delete();

        return redirect()
            ->route('tax.pph23.index', ['periode' => $periode])
            ->with('success', 'Catatan PPh 23 berhasil dihapus.');
    }
}
