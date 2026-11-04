<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\VendorScore;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VendorScorecardService
{
    /**
     * Hitung & simpan skor vendor untuk bulan tertentu.
     */
    public function computeForMonth(int $supplierId, int $year, int $month): VendorScore
    {
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to   = $from->copy()->endOfMonth();

        // ── GR dalam periode ─────────────────────────────────────────────
        $grs = GoodsReceipt::with(['items', 'purchaseOrder'])
            ->whereBetween('receipt_date', [$from, $to])
            ->whereHas('purchaseTransaction', fn ($q) => $q->where('supplier_id', $supplierId))
            ->orWhere(function ($q) use ($supplierId, $from, $to) {
                $q->whereBetween('receipt_date', [$from, $to])
                  ->whereHas('purchaseOrder', fn ($q2) => $q2->where('supplier_id', $supplierId));
            })
            ->get();

        $totalOrders    = $grs->count();
        $onTime         = 0;
        $late           = 0;
        $partial        = 0;
        $qtyOrdered     = 0;
        $qtyReceived    = 0;

        foreach ($grs as $gr) {
            $expectedDate = $gr->purchaseOrder?->expected_date;
            if ($expectedDate) {
                if ($gr->receipt_date->lte($expectedDate)) $onTime++;
                else $late++;
            }
            if ($gr->isPartial()) $partial++;

            foreach ($gr->items as $item) {
                $qtyOrdered  += (float) $item->qty_ordered;
                $qtyReceived += (float) $item->qty_received;
            }
        }

        // ── Returns dalam periode ────────────────────────────────────────
        $returns = PurchaseReturn::where('status', 'approved')
            ->whereBetween('return_date', [$from, $to])
            ->whereHas('purchaseTransaction', fn ($q) => $q->where('supplier_id', $supplierId))
            ->get();

        $qtyReturned = (float) $returns->sum(fn ($r) => $r->items->sum('qty_returned'));

        // ── Score calculation ────────────────────────────────────────────
        $deliveryScore = $totalOrders > 0 && ($onTime + $late) > 0
            ? ($onTime / ($onTime + $late)) * 100
            : 100.0;   // Kalau tidak ada data expected_date, asumsi 100%

        $quantityScore = $qtyOrdered > 0
            ? min(100, ($qtyReceived / $qtyOrdered) * 100)
            : 100.0;

        $qualityScore = $qtyReceived > 0
            ? max(0, 100 - ($qtyReturned / $qtyReceived) * 100)
            : 100.0;

        // Price score: bandingkan harga rata-rata supplier ini vs rata-rata semua supplier bulan ini
        // Sementara set 80 sebagai default jika belum ada data pembanding
        $priceScore = $this->computePriceScore($supplierId, $from, $to);

        // Weighted overall: delivery 30%, qty 25%, quality 30%, price 15%
        $overallScore = (
            $deliveryScore  * 0.30 +
            $quantityScore  * 0.25 +
            $qualityScore   * 0.30 +
            $priceScore     * 0.15
        );

        return VendorScore::updateOrCreate(
            ['supplier_id' => $supplierId, 'year' => $year, 'month' => $month],
            [
                'total_orders'       => $totalOrders,
                'on_time_deliveries' => $onTime,
                'late_deliveries'    => $late,
                'partial_deliveries' => $partial,
                'total_qty_ordered'  => $qtyOrdered,
                'total_qty_received' => $qtyReceived,
                'total_qty_returned' => $qtyReturned,
                'delivery_score'     => round($deliveryScore, 2),
                'quantity_score'     => round($quantityScore, 2),
                'quality_score'      => round($qualityScore, 2),
                'price_score'        => round($priceScore, 2),
                'overall_score'      => round($overallScore, 2),
                'grade'              => VendorScore::computeGrade($overallScore),
            ]
        );
    }

    /**
     * Batch compute semua supplier aktif untuk bulan sekarang.
     */
    public function computeAllForCurrentMonth(): int
    {
        $year  = now()->year;
        $month = now()->month;
        $count = 0;

        Supplier::where('is_active', true)->each(function (Supplier $s) use ($year, $month, &$count) {
            $this->computeForMonth($s->id, $year, $month);
            $count++;
        });

        return $count;
    }

    /**
     * Ambil skor 12 bulan terakhir untuk satu supplier.
     */
    public function getHistory(int $supplierId, int $months = 12): \Illuminate\Database\Eloquent\Collection
    {
        return VendorScore::where('supplier_id', $supplierId)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit($months)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Bandingkan harga vs rata-rata pasar — sederhana untuk sekarang.
     */
    private function computePriceScore(int $supplierId, Carbon $from, Carbon $to): float
    {
        // Ambil rata-rata harga per material dari supplier ini
        $avgThisSupplier = DB::table('purchase_transaction_items')
            ->join('purchase_transactions', 'purchase_transaction_items.purchase_transaction_id', '=', 'purchase_transactions.id')
            ->where('purchase_transactions.supplier_id', $supplierId)
            ->whereBetween('purchase_transactions.transaction_date', [$from, $to])
            ->whereNotNull('purchase_transaction_items.material_id')
            ->avg('purchase_transaction_items.price');

        // Ambil rata-rata harga semua supplier untuk material yang sama
        $avgAllSuppliers = DB::table('purchase_transaction_items')
            ->join('purchase_transactions', 'purchase_transaction_items.purchase_transaction_id', '=', 'purchase_transactions.id')
            ->whereBetween('purchase_transactions.transaction_date', [$from, $to])
            ->whereNotNull('purchase_transaction_items.material_id')
            ->avg('purchase_transaction_items.price');

        if (!$avgAllSuppliers || !$avgThisSupplier) return 80.0;

        // Skor: jika harga supplier lebih rendah dari rata-rata → skor lebih tinggi
        $ratio = $avgAllSuppliers / $avgThisSupplier;
        return min(100, max(0, $ratio * 80));
    }
}
