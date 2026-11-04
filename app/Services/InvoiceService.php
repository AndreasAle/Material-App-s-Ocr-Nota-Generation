<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    // -------------------------------------------------------------------------
    // Generate nomor invoice otomatis: INV-YYYYMM-XXXX
    // -------------------------------------------------------------------------
    public function generateInvoiceNo(): string
    {
        $prefix = 'INV-' . now()->format('Ym') . '-';

        $last = Invoice::where('invoice_no', 'like', $prefix . '%')
            ->orderByDesc('invoice_no')
            ->first();

        $seq = $last ? ((int) substr($last->invoice_no, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // -------------------------------------------------------------------------
    // Store invoice baru
    // -------------------------------------------------------------------------
    public function store(array $data, int $userId): Invoice
    {
        return DB::transaction(function () use ($data, $userId) {
            $items = $data['items'] ?? [];

            // Hitung subtotal dari items
            $subtotal = collect($items)->sum(fn ($i) => (float) ($i['qty'] ?? 1) * (float) ($i['unit_price'] ?? 0));

            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $taxPercent     = (float) ($data['tax_percent'] ?? 0);
            $taxBase        = $subtotal - $discountAmount;
            $taxAmount      = $taxBase * $taxPercent / 100;
            $grandTotal     = $taxBase + $taxAmount;

            $invoice = Invoice::create([
                'invoice_no'      => $data['invoice_no'] ?? $this->generateInvoiceNo(),
                'project_id'      => $data['project_id'] ?? null,
                'client_name'     => $data['client_name'],
                'client_address'  => $data['client_address'] ?? null,
                'client_phone'    => $data['client_phone'] ?? null,
                'invoice_date'    => $data['invoice_date'],
                'due_date'        => $data['due_date'] ?? null,
                'payment_status'  => 'unpaid',
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_percent'     => $taxPercent,
                'tax_amount'      => $taxAmount,
                'grand_total'     => $grandTotal,
                'paid_amount'     => 0,
                'notes'           => $data['notes'] ?? null,
                'created_by'      => $userId,
            ]);

            foreach ($items as $i => $item) {
                $qty        = (float) ($item['qty'] ?? 1);
                $unitPrice  = (float) ($item['unit_price'] ?? 0);
                $itemSubtotal = $qty * $unitPrice;

                $invoice->items()->create([
                    'description' => $item['description'],
                    'unit'        => $item['unit'] ?? null,
                    'qty'         => $qty,
                    'unit_price'  => $unitPrice,
                    'subtotal'    => $itemSubtotal,
                    'notes'       => $item['notes'] ?? null,
                    'sort_order'  => $i,
                ]);
            }

            return $invoice;
        });
    }

    // -------------------------------------------------------------------------
    // Catat pembayaran
    // -------------------------------------------------------------------------
    public function recordPayment(Invoice $invoice, float $amount): Invoice
    {
        return DB::transaction(function () use ($invoice, $amount) {
            $newPaid = min((float) $invoice->grand_total, (float) $invoice->paid_amount + $amount);

            $status = match(true) {
                $newPaid >= (float) $invoice->grand_total => 'paid',
                $newPaid > 0                              => 'partial',
                default                                   => 'unpaid',
            };

            $invoice->update([
                'paid_amount'    => $newPaid,
                'payment_status' => $status,
            ]);

            return $invoice->refresh();
        });
    }

    // -------------------------------------------------------------------------
    // Recalculate totals (dipakai saat edit items)
    // -------------------------------------------------------------------------
    public function recalculate(Invoice $invoice): void
    {
        $subtotal       = $invoice->items()->sum('subtotal');
        $discountAmount = (float) $invoice->discount_amount;
        $taxPercent     = (float) $invoice->tax_percent;
        $taxBase        = $subtotal - $discountAmount;
        $taxAmount      = $taxBase * $taxPercent / 100;
        $grandTotal     = $taxBase + $taxAmount;

        $invoice->update([
            'subtotal'    => $subtotal,
            'tax_amount'  => $taxAmount,
            'grand_total' => $grandTotal,
        ]);
    }
}
