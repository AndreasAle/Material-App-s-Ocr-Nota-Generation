<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\PurchaseTransaction;
use App\Models\Sale;
use App\Services\Store\PurchasePriceAlertService;
use App\Services\Store\StoreReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreReportController extends Controller
{
    public function __construct(
        protected StoreReportService $reports,
    ) {}

    /** Ringkasan hari ini untuk pemilik (dashboard toko). */
    public function dashboard(): View
    {
        $today = now()->toDateString();
        $summary = $this->reports->summary($today, $today);
        $month = $this->reports->summary(now()->startOfMonth()->toDateString(), $today);
        $top = $this->reports->topProducts(now()->startOfMonth()->toDateString(), $today, 5);
        $restock = $this->reports->restockSuggestions()->take(8);
        $overdueBon = Sale::with('customer')->where('remaining_amount', '>', 0)
            ->whereNotNull('due_date')->whereDate('due_date', '<', $today)
            ->orderBy('due_date')->limit(8)->get();
        $payablesDue = PurchaseTransaction::with('supplier')->notRejected()
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')->whereDate('due_date', '<=', now()->addDays(7))
            ->orderBy('due_date')->limit(8)->get();
        $recentSales = Sale::with('customer')->latest('sale_date')->limit(5)->get();

        $guide = app(\App\Services\Store\StoreGuideService::class);
        $setupSteps = $guide->setupSteps(auth()->user());
        $tasks = $guide->tasks();

        return view('store.dashboard', compact('summary', 'month', 'top', 'restock', 'overdueBon', 'payablesDue', 'recentSales', 'setupSteps', 'tasks'));
    }

    /** Rekap harian / bulanan dengan rentang tanggal bebas. */
    public function recap(Request $request): View
    {
        [$from, $to, $period] = $this->range($request);
        $summary = $this->reports->summary($from, $to);
        $daily = $this->reports->daily($from, $to);
        $top = $this->reports->topProducts($from, $to, 10);

        return view('store.reports.recap', compact('summary', 'daily', 'top', 'from', 'to', 'period'));
    }

    public function recapPdf(Request $request)
    {
        [$from, $to] = $this->range($request);
        $summary = $this->reports->summary($from, $to);
        $daily = $this->reports->daily($from, $to);
        $top = $this->reports->topProducts($from, $to, 10);

        return Pdf::loadView('store.reports.recap-pdf', compact('summary', 'daily', 'top', 'from', 'to'))
            ->setPaper('a4')
            ->download("rekap-toko-{$from}-{$to}.pdf");
    }

    public function topProducts(Request $request): View
    {
        [$from, $to, $period] = $this->range($request);
        $top = $this->reports->topProducts($from, $to, 50);

        return view('store.reports.top-products', compact('top', 'from', 'to', 'period'));
    }

    public function restock(): View
    {
        $rows = $this->reports->restockSuggestions();

        return view('store.reports.restock', compact('rows'));
    }

    public function applySuggestedPrices(Request $request, PurchasePriceAlertService $alerts): RedirectResponse
    {
        $ids = $request->validate(['material_ids' => ['required', 'array'], 'material_ids.*' => ['integer']])['material_ids'];
        $count = $alerts->applySuggested($ids);

        return back()->with('success', "Harga jual {$count} barang diperbarui sesuai saran.");
    }

    /** @return array{0:string,1:string,2:string} */
    private function range(Request $request): array
    {
        $period = $request->input('period', 'month');
        [$from, $to] = match ($period) {
            'today'      => [now()->toDateString(), now()->toDateString()],
            'yesterday'  => [now()->subDay()->toDateString(), now()->subDay()->toDateString()],
            'week'       => [now()->startOfWeek()->toDateString(), now()->toDateString()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            'custom'     => [$request->input('from', now()->toDateString()), $request->input('to', now()->toDateString())],
            default      => [now()->startOfMonth()->toDateString(), now()->toDateString()],
        };

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to, $period];
    }
}
