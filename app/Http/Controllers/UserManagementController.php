<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\PurchaseScanDraft;
use App\Models\PurchaseTransaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $role   = $request->input('role');
        $status = $request->input('status');

        $users = User::query()
            ->when($search, fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($role,   fn ($q) => $q->where('role', $role))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', (bool) $status))
            ->withCount(['purchaseTransactions'])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'    => User::count(),
            'admin'    => User::where('role', 'admin')->count(),
            'staff'    => User::where('role', 'staff')->count(),
            'pimpinan' => User::where('role', 'pimpinan')->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('users.index', compact('users', 'stats', 'search', 'role', 'status'));
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------
    public function create(): View
    {
        return view('users.create');
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'role'            => $data['role'],
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
            'is_active'       => $request->boolean('is_active', true),
        ]);

        return redirect()->route('users.index')
            ->with('success', "User {$data['name']} berhasil ditambahkan.");
    }

    // -------------------------------------------------------------------------
    // show — profil + aktivitas user
    // -------------------------------------------------------------------------
    public function show(User $user): View
    {
        // Statistik aktivitas
        $txCount     = PurchaseTransaction::where('user_id', $user->id)->count();
        $txTotal     = PurchaseTransaction::where('user_id', $user->id)->sum('grand_total');
        $scanCount   = PurchaseScanDraft::where('created_by', $user->id)->count();
        $txApproved  = PurchaseTransaction::where('user_id', $user->id)->where('approval_status', 'approved')->count();

        // 10 transaksi terakhir
        $recentTx = PurchaseTransaction::where('user_id', $user->id)
            ->with('project:id,name', 'supplier:id,name')
            ->orderByDesc('transaction_date')
            ->limit(10)
            ->get();

        // Pengeluaran per bulan (6 bulan terakhir)
        $monthlySpend = PurchaseTransaction::where('user_id', $user->id)
            ->select(DB::raw('YEAR(transaction_date) as year'), DB::raw('MONTH(transaction_date) as month'), DB::raw('SUM(grand_total) as total'))
            ->where('transaction_date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get();

        $chartLabels = [];
        $chartData   = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $chartLabels[] = $d->translatedFormat('M Y');
            $found = $monthlySpend->first(fn ($r) => $r->year == $d->year && $r->month == $d->month);
            $chartData[] = $found ? (float) $found->total : 0;
        }

        return view('users.show', compact(
            'user',
            'txCount', 'txTotal', 'scanCount', 'txApproved',
            'recentTx', 'chartLabels', 'chartData',
        ));
    }

    // -------------------------------------------------------------------------
    // edit
    // -------------------------------------------------------------------------
    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        // Cegah admin menonaktifkan dirinya sendiri
        if ($user->id === auth()->id() && ! $request->boolean('is_active', true)) {
            return back()->with('error', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');
        }

        // Cegah admin mengubah role dirinya sendiri
        if ($user->id === auth()->id() && $request->input('role') !== $user->role) {
            return back()->with('error', 'Anda tidak bisa mengubah role Anda sendiri.');
        }

        $data = $request->validated();

        $user->update([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'role'            => $data['role'],
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
            'is_active'       => $request->boolean('is_active', true),
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'Data user berhasil diperbarui.');
    }

    // -------------------------------------------------------------------------
    // resetPassword
    // -------------------------------------------------------------------------
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.required'  => 'Password baru wajib diisi.',
            'new_password.min'       => 'Password minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return back()->with('success', "Password user {$user->name} berhasil direset.");
    }

    // -------------------------------------------------------------------------
    // toggleActive
    // -------------------------------------------------------------------------
    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User {$user->name} berhasil {$status}.");
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        // Cek apakah user punya transaksi
        if ($user->purchaseTransactions()->exists()) {
            return back()->with('error', "User {$user->name} tidak bisa dihapus karena memiliki riwayat transaksi. Nonaktifkan saja.");
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User {$name} berhasil dihapus.");
    }
}
