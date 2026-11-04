<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Store\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected SaleService $sales,
    ) {}

    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->withSum(['sales as outstanding' => fn ($q) => $q->where('remaining_amount', '>', 0)], 'remaining_amount')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('phone', 'like', '%' . $request->q . '%')))
            ->when($request->get('filter') === 'bon', fn ($q) => $q->whereHas('sales', fn ($s) => $s->where('remaining_amount', '>', 0)))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $totalBon = (float) \App\Models\Sale::where('remaining_amount', '>', 0)->sum('remaining_amount');

        return view('store.customers.index', compact('customers', 'totalBon'));
    }

    public function create(): View
    {
        return view('store.customers.form', ['customer' => new Customer(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $customer = Customer::create($this->validated($request) + ['is_active' => true]);

        if ($request->expectsJson()) {
            return response()->json(['id' => $customer->id, 'name' => $customer->name, 'phone' => $customer->phone]);
        }

        return redirect()->route('store.customers.show', $customer)->with('success', 'Pelanggan ditambahkan.');
    }

    public function show(Customer $customer): View
    {
        $sales = $customer->sales()->latest('sale_date')->paginate(20);
        $payments = $customer->payments()->latest('payment_date')->limit(20)->get();
        $outstanding = $customer->outstanding();

        return view('store.customers.show', compact('customer', 'sales', 'payments', 'outstanding'));
    }

    public function edit(Customer $customer): View
    {
        return view('store.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validated($request) + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('store.customers.show', $customer)->with('success', 'Data pelanggan diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->sales()->exists()) {
            $customer->update(['is_active' => false]);

            return redirect()->route('store.customers.index')->with('success', 'Pelanggan punya riwayat transaksi, jadi dinonaktifkan (tidak dihapus).');
        }

        $customer->delete();

        return redirect()->route('store.customers.index')->with('success', 'Pelanggan dihapus.');
    }

    /** Bayar bon pelanggan, dialokasikan ke bon tertua dulu. */
    public function pay(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', 'in:cash,transfer'],
            'notes'          => ['nullable', 'string', 'max:255'],
        ]);

        $this->sales->receivePayment($customer, (float) $validated['amount'], $validated['payment_method'], (int) auth()->id(), null, $validated['notes'] ?? null);

        return back()->with('success', 'Pembayaran bon Rp ' . number_format((float) $validated['amount'], 0, ',', '.') . ' dicatat.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'address'      => ['nullable', 'string', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
