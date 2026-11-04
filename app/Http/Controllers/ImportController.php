<?php

namespace App\Http\Controllers;

use App\Imports\MaterialImport;
use App\Imports\PurchaseTransactionImport;
use App\Imports\RabPlanItemImport;
use App\Models\RabPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class ImportController extends Controller
{
    public function index(Request $request): View
    {
        $rabPlans = RabPlan::where('status', '!=', 'closed')
            ->with('project')
            ->orderBy('name')
            ->get();

        return view('import.index', compact('rabPlans'));
    }

    /**
     * Import daftar material dari Excel.
     */
    public function importMaterials(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new MaterialImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (ValidationException $e) {
            return back()->with('import_errors', collect($e->failures())
                ->map(fn ($f) => "Baris {$f->row()}: " . implode(', ', $f->errors()))
                ->toArray()
            )->with('import_type', 'materials');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }

        $msg = "Import Material selesai: {$import->imported} berhasil, {$import->skipped} dilewati.";

        return back()
            ->with('success', $msg)
            ->with('import_errors', $import->errors)
            ->with('import_type', 'materials');
    }

    /**
     * Import item RAB Plan dari Excel.
     */
    public function importRabItems(Request $request): RedirectResponse
    {
        $request->validate([
            'file'        => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'rab_plan_id' => 'required|exists:rab_plans,id',
        ]);

        $rabPlan = RabPlan::findOrFail($request->rab_plan_id);
        $import  = new RabPlanItemImport($rabPlan);

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }

        return back()
            ->with('success', "Import Item RAB selesai: {$import->imported} berhasil, {$import->skipped} dilewati.")
            ->with('import_errors', $import->errors)
            ->with('import_type', 'rab-items');
    }

    /**
     * Import transaksi pembelian dari Excel.
     */
    public function importTransactions(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new PurchaseTransactionImport(auth()->id());

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }

        return back()
            ->with('success', "Import Transaksi selesai: {$import->imported} transaksi berhasil, {$import->skipped} dilewati.")
            ->with('import_errors', $import->errors)
            ->with('import_type', 'transactions');
    }

    /**
     * Download template Excel untuk setiap tipe import.
     */
    public function downloadTemplate(string $type)
    {
        $templates = [
            'materials'    => 'templates/import_material_template.xlsx',
            'rab-items'    => 'templates/import_rab_items_template.xlsx',
            'transactions' => 'templates/import_transactions_template.xlsx',
        ];

        if (! array_key_exists($type, $templates)) {
            abort(404);
        }

        $path = public_path($templates[$type]);

        if (! file_exists($path)) {
            // Generate on-the-fly minimal CSV template
            return $this->generateCsvTemplate($type);
        }

        return response()->download($path);
    }

    private function generateCsvTemplate(string $type)
    {
        $headers = [
            'materials' => [
                'nama_material', 'satuan', 'kategori', 'harga_default', 'deskripsi',
            ],
            'rab-items' => [
                'nama_material', 'satuan', 'qty_rencana', 'harga_rencana', 'catatan',
            ],
            'transactions' => [
                'nomor_transaksi', 'tanggal', 'supplier', 'proyek', 'nama_material', 'satuan', 'qty', 'harga_satuan', 'catatan',
            ],
        ];

        $examples = [
            'materials' => [
                ['Bata Merah', 'pcs', 'Material Bangunan', '800', 'Bata merah standar'],
                ['Semen Portland', 'sak', 'Material Bangunan', '75000', ''],
            ],
            'rab-items' => [
                ['Bata Merah', 'pcs', '5000', '800', 'Untuk dinding utama'],
                ['Semen Portland', 'sak', '200', '75000', ''],
            ],
            'transactions' => [
                ['TRX-202506-0001', '2025-06-01', 'Toko Bangunan Jaya', 'Proyek A', 'Bata Merah', 'pcs', '1000', '800', ''],
                ['TRX-202506-0001', '2025-06-01', 'Toko Bangunan Jaya', 'Proyek A', 'Semen Portland', 'sak', '50', '75000', ''],
            ],
        ];

        $cols    = $headers[$type] ?? [];
        $rows    = $examples[$type] ?? [];
        $csvName = "template_import_{$type}.csv";

        $output = implode(',', $cols) . "\r\n";
        foreach ($rows as $row) {
            $output .= implode(',', $row) . "\r\n";
        }

        return response($output, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$csvName}\"",
        ]);
    }
}
