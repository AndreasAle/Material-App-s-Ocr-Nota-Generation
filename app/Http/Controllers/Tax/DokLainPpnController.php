<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\TaxImportBatch;
use App\Models\TaxPpnMasukan;
use App\Models\TaxPpnKeluaran;
use App\Models\TaxSetting;
use App\Services\Tax\DokLainPpnService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DokLainPpnController extends Controller
{
    public function __construct(private DokLainPpnService $service) {}

    // ─────────────────────────────────────────────────────────────────────────
    //  INDEX — Dashboard Dok Lain PPN
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tab       = $request->input('tab', 'masukan');
        $masaFilter = $request->input('masa', now()->format('Y-m'));

        $masukan = TaxPpnMasukan::where('periode', $masaFilter)
                    ->whereNotNull('kode_dokumen_lain')
                    ->orWhere(fn($q) => $q->where('periode', $masaFilter)->where('sumber', 'import_excel'))
                    ->orderBy('tanggal_faktur')
                    ->get();

        $keluaran = TaxPpnKeluaran::where('periode', $masaFilter)
                    ->whereNotNull('kode_dokumen_lain')
                    ->orWhere(fn($q) => $q->where('periode', $masaFilter)->where('sumber', 'import_excel'))
                    ->orderBy('tanggal_faktur')
                    ->get();

        $batches = TaxImportBatch::whereIn('jenis', ['dok_lain_masukan', 'dok_lain_keluaran'])
                    ->latest()->take(10)->get();

        $totalMasukan = $masukan->sum('ppn');
        $totalKeluaran = $keluaran->sum('ppn');

        $kodeTransaksiMasukan = DokLainPpnService::kodeTransaksiMasukan();
        $kodeTransaksiKeluaran = DokLainPpnService::kodeTransaksiKeluaran();
        $kodeDokumenLain = DokLainPpnService::kodeDokumenLain();

        return view('tax.dok-lain.index', compact(
            'tab', 'masaFilter', 'masukan', 'keluaran',
            'batches', 'totalMasukan', 'totalKeluaran',
            'kodeTransaksiMasukan', 'kodeTransaksiKeluaran', 'kodeDokumenLain'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  IMPORT Masukan
    // ─────────────────────────────────────────────────────────────────────────

    public function importMasukanForm()
    {
        return view('tax.dok-lain.import-masukan');
    }

    public function importMasukanProcess(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:10240']);

        $file = $request->file('file');
        $path = $file->store('tax-imports', 'local');

        try {
            $batch = $this->service->readMasukanForPreview(
                storage_path('app/' . $path),
                $file->getClientOriginalName()
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        return redirect()->route('tax.dok-lain.preview', $batch->id)
                         ->with('success', 'File berhasil dibaca. Silakan review.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  IMPORT Keluaran
    // ─────────────────────────────────────────────────────────────────────────

    public function importKeluaranForm()
    {
        return view('tax.dok-lain.import-keluaran');
    }

    public function importKeluaranProcess(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:10240']);

        $file = $request->file('file');
        $path = $file->store('tax-imports', 'local');

        try {
            $batch = $this->service->readKeluaranForPreview(
                storage_path('app/' . $path),
                $file->getClientOriginalName()
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        return redirect()->route('tax.dok-lain.preview', $batch->id)
                         ->with('success', 'File berhasil dibaca. Silakan review.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PREVIEW & COMMIT
    // ─────────────────────────────────────────────────────────────────────────

    public function preview(TaxImportBatch $batch)
    {
        if (!in_array($batch->jenis, ['dok_lain_masukan', 'dok_lain_keluaran'])) abort(404);
        $rows = $batch->preview_data ?? [];
        return view('tax.dok-lain.preview', compact('batch', 'rows'));
    }

    public function commit(TaxImportBatch $batch)
    {
        if (!in_array($batch->jenis, ['dok_lain_masukan', 'dok_lain_keluaran'])) abort(404);

        $result = match($batch->jenis) {
            'dok_lain_masukan'  => $this->service->commitMasukanBatch($batch),
            'dok_lain_keluaran' => $this->service->commitKeluaranBatch($batch),
        };

        if ($result['success']) {
            return redirect()->route('tax.dok-lain.index')
                             ->with('success', $result['message']);
        }
        return back()->with('error', $result['message']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EXPORT
    // ─────────────────────────────────────────────────────────────────────────

    public function exportMasukan(Request $request)
    {
        $request->validate(['masa_pajak' => 'required|string', 'npwp' => 'required', 'nama' => 'required']);
        $spreadsheet = $this->service->generateExportMasukan(
            $request->masa_pajak, $request->npwp, $request->nama
        );
        return $this->download($spreadsheet, 'DokLainMasukan_' . str_replace('-', '', $request->masa_pajak));
    }

    public function exportKeluaran(Request $request)
    {
        $request->validate(['masa_pajak' => 'required|string', 'npwp' => 'required', 'nama' => 'required']);
        $spreadsheet = $this->service->generateExportKeluaran(
            $request->masa_pajak, $request->npwp, $request->nama
        );
        return $this->download($spreadsheet, 'DokLainKeluaran_' . str_replace('-', '', $request->masa_pajak));
    }

    private function download($spreadsheet, string $basename): never
    {
        $filename = $basename . '_' . now()->format('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
}
