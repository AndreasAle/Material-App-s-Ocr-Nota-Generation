<?php

namespace App\Services\Accounting;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Support\Facades\DB;

class InvoicePaymentService
{
    /**
     * Record a payment for an invoice and update invoice paid status.
     */
    public function store(Invoice $invoice, array $data, int $userId): InvoicePayment
    {
        return DB::transaction(function () use ($invoice, $data, $userId) {
            $payment = InvoicePayment::create([
                'invoice_id'     => $invoice->id,
                'payment_date'   => $data['payment_date'],
                'amount'         => (float) $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'transfer',
                'reference_no'   => $data['reference_no'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'created_by'     => $userId,
            ]);

            $this->syncInvoicePaidAmount($invoice);

            return $payment;
        });
    }

    /**
     * Delete a payment and re-sync invoice totals.
     */
    public function destroy(InvoicePayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $invoice = $payment->invoice;
            $payment->delete();
            $this->syncInvoicePaidAmount($invoice);
        });
    }

    /**
     * Recalculate invoice paid_amount from sum of all payment records.
     */
    public function syncInvoicePaidAmount(Invoice $invoice): void
    {
        $totalPaid = InvoicePayment::where('invoice_id', $invoice->id)->sum('amount');
        $totalPaid = (float) $totalPaid;
        $grandTotal = (float) $invoice->grand_total;

        $status = match(true) {
            $totalPaid >= $grandTotal && $grandTotal > 0 => 'paid',
            $totalPaid > 0                               => 'partial',
            default                                      => 'unpaid',
        };

        $invoice->update([
            'paid_amount'    => $totalPaid,
            'payment_status' => $status,
        ]);
    }

    /**
     * Get payment history for an invoice.
     */
    public function getHistory(Invoice $invoice)
    {
        return InvoicePayment::where('invoice_id', $invoice->id)
            ->with('creator')
            ->orderBy('payment_date')
            ->get();
    }
}
