<?php

namespace App\Http\Controllers;

use App\Models\PurchaseScanBatch;
use App\Models\PurchaseScanDraft;
use App\Models\PurchaseTransaction;
use App\Models\ScanCorrection;
use App\Models\User;
use App\Services\Purchases\Ocr\ScanCorrectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScannerReportController extends Controller
{
    public function __construct(
        protected ScanCorrectionService $correctionService,
    ) {}

    public function index(Request $request): View
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $transactionBase = PurchaseTransaction::query()
            ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $endDate));

        $singleDraftBase = PurchaseScanDraft::query()
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate));

        $batchBase = PurchaseScanBatch::query()
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate));

        $summary = [
            'manual_transactions' => (clone $transactionBase)->where('input_source', 'manual')->count(),
            'scanner_transactions' => (clone $transactionBase)->where('input_source', 'scanner')->count(),
            'single_scan_transactions' => (clone $transactionBase)->where('scan_source_type', 'single')->count(),
            'multiple_scan_transactions' => (clone $transactionBase)->where('scan_source_type', 'multiple')->count(),

            'single_draft_total' => (clone $singleDraftBase)->count(),
            'single_draft_processed' => (clone $singleDraftBase)->where('status', 'processed')->count(),
            'single_draft_reviewed' => (clone $singleDraftBase)->where('status', 'reviewed')->count(),
            'single_draft_imported' => (clone $singleDraftBase)->where('status', 'imported')->count(),
            'single_draft_failed' => (clone $singleDraftBase)->where('status', 'failed')->count(),

            'batch_total' => (clone $batchBase)->count(),
            'batch_processed' => (clone $batchBase)->where('status', 'processed')->count(),
            'batch_reviewed' => (clone $batchBase)->where('status', 'reviewed')->count(),
            'batch_imported' => (clone $batchBase)->where('status', 'imported')->count(),
            'batch_supplier_mismatch' => (clone $batchBase)->where('status', 'supplier_mismatch')->count(),
            'batch_failed' => (clone $batchBase)->where('status', 'failed')->count(),
        ];

        $ocrBaseCount = $summary['single_draft_total'] + $summary['batch_total'];
        $ocrSuccessCount = $summary['single_draft_processed'] + $summary['single_draft_reviewed'] + $summary['single_draft_imported']
            + $summary['batch_processed'] + $summary['batch_reviewed'] + $summary['batch_imported'];
        $ocrSuccessRate = $ocrBaseCount > 0 ? round(($ocrSuccessCount / $ocrBaseCount) * 100, 1) : 0;

        $importBaseCount = $summary['single_draft_total'] + $summary['batch_total'];
        $importedCount = $summary['single_draft_imported'] + $summary['batch_imported'];
        $importSuccessRate = $importBaseCount > 0 ? round(($importedCount / $importBaseCount) * 100, 1) : 0;

        $topMismatchBatches = PurchaseScanBatch::query()
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('status', 'supplier_mismatch')
            ->latest()
            ->limit(5)
            ->get();

        $scanModeBreakdown = [
            'manual' => $summary['manual_transactions'],
            'scanner' => $summary['scanner_transactions'],
            'single' => $summary['single_scan_transactions'],
            'multiple' => $summary['multiple_scan_transactions'],
        ];

        // Statistik koreksi (feedback loop)
        $correctionStats = [
            'total' => ScanCorrection::count(),
            'total_usages' => (int) ScanCorrection::sum('usage_count'),
            'top_corrections' => ScanCorrection::with('corrector:id,name')
                ->orderByDesc('usage_count')
                ->limit(10)
                ->get(),
        ];

        // Per-user scanner activity
        $userActivity = PurchaseScanDraft::query()
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->select('created_by', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN status = "imported" THEN 1 ELSE 0 END) as imported'))
            ->with('creator:id,name')
            ->groupBy('created_by')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Statistik duplikat terdeteksi
        $duplicateCount = PurchaseScanDraft::query()
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->whereNotNull('content_hash')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('purchase_scan_drafts as psd2')
                    ->whereColumn('psd2.content_hash', 'purchase_scan_drafts.content_hash')
                    ->whereColumn('psd2.created_by', 'purchase_scan_drafts.created_by')
                    ->whereColumn('psd2.id', '!=', 'purchase_scan_drafts.id');
            })
            ->count();

        return view('reports.scanner.index', compact(
            'summary',
            'ocrSuccessRate',
            'importSuccessRate',
            'topMismatchBatches',
            'scanModeBreakdown',
            'startDate',
            'endDate',
            'correctionStats',
            'userActivity',
            'duplicateCount',
        ));
    }
}
