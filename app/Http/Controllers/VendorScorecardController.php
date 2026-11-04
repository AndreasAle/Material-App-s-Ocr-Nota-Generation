<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\VendorScore;
use App\Services\VendorScorecardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorScorecardController extends Controller
{
    public function __construct(
        protected VendorScorecardService $service,
    ) {}

    /**
     * Halaman scorecard satu supplier + chart 12 bulan
     */
    public function show(Supplier $supplier): View
    {
        $supplier->load('priceHistories');

        $history = $this->service->getHistory($supplier->id, 12);

        // Data untuk chart
        $chartLabels  = $history->map(fn ($s) => $s->month . '/' . $s->year)->toArray();
        $chartOverall = $history->map(fn ($s) => (float) $s->overall_score)->toArray();
        $chartDelivery= $history->map(fn ($s) => (float) $s->delivery_score)->toArray();
        $chartQuality = $history->map(fn ($s) => (float) $s->quality_score)->toArray();

        // Bulan ini
        $currentScore = VendorScore::where('supplier_id', $supplier->id)
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->first();

        // Rata-rata 12 bulan
        $avgScore = $history->avg('overall_score');

        return view('suppliers.scorecard', compact(
            'supplier', 'history', 'currentScore', 'avgScore',
            'chartLabels', 'chartOverall', 'chartDelivery', 'chartQuality'
        ));
    }

    /**
     * Recalculate skor bulan ini untuk supplier tertentu
     */
    public function recalculate(Supplier $supplier): RedirectResponse
    {
        $this->service->computeForMonth($supplier->id, now()->year, now()->month);
        return back()->with('success', "Skor vendor {$supplier->name} berhasil diperbarui.");
    }

    /**
     * Leaderboard semua supplier
     */
    public function leaderboard(Request $request): View
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $scores = VendorScore::with('supplier')
            ->where('year', $year)
            ->where('month', $month)
            ->orderByDesc('overall_score')
            ->paginate(20);

        return view('suppliers.leaderboard', compact('scores', 'year', 'month'));
    }

    /**
     * Batch recalc semua supplier (bisa dipanggil dari scheduler)
     */
    public function recalculateAll(): RedirectResponse
    {
        $count = $this->service->computeAllForCurrentMonth();
        return back()->with('success', "Skor {$count} vendor berhasil diperbarui.");
    }
}
