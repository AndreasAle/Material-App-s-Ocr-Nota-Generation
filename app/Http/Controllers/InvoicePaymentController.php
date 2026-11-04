<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Services\Accounting\InvoicePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoicePaymentController extends Controller
{
    public function __construct(
        protected InvoicePaymentService $service,
    ) {}

    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:transfer,cash,giro,cek',
            'reference_no'   => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:500',
        ]);

        $outstanding = (float) $invoice->grand_total - (float) $invoice->paid_amount;
        if ((float) $validated['amount'] > $outstanding + 0.01) {
            return back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan.');
        }

        $this->service->store($invoice, $validated, auth()->id());

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Pembayaran sebesar Rp ' . number_format($validated['amount'], 0, ',', '.') . ' berhasil dicatat.');
    }

    public function destroy(Invoice $invoice, InvoicePayment $payment): RedirectResponse
    {
        abort_unless($payment->invoice_id === $invoice->id, 404);

        $this->service->destroy($payment);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Data pembayaran berhasil dihapus.');
    }
}
