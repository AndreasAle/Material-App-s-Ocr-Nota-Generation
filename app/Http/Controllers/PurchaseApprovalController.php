<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectTransactionRequest;
use App\Mail\PurchaseApprovalRequestMail;
use App\Mail\PurchaseApprovalResultMail;
use App\Models\PurchaseTransaction;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\WhatsApp\PurchaseWhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class PurchaseApprovalController extends Controller
{
    public function __construct(
        protected PurchaseWhatsAppService $whatsapp,
        protected NotificationService $notifications,
    ) {}

    public function submitForApproval(PurchaseTransaction $purchaseTransaction): RedirectResponse
    {
        $user = auth()->user();

        if ($user->role === 'staff' && $purchaseTransaction->user_id !== $user->id) {
            abort(403);
        }

        if (! in_array($purchaseTransaction->approval_status, ['draft', 'rejected', null], true)) {
            return back()->with('error', 'Transaksi ini sedang menunggu atau sudah mendapat persetujuan.');
        }

        $purchaseTransaction->update([
            'approval_status' => 'pending',
            'rejection_reason' => null,
        ]);

        $this->whatsapp->notifyApprovalRequest($purchaseTransaction);

        // In-app notification
        $this->notifications->notifyAdmins(
            type: 'approval_request',
            title: "Transaksi {$purchaseTransaction->transaction_no} Menunggu Persetujuan",
            message: "{$user->name} mengajukan transaksi untuk disetujui.",
            data: ['url' => "/purchase-transactions/{$purchaseTransaction->id}"],
        );

        // Email notification to all approvers
        try {
            User::whereIn('role', ['admin', 'pimpinan'])
                ->whereNotNull('email')
                ->each(function (User $approver) use ($purchaseTransaction) {
                    Mail::to($approver->email)->queue(new PurchaseApprovalRequestMail($purchaseTransaction, $approver));
                });
        } catch (\Throwable) {}

        return back()->with('success', 'Transaksi berhasil diajukan untuk persetujuan.');
    }

    public function approve(PurchaseTransaction $purchaseTransaction): RedirectResponse
    {
        $user = auth()->user();

        if (! in_array($user->role, ['admin', 'pimpinan'])) {
            abort(403);
        }

        if ($purchaseTransaction->approval_status !== 'pending') {
            return back()->with('error', 'Hanya transaksi dengan status "Menunggu Persetujuan" yang dapat disetujui.');
        }

        $purchaseTransaction->update([
            'approval_status'  => 'approved',
            'approved_by'      => $user->id,
            'approved_at'      => now(),
            'rejection_reason' => null,
        ]);

        $fresh = $purchaseTransaction->fresh();
        $this->whatsapp->notifyApprovalResult($fresh);

        // In-app notification to creator
        $creator = $fresh->creator ?? $fresh->user;
        if ($creator) {
            $this->notifications->create(
                userId: $creator->id,
                type: 'approval_result',
                title: "Transaksi {$fresh->transaction_no} Disetujui",
                message: "{$user->name} telah menyetujui transaksi Anda.",
                data: ['url' => "/purchase-transactions/{$fresh->id}"],
            );
        }

        // Email notification to creator
        try {
            if ($creator?->email) {
                Mail::to($creator->email)->queue(new PurchaseApprovalResultMail($fresh, 'approved'));
            }
        } catch (\Throwable) {}

        return back()->with('success', 'Transaksi berhasil disetujui.');
    }

    public function reject(RejectTransactionRequest $request, PurchaseTransaction $purchaseTransaction): RedirectResponse
    {
        $user = auth()->user();

        if (! in_array($user->role, ['admin', 'pimpinan'])) {
            abort(403);
        }

        if ($purchaseTransaction->approval_status !== 'pending') {
            return back()->with('error', 'Hanya transaksi dengan status "Menunggu Persetujuan" yang dapat ditolak.');
        }

        $purchaseTransaction->update([
            'approval_status'  => 'rejected',
            'approved_by'      => $user->id,
            'approved_at'      => now(),
            'rejection_reason' => $request->validated()['rejection_reason'],
        ]);

        $freshRejected = $purchaseTransaction->fresh();
        $this->whatsapp->notifyApprovalResult($freshRejected);

        // In-app notification to creator
        $creatorRej = $freshRejected->creator ?? $freshRejected->user;
        if ($creatorRej) {
            $this->notifications->create(
                userId: $creatorRej->id,
                type: 'rejection_result',
                title: "Transaksi {$freshRejected->transaction_no} Ditolak",
                message: "{$user->name} menolak transaksi Anda: {$request->validated()['rejection_reason']}",
                data: ['url' => "/purchase-transactions/{$freshRejected->id}"],
            );
        }

        // Email notification to creator
        try {
            if ($creatorRej?->email) {
                Mail::to($creatorRej->email)->queue(new PurchaseApprovalResultMail($freshRejected, 'rejected'));
            }
        } catch (\Throwable) {}

        return back()->with('success', 'Transaksi berhasil ditolak.');
    }
}
