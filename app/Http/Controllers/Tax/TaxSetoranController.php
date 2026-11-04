<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\TaxSetoran;
use App\Services\Tax\TaxCalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxSetoranController extends Controller
{
    public function index(Request $request): View
    {
        $periode    = $request->get('periode', now()->format('Y-m'));
        $jenisPajak = $request->get('jenis_pajak', '');
        $status     = $request->get('status', '');

        $setoran = TaxSetoran::with('creator')
            ->when($periode,    fn ($q) => $q->where('periode', $periode))
            ->when($jenisPajak, fn ($q) => $q->where('jenis_pajak', $jenisPajak))
            ->when($status,     fn ($q) => $q->where('status', $status))
            ->orderBy('jatuh_tempo')
            ->paginate(20)
            ->withQueryString();

        $overdue = TaxSetoran::where('status', 'belum_bayar')
            ->whereDate('jatuh_tempo', '<', today())
            ->count();

        $sumBelum  = TaxSetoran::where('status', 'belum_bayar')->where('periode', $periode)->sum('jumlah');
        $sumSudah  = TaxSetoran::where('status', 'sudah_bayar')->where('periode', $periode)->sum('jumlah');

        $periodeList = TaxSetoran::selectRaw('DISTINCT periode')->pluck('periode')
            ->sortDesc()->values();

        return view('tax.setoran.index', compact(
            'setoran', 'periode', 'jenisPajak', 'status',
            'overdue', 'sumBelum', 'sumSudah',
            'periodeList',
        ));
    }

    public function create(): View
    {
        $jenisPajakList = [
            'ppn'           => 'PPN Masa',
            'pph21'         => 'PPh Pasal 21',
            'pph23'         => 'PPh Pasal 23',
            'pph_final_4_2' => 'PPh Final Pasal 4(2)',
        ];

        return view('tax.setoran.create', compact('jenisPajakList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'periode'    => 'required|regex:/^\d{4}-\d{2}$/',
            'jenis_pajak'=> 'required|in:ppn,pph21,pph23,pph_final_4_2',
            'jumlah'     => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Auto-isi KAP, KJS, uraian dari service
        $kapKjs = TaxCalculatorService::getKapKjs($data['jenis_pajak']);
        $jatuhTempo = TaxCalculatorService::getJatuhTempo($data['periode'], $data['jenis_pajak']);

        TaxSetoran::create([
            'periode'     => $data['periode'],
            'jenis_pajak' => $data['jenis_pajak'],
            'kap'         => $kapKjs['kap'],
            'kjs'         => $kapKjs['kjs'],
            'uraian'      => $kapKjs['uraian'],
            'jumlah'      => $data['jumlah'],
            'jatuh_tempo' => $jatuhTempo->toDateString(),
            'status'      => 'belum_bayar',
            'keterangan'  => $data['keterangan'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        return redirect()
            ->route('tax.setoran.index', ['periode' => $data['periode']])
            ->with('success', 'Catatan setoran berhasil dibuat.');
    }

    /**
     * Konfirmasi sudah bayar — NTPN + tanggal
     */
    public function konfirmBayar(TaxSetoran $setoran, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal_setor' => 'required|date',
            'ntpn'          => 'nullable|string|max:30',
        ]);

        $setoran->update(array_merge($data, ['status' => 'sudah_bayar']));

        return back()->with('success', 'Setoran telah dikonfirmasi sebagai sudah dibayar.');
    }

    public function destroy(TaxSetoran $setoran): RedirectResponse
    {
        $periode = $setoran->periode;

        if ($setoran->status === 'sudah_bayar') {
            return back()->with('error', 'Setoran yang sudah dibayar tidak dapat dihapus.');
        }

        $setoran->delete();

        return redirect()
            ->route('tax.setoran.index', ['periode' => $periode])
            ->with('success', 'Catatan setoran berhasil dihapus.');
    }
}
