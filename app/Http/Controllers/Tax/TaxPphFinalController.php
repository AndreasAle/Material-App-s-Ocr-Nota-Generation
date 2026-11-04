<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\TaxPphFinalRecord;
use App\Models\TaxSetting;
use App\Services\Tax\TaxCalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxPphFinalController extends Controller
{
    public function index(Request $request): View
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        $status  = $request->get('status', '');

        $records = TaxPphFinalRecord::with(['invoice', 'project', 'creator'])
            ->when($periode, fn ($q) => $q->where('periode', $periode))
            ->when($status,  fn ($q) => $q->where('status', $status))
            ->orderByDesc('tanggal_bukti_potong')
            ->paginate(20)
            ->withQueryString();

        $sumPph    = TaxPphFinalRecord::where('periode', $periode)->sum('pph_dipotong');
        $countBelum = TaxPphFinalRecord::where('status', 'belum_terima_bukpot')->count();

        $periodeList = TaxPphFinalRecord::selectRaw('DISTINCT periode')->pluck('periode')
            ->sortDesc()->values();

        // Tarif sesuai setting
        $setting = TaxSetting::getSetting();
        $tarif   = TaxCalculatorService::getTarifPphFinal(
            $setting->jenis_usaha,
            $setting->kualifikasi_konstruksi
        );

        return view('tax.pph-final.index', compact(
            'records', 'periode', 'status',
            'sumPph', 'countBelum',
            'periodeList', 'setting', 'tarif',
        ));
    }

    public function create(Request $request): View
    {
        $setting  = TaxSetting::getSetting();
        $invoices = Invoice::with('project')->orderByDesc('invoice_date')->limit(100)->get();
        $projects = Project::where('is_active', true)->orderBy('name')->get();

        $prefill = null;
        if ($request->filled('from_invoice')) {
            $prefill = Invoice::with('project')->find($request->from_invoice);
        }

        // Tarif default berdasarkan setting
        $tarifDefault = TaxCalculatorService::getTarifPphFinal(
            $setting->jenis_usaha,
            $setting->kualifikasi_konstruksi
        );

        return view('tax.pph-final.create', compact('setting', 'invoices', 'projects', 'prefill', 'tarifDefault'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'invoice_id'           => 'nullable|exists:invoices,id',
            'project_id'           => 'nullable|exists:projects,id',
            'nama_pemotong'        => 'required|string|max:255',
            'npwp_pemotong'        => 'nullable|string|max:30',
            'no_bukti_potong'      => 'required|string|max:50',
            'tanggal_bukti_potong' => 'required|date',
            'jenis_usaha'          => 'required|in:pelaksana,perencana,pengawas,konsultansi',
            'kualifikasi'          => 'required|in:kecil,menengah,besar,tidak_bersertifikat',
            'nilai_kontrak'        => 'required|numeric|min:0',
            'tarif'                => 'required|numeric|min:0|max:100',
            'pph_dipotong'         => 'required|numeric|min:0',
            'periode'              => 'required|regex:/^\d{4}-\d{2}$/',
            'status'               => 'required|in:belum_terima_bukpot,sudah_terima_bukpot',
            'keterangan'           => 'nullable|string|max:500',
        ]);

        $data['created_by'] = auth()->id();

        TaxPphFinalRecord::create($data);

        return redirect()
            ->route('tax.pph-final.index', ['periode' => $data['periode']])
            ->with('success', 'Bukti Potong PPh Final berhasil dicatat.');
    }

    public function show(TaxPphFinalRecord $record): View
    {
        $record->load(['invoice', 'project', 'creator']);

        $kapKjs = \App\Services\Tax\TaxCalculatorService::getKapKjs('pph_final_4_2');
        $jatuhTempo = \App\Services\Tax\TaxCalculatorService::getJatuhTempo($record->periode, 'pph_final_4_2');

        return view('tax.pph-final.show', compact('record', 'kapKjs', 'jatuhTempo'));
    }

    public function konfirmBukpot(TaxPphFinalRecord $record): RedirectResponse
    {
        $record->update(['status' => 'sudah_terima_bukpot']);

        return back()->with('success', 'Status Bukti Potong diperbarui menjadi Sudah Diterima.');
    }

    public function destroy(TaxPphFinalRecord $record): RedirectResponse
    {
        $periode = $record->periode;
        $record->delete();

        return redirect()
            ->route('tax.pph-final.index', ['periode' => $periode])
            ->with('success', 'Catatan PPh Final berhasil dihapus.');
    }
}
