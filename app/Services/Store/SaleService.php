<?php

namespace App\Services\Store;

use App\Models\Customer;
use App\Models\Material;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Services\Stock\StockMovementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Penjualan toko: kasir, bon pelanggan, pembayaran bon, retur penjualan.
 * Semua gerakan stok memakai satuan dasar material.
 */
class SaleService
{
    public function __construct(
        protected StockMovementService $stock,
    ) {}

    /**
     * @param array{items: array<int, array{material_id:int, unit_name?:string, qty:float, price?:float}>,
     *              customer_id?:int|null, discount_amount?:float, payment_method:string,
     *              cash_received?:float|null, down_payment?:float|null, notes?:string|null} $data
     */
    public function store(array $data, int $userId): Sale
    {
        return DB::transaction(function () use ($data, $userId) {
            $method = $data['payment_method'];
            $customer = ! empty($data['customer_id']) ? Customer::find($data['customer_id']) : null;

            if ($method === 'bon' && ! $customer) {
                throw ValidationException::withMessages(['customer_id' => 'Penjualan bon wajib memilih pelanggan.']);
            }

            [$lines, $subtotal, $costTotal] = $this->buildLines($data['items'] ?? []);

            $discount = min(max(0, (float) ($data['discount_amount'] ?? 0)), $subtotal);
            $grandTotal = round($subtotal - $discount, 2);

            if ($method === 'bon') {
                $paid = min(max(0, (float) ($data['down_payment'] ?? 0)), $grandTotal);
                $cashReceived = $paid;
                $change = 0;
                $remaining = round($grandTotal - $paid, 2);

                if ($customer->credit_limit !== null && (float) $customer->credit_limit > 0
                    && $customer->outstanding() + $remaining > (float) $customer->credit_limit + 0.01) {
                    throw ValidationException::withMessages([
                        'customer_id' => "Bon melebihi limit kredit {$customer->name} (limit Rp " . number_format((float) $customer->credit_limit, 0, ',', '.')
                            . ', bon berjalan Rp ' . number_format($customer->outstanding(), 0, ',', '.') . ').',
                    ]);
                }
            } else {
                $paid = $grandTotal;
                $cashReceived = $method === 'cash'
                    ? (float) ($data['cash_received'] ?? $grandTotal)
                    : $grandTotal;

                if ($cashReceived + 0.01 < $grandTotal) {
                    throw ValidationException::withMessages(['cash_received' => 'Uang diterima kurang dari total belanja. Gunakan pembayaran Bon bila pembeli belum membayar penuh.']);
                }
                $change = round($cashReceived - $grandTotal, 2);
                $remaining = 0;
            }

            $sale = Sale::create([
                'sale_no'          => $this->generateSaleNo(),
                'sale_date'        => now(),
                'customer_id'      => $customer?->id,
                'user_id'          => $userId,
                'subtotal'         => $subtotal,
                'discount_amount'  => $discount,
                'grand_total'      => $grandTotal,
                'cost_total'       => $costTotal,
                'paid_amount'      => $paid,
                'cash_received'    => $cashReceived,
                'change_amount'    => $change,
                'remaining_amount' => $remaining,
                'payment_method'   => $method,
                'payment_status'   => $this->statusFor($grandTotal, $remaining),
                'due_date'         => $remaining > 0 ? now()->addDays((int) config('store.credit_days', 30))->toDateString() : null,
                'notes'            => $data['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                // base_unit hanya untuk catatan stok, bukan kolom sale_items
                $sale->items()->create(array_diff_key($line, ['base_unit' => true]));

                $this->stock->recordAuto([
                    'material_id'            => $line['material_id'],
                    'material_name_snapshot' => $line['material_name_snapshot'],
                    'unit_name_snapshot'     => $line['base_unit'],
                    'movement_type'          => 'out',
                    'qty'                    => $line['qty_base'],
                    'unit_price'             => $line['cost_price'],
                    'reference_type'         => 'sale',
                    'reference_id'           => $sale->id,
                    'notes'                  => "Penjualan {$sale->sale_no}",
                    'movement_date'          => now()->toDateString(),
                    'created_by'             => $userId,
                ]);
            }

            if ($paid > 0) {
                SalePayment::create([
                    'sale_id'        => $sale->id,
                    'customer_id'    => $customer?->id,
                    'payment_date'   => now()->toDateString(),
                    'amount'         => $paid,
                    'payment_method' => $method === 'bon' ? ($data['down_payment_method'] ?? 'cash') : $method,
                    'notes'          => $method === 'bon' ? 'Uang muka bon' : 'Pembayaran di kasir',
                    'created_by'     => $userId,
                ]);
            }

            return $sale->load(['items', 'customer', 'user']);
        });
    }

    /**
     * Bayar bon. Bila $sale kosong, pembayaran dialokasikan ke bon tertua pelanggan (FIFO).
     */
    public function receivePayment(Customer $customer, float $amount, string $method, int $userId, ?Sale $sale = null, ?string $notes = null): float
    {
        return DB::transaction(function () use ($customer, $amount, $method, $userId, $sale, $notes) {
            $sales = $sale
                ? collect([$sale])
                : $customer->sales()->where('remaining_amount', '>', 0)->orderBy('sale_date')->orderBy('id')->lockForUpdate()->get();

            $outstanding = (float) $sales->sum('remaining_amount');
            if ($amount <= 0 || $amount > $outstanding + 0.01) {
                throw ValidationException::withMessages([
                    'amount' => 'Jumlah bayar harus lebih dari 0 dan tidak melebihi sisa bon (Rp ' . number_format($outstanding, 0, ',', '.') . ').',
                ]);
            }

            $left = $amount;
            foreach ($sales as $s) {
                if ($left <= 0) {
                    break;
                }
                $pay = min($left, (float) $s->remaining_amount);
                if ($pay <= 0) {
                    continue;
                }

                SalePayment::create([
                    'sale_id'        => $s->id,
                    'customer_id'    => $customer->id,
                    'payment_date'   => now()->toDateString(),
                    'amount'         => $pay,
                    'payment_method' => $method,
                    'notes'          => $notes ?? 'Pembayaran bon',
                    'created_by'     => $userId,
                ]);

                $remaining = round((float) $s->remaining_amount - $pay, 2);
                $s->update([
                    'paid_amount'      => (float) $s->paid_amount + $pay,
                    'remaining_amount' => $remaining,
                    'payment_status'   => $this->statusFor($s->netTotal(), $remaining),
                ]);

                $left -= $pay;
            }

            return $amount;
        });
    }

    /**
     * Retur penjualan: stok kembali, bon berkurang dulu, sisanya dikembalikan sebagai uang.
     *
     * @param array<int, float> $quantities sale_item_id => qty diretur (dalam satuan jual)
     */
    public function returnItems(Sale $sale, array $quantities, ?string $reason, int $userId): SaleReturn
    {
        return DB::transaction(function () use ($sale, $quantities, $reason, $userId) {
            $sale = Sale::lockForUpdate()->findOrFail($sale->id);
            $items = $sale->items()->get()->keyBy('id');
            $discountRatio = (float) $sale->subtotal > 0 ? (float) $sale->discount_amount / (float) $sale->subtotal : 0;

            $lines = [];
            $total = 0;
            $costTotal = 0;
            foreach ($quantities as $itemId => $qty) {
                $qty = (float) $qty;
                if ($qty <= 0) {
                    continue;
                }
                $item = $items->get((int) $itemId);
                if (! $item) {
                    throw ValidationException::withMessages(['items' => 'Item retur tidak ada di penjualan ini.']);
                }
                if ($qty > $item->returnableQty() + 0.0001) {
                    throw ValidationException::withMessages([
                        'items' => "Qty retur {$item->material_name_snapshot} melebihi qty yang bisa diretur ({$item->returnableQty()}).",
                    ]);
                }

                $amount = round($qty * (float) $item->price * (1 - $discountRatio), 2);
                $qtyBase = $qty * (float) $item->factor;
                $total += $amount;
                $costTotal += $qtyBase * (float) $item->cost_price;
                $lines[] = compact('item', 'qty', 'amount', 'qtyBase');
            }

            if (! $lines) {
                throw ValidationException::withMessages(['items' => 'Isi qty retur minimal untuk satu barang.']);
            }

            // Retur mengurangi bon dulu; kelebihannya dikembalikan tunai
            $reduceDebt = min($total, (float) $sale->remaining_amount);
            $refund = round($total - $reduceDebt, 2);

            $return = SaleReturn::create([
                'return_no'     => $this->generateReturnNo(),
                'sale_id'       => $sale->id,
                'return_date'   => now()->toDateString(),
                'total'         => $total,
                'cost_total'    => round($costTotal, 2),
                'refund_amount' => $refund,
                'reason'        => $reason,
                'created_by'    => $userId,
            ]);

            foreach ($lines as $line) {
                $return->items()->create([
                    'sale_item_id' => $line['item']->id,
                    'qty'          => $line['qty'],
                    'amount'       => $line['amount'],
                ]);
                $line['item']->increment('qty_returned', $line['qty']);

                if ($line['item']->material_id) {
                    $material = Material::with('unit')->find($line['item']->material_id);
                    $this->stock->recordAuto([
                        'material_id'            => $line['item']->material_id,
                        'material_name_snapshot' => $line['item']->material_name_snapshot,
                        'unit_name_snapshot'     => $material?->baseUnitName() ?? $line['item']->unit_name,
                        'movement_type'          => 'in',
                        'qty'                    => $line['qtyBase'],
                        'unit_price'             => $line['item']->cost_price,
                        'reference_type'         => 'sale_return',
                        'reference_id'           => $return->id,
                        'notes'                  => "Retur penjualan {$sale->sale_no}",
                        'movement_date'          => now()->toDateString(),
                        'created_by'             => $userId,
                    ]);
                }
            }

            $remaining = round((float) $sale->remaining_amount - $reduceDebt, 2);
            $sale->update([
                'returned_total'   => round((float) $sale->returned_total + $total, 2),
                'paid_amount'      => round((float) $sale->paid_amount - $refund, 2),
                'remaining_amount' => $remaining,
                'payment_status'   => $this->statusFor((float) $sale->grand_total - (float) $sale->returned_total - $total, $remaining),
            ]);

            return $return->load('items');
        });
    }

    /**
     * Susun baris penjualan, cek stok & hitung HPP.
     *
     * @return array{0: array<int, array>, 1: float, 2: float}
     */
    private function buildLines(array $items): array
    {
        $items = array_values(array_filter($items, fn ($i) => (float) ($i['qty'] ?? 0) > 0));
        if (! $items) {
            throw ValidationException::withMessages(['items' => 'Keranjang masih kosong.']);
        }

        $materials = Material::with(['unit', 'units'])
            ->whereIn('id', array_column($items, 'material_id'))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $needBase = [];
        $lines = [];
        $subtotal = 0;
        $costTotal = 0;

        foreach ($items as $row) {
            $material = $materials->get((int) $row['material_id']);
            if (! $material || ! $material->is_active) {
                throw ValidationException::withMessages(['items' => 'Ada barang yang tidak ditemukan atau sudah nonaktif.']);
            }

            $unitName = trim((string) ($row['unit_name'] ?? '')) ?: $material->baseUnitName();
            $factor = $material->factorFor($unitName);
            $qty = round((float) $row['qty'], 2);
            $qtyBase = $qty * $factor;

            // Harga dari kasir boleh diubah (nego), tapi tidak boleh negatif
            $price = isset($row['price']) && $row['price'] !== '' ? max(0, (float) $row['price']) : $material->sellPriceFor($unitName, $qty);
            if ($price <= 0) {
                throw ValidationException::withMessages(['items' => "Harga jual {$material->name} belum diisi."]);
            }

            $cost = $this->stock->averageCost($material->id);
            $lineTotal = round($qty * $price, 2);

            $needBase[$material->id] = ($needBase[$material->id] ?? 0) + $qtyBase;
            $subtotal += $lineTotal;
            $costTotal += $qtyBase * $cost;

            $lines[] = [
                'material_id'            => $material->id,
                'material_name_snapshot' => $material->name,
                'unit_name'              => $unitName,
                'factor'                 => $factor,
                'qty'                    => $qty,
                'qty_base'               => $qtyBase,
                'price'                  => $price,
                'cost_price'             => $cost,
                'subtotal'               => $lineTotal,
                'base_unit'              => $material->baseUnitName(),
            ];
        }

        foreach ($needBase as $materialId => $need) {
            $available = $this->stock->currentStock($materialId);
            if ($need > $available + 0.0001) {
                $m = $materials[$materialId];
                throw ValidationException::withMessages([
                    'items' => "Stok {$m->name} tidak cukup. Tersedia " . rtrim(rtrim(number_format($available, 2, ',', '.'), '0'), ',') . " {$m->baseUnitName()}.",
                ]);
            }
        }

        return [$lines, round($subtotal, 2), round($costTotal, 2)];
    }

    private function statusFor(float $total, float $remaining): string
    {
        return match (true) {
            $remaining <= 0.009     => 'paid',
            $remaining < $total     => 'partial',
            default                 => 'unpaid',
        };
    }

    private function generateSaleNo(): string
    {
        $prefix = 'PJ-' . now()->format('Ymd') . '-';
        $last = Sale::where('sale_no', 'like', $prefix . '%')->lockForUpdate()->orderByDesc('sale_no')->value('sale_no');
        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function generateReturnNo(): string
    {
        $prefix = 'RJ-' . now()->format('Ymd') . '-';
        $last = SaleReturn::where('return_no', 'like', $prefix . '%')->lockForUpdate()->orderByDesc('return_no')->value('return_no');
        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
