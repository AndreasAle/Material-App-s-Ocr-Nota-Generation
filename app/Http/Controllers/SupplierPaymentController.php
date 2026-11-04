<?php

namespace App\Http\Controllers;

use App\Models\PurchaseTransaction;
use App\Models\SupplierPayment;
use App\Services\Accounting\SupplierPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    public function __construct(
        protected SupplierPaymentService $service,
    ) {}

    public function store(Request $request, PurchaseTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:transfer,cash,giro,cek',
            'reference_no'   => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:500',
        ]);

        if ($transaction->approval_status === 'rejected') {
            return back()->with('error', 'Transaksi yang ditolak tidak bisa dibayar.');
        }

        $outstanding = (float) $transaction->grand_total - (float) ($transaction->paid_amount ?? 0)
            - $this->service->creditedAmount($transaction);
        if ((float) $validated['amount'] > $outstanding + 0.01) {
            return back()->with('error', 'Jumlah pembayaran melebihi sisa hutang.');
        }

        $this->service->store($transaction, $validated, auth()->id());

        return redirect()->route('purchase-transactions.show', $transaction)
            ->with('success', 'Pembayaran kepada supplier berhasil dicatat.');
    }

    public function destroy(PurchaseTransaction $transaction, SupplierPayment $payment): RedirectResponse
    {
        abort_unless($payment->purchase_transaction_id === $transaction->id, 404);

        $this->service->destroy($payment);

        return redirect()->route('purchase-transactions.show', $transaction)
            ->with('success', 'Data pembayaran berhasil dihapus.');
    }
}
