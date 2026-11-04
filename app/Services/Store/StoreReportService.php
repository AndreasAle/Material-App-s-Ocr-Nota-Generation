<?php

namespace App\Services\Store;

use App\Models\Material;
use App\Models\PurchaseTransaction;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\SaleReturn;
use App\Models\SupplierPayment;
use App\Models\SupplierPriceHistory;
use App\Services\Stock\StockMovementService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Rekap toko: omzet, laba kotor, uang masuk/keluar, terlaris, stok menipis & saran kulakan.
 */
class StoreReportService
{
    public function __construct(
        protected StockMovementService $stock,
    ) {}

    public function summary(string $from, string $to): array
    {
        $sales = Sale::whereDate('sale_date', '>=', $from)->whereDate('sale_date', '<=', $to);
        $returns = SaleReturn::whereDate('return_date', '>=', $from)->whereDate('return_date', '<=', $to);

        $gross = (float) (clone $sales)->sum('grand_total');
        $returnTotal = (float) (clone $returns)->sum('total');
        $omzet = $gross - $returnTotal;
        $hpp = (float) (clone $sales)->sum('cost_total') - (float) (clone $returns)->sum('cost_total');

        $cashIn = (float) SalePayment::whereDate('payment_date', '>=', $from)->whereDate('payment_date', '<=', $to)->sum('amount');
        $supplierPaid = (float) SupplierPayment::whereDate('payment_date', '>=', $from)->whereDate('payment_date', '<=', $to)->sum('amount');
        $refunds = (float) (clone $returns)->sum('refund_amount');

        return [
            'transactions'    => (clone $sales)->count(),
            'gross_sales'     => $gross,
            'returns'         => $returnTotal,
            'omzet'           => $omzet,
            'hpp'             => $hpp,
            'gross_profit'    => $omzet - $hpp,
            'margin_pct'      => $omzet > 0 ? round(($omzet - $hpp) / $omzet * 100, 1) : 0,
            'cash_in'         => $cashIn,
            'cash_out'        => $supplierPaid + $refunds,
            'supplier_paid'   => $supplierPaid,
            'refunds'         => $refunds,
            'net_cash'        => $cashIn - $supplierPaid - $refunds,
            'new_credit'      => (float) (clone $sales)->sum('remaining_amount'),
            'purchases'       => (float) PurchaseTransaction::notRejected()
                ->whereDate('transaction_date', '>=', $from)->whereDate('transaction_date', '<=', $to)->sum('grand_total'),
            'receivable_total' => (float) Sale::where('remaining_amount', '>', 0)->sum('remaining_amount'),
            'payable_total'    => (float) PurchaseTransaction::notRejected()->whereIn('payment_status', ['unpaid', 'partial'])->sum('remaining_amount'),
        ];
    }

    /**
     * Rincian per hari dalam rentang tanggal.
     */
    public function daily(string $from, string $to): Collection
    {
        $salesByDay = Sale::whereDate('sale_date', '>=', $from)->whereDate('sale_date', '<=', $to)
            ->get(['sale_date', 'grand_total', 'cost_total'])
            ->groupBy(fn ($s) => $s->sale_date->toDateString());
        $returnsByDay = SaleReturn::whereDate('return_date', '>=', $from)->whereDate('return_date', '<=', $to)
            ->get(['return_date', 'total', 'cost_total', 'refund_amount'])
            ->groupBy(fn ($r) => $r->return_date->toDateString());
        $paymentsByDay = SalePayment::whereDate('payment_date', '>=', $from)->whereDate('payment_date', '<=', $to)
            ->get(['payment_date', 'amount'])
            ->groupBy(fn ($p) => $p->payment_date->toDateString());
        $supplierByDay = SupplierPayment::whereDate('payment_date', '>=', $from)->whereDate('payment_date', '<=', $to)
            ->get(['payment_date', 'amount'])
            ->groupBy(fn ($p) => Carbon::parse($p->payment_date)->toDateString());

        $rows = collect();
        for ($d = Carbon::parse($from); $d->lte(Carbon::parse($to)); $d->addDay()) {
            $key = $d->toDateString();
            $s = $salesByDay->get($key, collect());
            $r = $returnsByDay->get($key, collect());
            $omzet = (float) $s->sum('grand_total') - (float) $r->sum('total');
            $hpp = (float) $s->sum('cost_total') - (float) $r->sum('cost_total');

            $rows->push([
                'date'      => $key,
                'count'     => $s->count(),
                'omzet'     => $omzet,
                'profit'    => $omzet - $hpp,
                'cash_in'   => (float) $paymentsByDay->get($key, collect())->sum('amount'),
                'cash_out'  => (float) $supplierByDay->get($key, collect())->sum('amount') + (float) $r->sum('refund_amount'),
            ]);
        }

        return $rows;
    }

    /**
     * Barang terlaris berdasarkan qty bersih (setelah retur) dalam satuan dasar.
     */
    public function topProducts(string $from, string $to, int $limit = 10): Collection
    {
        return SaleItem::query()
            ->whereHas('sale', fn ($q) => $q->whereDate('sale_date', '>=', $from)->whereDate('sale_date', '<=', $to))
            ->whereNotNull('material_id')
            ->get()
            ->groupBy('material_id')
            ->map(function ($items) {
                $first = $items->first();
                $qtyBase = $items->sum(fn ($i) => ((float) $i->qty - (float) $i->qty_returned) * (float) $i->factor);
                $omzet = $items->sum(fn ($i) => ((float) $i->qty - (float) $i->qty_returned) * (float) $i->price);
                $cost = $items->sum(fn ($i) => ((float) $i->qty - (float) $i->qty_returned) * (float) $i->factor * (float) $i->cost_price);

                return [
                    'material_id' => $first->material_id,
                    'name'        => $first->material_name_snapshot,
                    'qty_base'    => round($qtyBase, 2),
                    'omzet'       => round($omzet, 2),
                    'profit'      => round($omzet - $cost, 2),
                ];
            })
            ->sortByDesc('qty_base')
            ->take($limit)
            ->values();
    }

    /**
     * Barang yang perlu dibeli lagi: stok ≤ minimum (atau habis tapi laku 30 hari terakhir),
     * lengkap dengan supplier termurah dari riwayat harga.
     */
    public function restockSuggestions(): Collection
    {
        $stockMap = $this->stock->currentStockAll();
        $soldRecently = SaleItem::whereHas('sale', fn ($q) => $q->where('sale_date', '>=', now()->subDays(30)))
            ->selectRaw('material_id, SUM(qty_base) as qty')
            ->groupBy('material_id')
            ->pluck('qty', 'material_id');

        return Material::with('unit')
            ->where('is_active', true)
            ->get()
            ->map(function (Material $m) use ($stockMap, $soldRecently) {
                $stock = (float) ($stockMap[$m->id] ?? 0);
                $min = $m->minimum_stock !== null ? (float) $m->minimum_stock : null;
                $sold30 = (float) ($soldRecently[$m->id] ?? 0);

                $needs = ($min !== null && $min > 0 && $stock <= $min) || ($stock <= 0 && $sold30 > 0);
                if (! $needs) {
                    return null;
                }

                $cheapest = SupplierPriceHistory::with('supplier')
                    ->where('material_id', $m->id)
                    ->where('recorded_at', '>=', now()->subMonths(6)->startOfMonth())
                    ->orderBy('price')
                    ->first();

                // Saran qty: cukup untuk 30 hari penjualan, minimal sampai 2× stok minimum
                $target = max($sold30, ($min ?? 0) * 2);

                return [
                    'material'      => $m,
                    'stock'         => $stock,
                    'minimum'       => $min,
                    'sold_30d'      => $sold30,
                    'suggested_qty' => max(0, round($target - $stock, 2)),
                    'supplier'      => $cheapest?->supplier?->name,
                    'supplier_phone' => $cheapest?->supplier?->phone,
                    'price'         => $cheapest ? (float) $cheapest->price : null,
                    'price_unit'    => $cheapest?->unit_name_snapshot,
                ];
            })
            ->filter()
            ->sortBy('stock')
            ->values();
    }
}
