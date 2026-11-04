<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Services\Accounting\ChartOfAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChartOfAccountController extends Controller
{
    public function __construct(
        protected ChartOfAccountService $service,
    ) {}

    public function index(): View
    {
        $grouped = $this->service->getGrouped();

        $stats = [
            'total'   => ChartOfAccount::count(),
            'active'  => ChartOfAccount::where('is_active', true)->count(),
            'headers' => ChartOfAccount::where('is_header', true)->count(),
        ];

        return view('accounting.coa.index', compact('grouped', 'stats'));
    }

    public function create(): View
    {
        $parents = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        return view('accounting.coa.create', compact('parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'            => 'required|string|max:20|unique:chart_of_accounts,code',
            'name'            => 'required|string|max:255',
            'account_type'    => 'required|in:asset,liability,equity,revenue,expense',
            'account_subtype' => 'nullable|string|max:50',
            'normal_balance'  => 'required|in:debit,credit',
            'parent_id'       => 'nullable|exists:chart_of_accounts,id',
            'is_header'       => 'boolean',
            'description'     => 'nullable|string|max:500',
            'sort_order'      => 'nullable|integer',
        ]);

        $account = $this->service->store($validated);

        return redirect()->route('coa.index')
            ->with('success', "Akun [{$account->code}] {$account->name} berhasil ditambahkan.");
    }

    public function edit(ChartOfAccount $coa): View
    {
        $parents = ChartOfAccount::where('is_active', true)
            ->where('id', '!=', $coa->id)
            ->orderBy('code')
            ->get();

        return view('accounting.coa.edit', compact('coa', 'parents'));
    }

    public function update(Request $request, ChartOfAccount $coa): RedirectResponse
    {
        $validated = $request->validate([
            'code'            => "required|string|max:20|unique:chart_of_accounts,code,{$coa->id}",
            'name'            => 'required|string|max:255',
            'account_type'    => 'required|in:asset,liability,equity,revenue,expense',
            'account_subtype' => 'nullable|string|max:50',
            'normal_balance'  => 'required|in:debit,credit',
            'is_header'       => 'boolean',
            'is_active'       => 'boolean',
            'description'     => 'nullable|string|max:500',
            'sort_order'      => 'nullable|integer',
        ]);

        $this->service->update($coa, $validated);

        return redirect()->route('coa.index')
            ->with('success', "Akun [{$coa->code}] {$coa->name} berhasil diperbarui.");
    }

    public function destroy(ChartOfAccount $coa): RedirectResponse
    {
        if ($coa->journalLines()->exists()) {
            return back()->with('error', 'Akun ini sudah digunakan dalam jurnal dan tidak dapat dihapus.');
        }
        if ($coa->children()->exists()) {
            return back()->with('error', 'Akun ini memiliki sub-akun dan tidak dapat dihapus.');
        }

        $name = "{$coa->code} {$coa->name}";
        $coa->delete();

        return redirect()->route('coa.index')
            ->with('success', "Akun {$name} berhasil dihapus.");
    }
}
