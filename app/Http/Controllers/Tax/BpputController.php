<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\TaxImportBatch;
use App\Models\TaxPphFinalRecord;
use App\Models\TaxPph23Record;
use App\Models\TaxSetting;
use App\Services\Tax\BpputService;
use App\Services\Tax\TaxKodeObjekPajakReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BpputController extends Controller
{
    public function __construct(private BpputService $service) {}

    // ─────────────────────────────────────────────────────────────────────────
    //  INDEX — Dashboard BPPU dengan rekap SPT Masa
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tahun     = (int) $request->input('tahun', date('Y'));
        $masaFilter = $request->input('masa');
        $jenis     = $request->input('jenis', 'all'); // all | final | pph23

        // Rekap per masa untuk tracker SPT
        $rekapMasa = BpputService::rekapPerMasa($tahun);

        // Records untuk tabel detail
        $query = collect();
        if (in_array($jenis, ['all', 'final'])) {
            $q = TaxPphFinalRecord::with(['invoice.project', 'importBatch']);
            if ($masaFilter) $q->where('periode', $masaFilter);
            else $q->where('periode', 'like', $tahun . '-%');
            $final = $q->latest()->get()->map(fn($r) => (object) array_merge((array) $r->toArray(), [
                '_type' => 'final', '_model' => $r,
                '_dpp' => $r->nilai_kontrak, '_tarif_label' => 'PPh Final 4(2)',
            ]));
            $query = $query->concat($final);
        }

        if (in_array($jenis, ['all', 'pph23'])) {
            $q = TaxPph23Record::with(['purchaseTransaction', 'importBatch']);
            if ($masaFilter) $q->where('periode', $masaFilter);
            else $q->where('periode', 'like', $tahun . '-%');
            $pph23 = $q->latest()->get()->map(fn($r) => (object) array_merge((array) $r->toArray(), [
                '_type' => 'pph23', '_model' => $r,
                '_dpp' => $r->bruto, '_tarif_label' => 'PPh 23',
            ]));
            $query = $query->concat($pph23);
        }

        // Anomali PPh untuk masa filter aktif
        $anomali = [];
        if ($masaFilter) {
            $anomali = BpputService::cariAnomali($masaFilter);
        }

        // Import batches terbaru
        $batches = TaxImportBatch::where('jenis', 'bppu')
                    ->where('created_by', Auth::id())
                    ->latest()->take(10)->get();

        // Summary totals
        $totalFinal = TaxPphFinalRecord::when($masaFilter, fn($q) => $q->where('periode', $masaFilter))
                        ->when(!$masaFilter, fn($q) => $q->where('periode', 'like', $tahun . '-%'))
                        ->sum('pph_dipotong');
        $totalPph23 = TaxPph23Record::when($masaFilter, fn($q) => $q->where('periode', $masaFilter))
                        ->when(!$masaFilter, fn($q) => $q->where('periode', 'like', $tahun . '-%'))
                        ->sum('pph_dipotong');

        return view('tax.bppu.index', compact(
            'tahun', 'masaFilter', 'jenis', 'rekapMasa',
            'query', 'anomali', 'batches', 'totalFinal', 'totalPph23'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  IMPORT — Form upload & preview
    // ─────────────────────────────────────────────────────────────────────────

    public function importForm()
    {
        return view('tax.bppu.import');
    }

    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'File Excel BPPU wajib diupload.',
            'file.mimes'    => 'File harus berformat Excel (.xlsx atau .xls).',
            'file.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        $file     = $request->file('file');
        $path     = $file->store('tax-imports', 'local');
        $fullPath = storage_path('app/' . $path);

        try {
            $batch = $this->service->readForPreview($fullPath, $file->getClientOriginalName());
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        return redirect()->route('tax.bppu.preview', $batch->id)
                         ->with('success', 'File berhasil dibaca. Silakan review sebelum menyimpan.');
    }

    public function importPreview(TaxImportBatch $batch)
    {
        if ($batch->jenis !== 'bppu') abort(404);
        $batch->load('creator');
        $rows = $batch->preview_data ?? [];

        return view('tax.bppu.import-preview', compact('batch', 'rows'));
    }

    public function importCommit(TaxImportBatch $batch)
    {
        if ($batch->jenis !== 'bppu') abort(404);

        $result = $this->service->commitBatch($batch);

        if ($result['success']) {
            return redirect()->route('tax.bppu.index')
                             ->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EXPORT — Generate Excel DJP dari data internal
    // ─────────────────────────────────────────────────────────────────────────

    public function exportForm()
    {
        $setting = TaxSetting::first();
        $tahun   = date('Y');
        $masas   = [];
        for ($m = 1; $m <= 12; $m++) {
            $masa = $tahun . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            $finalCount = TaxPphFinalRecord::where('periode', $masa)->count();
            $pph23Count = TaxPph23Record::where('periode', $masa)->count();
            if ($finalCount + $pph23Count > 0) {
                $masas[] = ['masa' => $masa, 'final' => $finalCount, 'pph23' => $pph23Count];
            }
        }

        return view('tax.bppu.export', compact('setting', 'masas', 'tahun'));
    }

    public function exportDownload(Request $request)
    {
        $request->validate([
            'masa_pajak'    => 'required|string',
            'jenis'         => 'required|in:final,pph23,all',
            'npwp_pemotong' => 'required|string',
            'nama_pemotong' => 'required|string',
        ]);

        $spreadsheet = $this->service->generateExportBppu(
            $request->masa_pajak,
            $request->jenis,
            $request->npwp_pemotong,
            $request->nama_pemotong
        );

        $filename = 'BPPU_' . str_replace('-', '', $request->masa_pajak) . '_' . now()->format('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Show single batch detail
    // ─────────────────────────────────────────────────────────────────────────

    public function showBatch(TaxImportBatch $batch)
    {
        if ($batch->jenis !== 'bppu') abort(404);
        $batch->load('pphFinalRecords', 'pph23Records', 'creator');
        return view('tax.bppu.batch-detail', compact('batch'));
    }
}
