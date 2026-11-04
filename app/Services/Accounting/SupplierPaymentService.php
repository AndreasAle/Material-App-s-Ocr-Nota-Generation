<?php

namespace App\Services\Accounting;

use App\Models\PurchaseTransaction;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;

class SupplierPaymentService
{
    /**
     * Record a payment for a purchase transaction.
     */
    public function store(PurchaseTransaction $tx, array $data, int $userId): SupplierPayment
    {
        return DB::transaction(function () use ($tx, $data, $userId) {
            $payment = SupplierPayment::create([
                'purchase_transaction_id' => $tx->id,
                'payment_date'            => $data['payment_date'],
                'amount'                  => (float) $data['amount'],
                'payment_method'          => $data['payment_method'] ?? 'transfer',
                'reference_no'            => $data['reference_no'] ?? null,
                'notes'                   => $data['notes'] ?? null,
                'created_by'              => $userId,
            ]);

            $this->syncTransactionPaidAmount($tx);

            return $payment;
        });
    }

    /**
     * Delete a supplier payment and re-sync transaction totals.
     */
    public function destroy(SupplierPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $tx = $payment->purchaseTransaction;
            $payment->delete();
            $this->syncTransactionPaidAmount($tx);
        });
    }

    /**
     * Recalculate purchase transaction paid_amount from all payment records.
     */
    public function syncTransactionPaidAmount(PurchaseTransaction $tx): void
    {
        $totalPaid   = (float) SupplierPayment::where('purchase_transaction_id', $tx->id)->sum('amount');
        $totalCredit = $this->creditedAmount($tx);
        $grandTotal  = (float) $tx->grand_total;

        // paid_amount = uang yang benar-benar dibayar; nota kredit retur mengurangi hutang tanpa dihitung kas keluar
        $settled   = $totalPaid + $totalCredit;
        $remaining = max(0, $grandTotal - $settled);

        $status = match(true) {
            $settled >= $grandTotal && $grandTotal > 0 => 'paid',
            $settled > 0                               => 'partial',
            default                                    => 'unpaid',
        };

        $tx->update([
            'paid_amount'      => $totalPaid,
            'remaining_amount' => $remaining,
            'payment_status'   => $status,
        ]);
    }

    /**
     * Total nota kredit dari retur yang sudah dikreditkan supplier.
     */
    public function creditedAmount(PurchaseTransaction $tx): float
    {
        return (float) \App\Models\PurchaseReturn::where('purchase_transaction_id', $tx->id)
            ->where('status', 'credited')
            ->sum('credit_amount');
    }

    /**
     * Get payment history for a purchase transaction.
     */
    public function getHistory(PurchaseTransaction $tx)
    {
        return SupplierPayment::where('purchase_transaction_id', $tx->id)
            ->with('creator')
            ->orderBy('payment_date')
            ->get();
    }
}
