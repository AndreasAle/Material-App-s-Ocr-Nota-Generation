<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\Store\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $sales,
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $from = $request->input('from', now()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $query = Sale::with(['customer', 'user'])
            ->whereDate('sale_date', '>=', $from)
            ->whereDate('sale_date', '<=', $to)
            ->when($request->filled('status'), fn ($q) => $q->where('payment_status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('sale_no', 'like', '%' . $request->q . '%')
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', '%' . $request->q . '%'))))
            // kasir hanya melihat penjualannya sendiri
            ->when($user->role === 'kasir', fn ($q) => $q->where('user_id', $user->id));

        $summary = [
            'count' => (clone $query)->count(),
            'total' => (float) (clone $query)->sum('grand_total') - (float) (clone $query)->sum('returned_total'),
            'bon'   => (float) (clone $query)->sum('remaining_amount'),
        ];

        $sales = $query->latest('sale_date')->paginate(25)->withQueryString();

        return view('store.sales.index', compact('sales', 'summary', 'from', 'to'));
    }

    public function show(Sale $sale): View
    {
        $this->authorizeView($sale);
        $sale->load(['items.material', 'customer', 'user', 'payments', 'returns.items.saleItem']);

        return view('store.sales.show', compact('sale'));
    }

    public function receipt(Sale $sale): View
    {
        $this->authorizeView($sale);
        $sale->load(['items', 'customer', 'user']);

        return view('store.sales.receipt', compact('sale'));
    }

    public function storePayment(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', 'in:cash,transfer'],
        ]);

        if (! $sale->customer) {
            return back()->with('error', 'Penjualan ini tidak memiliki pelanggan.');
        }

        $this->sales->receivePayment($sale->customer, (float) $validated['amount'], $validated['payment_method'], (int) auth()->id(), $sale);

        return back()->with('success', 'Pembayaran bon dicatat.');
    }

    public function storeReturn(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate([
            'qty'    => ['required', 'array'],
            'qty.*'  => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $return = $this->sales->returnItems($sale, $validated['qty'], $validated['reason'] ?? null, (int) auth()->id());

        $msg = "Retur {$return->return_no} tersimpan, stok sudah dikembalikan.";
        if ((float) $return->refund_amount > 0) {
            $msg .= ' Kembalikan uang ke pembeli: Rp ' . number_format((float) $return->refund_amount, 0, ',', '.') . '.';
        }

        return redirect()->route('store.sales.show', $sale)->with('success', $msg);
    }

    private function authorizeView(Sale $sale): void
    {
        $user = auth()->user();
        abort_if($user->role === 'kasir' && $sale->user_id !== $user->id && ! $sale->sale_date->isToday(), 403);
    }
}
