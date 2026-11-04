<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\TaxBp21Record;
use App\Models\TaxImportBatch;
use App\Models\TaxSetting;
use App\Models\Invoice;
use App\Models\Project;
use App\Services\Tax\TaxBp21Service;
use App\Services\Tax\TaxKodeObjekPajakReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TaxBp21Controller extends Controller
{
    public function __construct(private TaxBp21Service $service) {}

    // ─────────────────────────────────────────────────────────────────────────
    //  INDEX
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $masaFilter = $request->input('masa', now()->format('Y-m'));
        $tahun      = (int) substr($masaFilter, 0, 4);

        $records = TaxBp21Record::where('masa_pajak', $masaFilter)
                    ->with(['project', 'importBatch'])
                    ->latest()->paginate(25);

        $rekapMasa = TaxBp21Service::rekapPerMasa($tahun);
        $totalPph  = TaxBp21Record::where('masa_pajak', $masaFilter)->sum('pph_dipotong');
        $belumSetor = TaxBp21Record::where('masa_pajak', $masaFilter)->where('status', 'belum_disetor')->sum('pph_dipotong');

        return view('tax.bp21.index', compact('masaFilter', 'tahun', 'records', 'rekapMasa', 'totalPph', 'belumSetor'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CREATE (manual entry)
    // ─────────────────────────────────────────────────────────────────────────

    public function create()
    {
        $kopOptions = TaxKodeObjekPajakReference::selectOptionsBp21();
        $projects   = Project::orderBy('name')->get(['id', 'name']);
        $invoices   = Invoice::orderBy('invoice_date', 'desc')->take(50)->get(['id', 'invoice_no', 'invoice_date']);

        return view('tax.bp21.create', compact('kopOptions', 'projects', 'invoices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'masa_pajak'          => 'required|string|size:7',
            'npwp_penerima'       => 'nullable|string|max:20',
            'nik_penerima'        => 'nullable|string|max:16',
            'nama_penerima'       => 'required|string|max:255',
            'status_ptkp'         => 'nullable|string|max:10',
            'kode_objek_pajak'    => 'required|string|max:20',
            'penghasilan_bruto'   => 'required|numeric|min:0',
            'deemed_percent'      => 'nullable|numeric|min:0|max:100',
            'tarif'               => 'required|numeric|min:0|max:100',
            'pph_dipotong'        => 'required|numeric|min:0',
            'jenis_dok_referensi' => 'nullable|string|max:100',
            'no_dok_referensi'    => 'nullable|string|max:100',
            'tanggal_dok_referensi' => 'nullable|date',
            'tanggal_pemotongan'  => 'nullable|date',
            'project_id'          => 'nullable|exists:projects,id',
            'keterangan'          => 'nullable|string|max:500',
        ]);

        [$year, $month] = explode('-', $validated['masa_pajak']);
        $kop = TaxKodeObjekPajakReference::find($validated['kode_objek_pajak']);

        TaxBp21Record::create(array_merge($validated, [
            'tahun_pajak'      => (int) $year,
            'nama_objek_pajak' => TaxKodeObjekPajakReference::namaKop($validated['kode_objek_pajak']),
            'jenis_tarif'      => $kop['jenis_tarif'] ?? 'TER',
            'status'           => 'belum_disetor',
            'sumber'           => 'manual',
            'created_by'       => Auth::id(),
        ]));

        return redirect()->route('tax.bp21.index', ['masa' => $validated['masa_pajak']])
                         ->with('success', 'Bukti Potong PPh 21 berhasil disimpan.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  SHOW
    // ─────────────────────────────────────────────────────────────────────────

    public function show(TaxBp21Record $record)
    {
        $record->load(['project', 'invoice', 'importBatch', 'creator']);
        return view('tax.bp21.show', compact('record'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  IMPORT
    // ─────────────────────────────────────────────────────────────────────────

    public function importForm()
    {
        return view('tax.bp21.import');
    }

    public function importProcess(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:10240']);

        $file = $request->file('file');
        $path = $file->store('tax-imports', 'local');

        try {
            $batch = $this->service->readForPreview(
                storage_path('app/' . $path),
                $file->getClientOriginalName()
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        return redirect()->route('tax.bp21.preview', $batch->id)
                         ->with('success', 'File BP21 berhasil dibaca. Silakan review sebelum menyimpan.');
    }

    public function importPreview(TaxImportBatch $batch)
    {
        if ($batch->jenis !== 'bp21') abort(404);
        $rows = $batch->preview_data ?? [];
        return view('tax.bp21.import-preview', compact('batch', 'rows'));
    }

    public function importCommit(TaxImportBatch $batch)
    {
        if ($batch->jenis !== 'bp21') abort(404);

        $result = $this->service->commitBatch($batch);

        if ($result['success']) {
            return redirect()->route('tax.bp21.index')
                             ->with('success', $result['message']);
        }
        return back()->with('error', $result['message']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EXPORT
    // ─────────────────────────────────────────────────────────────────────────

    public function exportForm(Request $request)
    {
        $masaFilter = $request->input('masa', now()->format('Y-m'));
        $setting    = TaxSetting::first();
        $masas      = [];
        $tahun      = (int) substr($masaFilter, 0, 4);

        for ($m = 1; $m <= 12; $m++) {
            $masa = $tahun . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            $count = TaxBp21Record::where('masa_pajak', $masa)->count();
            if ($count > 0) $masas[] = ['masa' => $masa, 'count' => $count];
        }

        return view('tax.bp21.export', compact('setting', 'masas', 'masaFilter', 'tahun'));
    }

    public function exportDownload(Request $request)
    {
        $request->validate([
            'masa_pajak'    => 'required|string',
            'npwp_pemotong' => 'required|string',
            'nama_pemotong' => 'required|string',
        ]);

        $spreadsheet = $this->service->generateExport(
            $request->masa_pajak,
            $request->npwp_pemotong,
            $request->nama_pemotong
        );

        $filename = 'BP21_' . str_replace('-', '', $request->masa_pajak) . '_' . now()->format('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Konfirmasi Setor
    // ─────────────────────────────────────────────────────────────────────────

    public function konfirmSetor(Request $request, TaxBp21Record $record)
    {
        $request->validate([
            'tanggal_setor' => 'required|date',
            'ntpn'          => 'nullable|string|max:20',
        ]);

        $record->update([
            'status'        => 'sudah_disetor',
            'tanggal_setor' => $request->tanggal_setor,
            'ntpn'          => $request->ntpn,
        ]);

        return back()->with('success', 'Status PPh 21 diperbarui: sudah disetor.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EDIT / UPDATE
    // ─────────────────────────────────────────────────────────────────────────

    public function edit(TaxBp21Record $record)
    {
        $kopOptions = TaxKodeObjekPajakReference::selectOptionsBp21();
        $projects   = Project::orderBy('name')->get(['id', 'name']);
        $invoices   = Invoice::orderBy('invoice_date', 'desc')->take(50)->get(['id', 'invoice_no', 'invoice_date']);

        return view('tax.bp21.edit', compact('record', 'kopOptions', 'projects', 'invoices'));
    }

    public function update(Request $request, TaxBp21Record $record)
    {
        $validated = $request->validate([
            'masa_pajak'            => 'required|string|size:7',
            'npwp_penerima'         => 'nullable|string|max:20',
            'nik_penerima'          => 'nullable|string|max:16',
            'nama_penerima'         => 'required|string|max:255',
            'status_ptkp'           => 'nullable|string|max:10',
            'kode_objek_pajak'      => 'required|string|max:20',
            'penghasilan_bruto'     => 'required|numeric|min:0',
            'deemed_percent'        => 'nullable|numeric|min:0|max:100',
            'tarif'                 => 'required|numeric|min:0|max:100',
            'pph_dipotong'          => 'required|numeric|min:0',
            'jenis_dok_referensi'   => 'nullable|string|max:100',
            'no_dok_referensi'      => 'nullable|string|max:100',
            'tanggal_dok_referensi' => 'nullable|date',
            'tanggal_pemotongan'    => 'nullable|date',
            'project_id'            => 'nullable|exists:projects,id',
            'keterangan'            => 'nullable|string|max:500',
        ]);

        [$year] = explode('-', $validated['masa_pajak']);
        $kop = TaxKodeObjekPajakReference::find($validated['kode_objek_pajak']);

        $record->update(array_merge($validated, [
            'tahun_pajak'      => (int) $year,
            'nama_objek_pajak' => TaxKodeObjekPajakReference::namaKop($validated['kode_objek_pajak']),
            'jenis_tarif'      => $kop['jenis_tarif'] ?? $record->jenis_tarif,
        ]));

        return redirect()->route('tax.bp21.show', $record->id)
                         ->with('success', 'Bukti Potong PPh 21 berhasil diperbarui.');
    }

    public function destroy(TaxBp21Record $record)
    {
        $masa = $record->masa_pajak;
        $record->delete();
        return redirect()->route('tax.bp21.index', ['masa' => $masa])
                         ->with('success', 'Bukti potong PPh 21 dihapus.');
    }
}
