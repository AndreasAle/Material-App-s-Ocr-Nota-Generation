<?php

namespace App\Services\WhatsApp;

use App\Models\PurchaseTransaction;
use App\Models\RabPlan;
use App\Models\User;

class PurchaseWhatsAppService
{
    public function __construct(
        protected WhatsAppService $whatsapp,
    ) {}

    /**
     * Kirim notifikasi ke semua admin ketika staff submit approval.
     */
    public function notifyApprovalRequest(PurchaseTransaction $tx): void
    {
        $tx->loadMissing(['user', 'project', 'supplier']);

        $staffName   = $tx->user?->name ?? 'Staff';
        $projectName = $tx->project?->name ?? '-';
        $supplier    = $tx->supplier?->name ?? '-';
        $total       = 'Rp ' . number_format((float) $tx->grand_total, 0, ',', '.');
        $date        = $tx->transaction_date?->format('d/m/Y') ?? '-';

        $message = "📋 *Persetujuan Pembelian Diajukan*\n\n"
            . "Staff *{$staffName}* mengajukan persetujuan:\n"
            . "• No. Transaksi: *{$tx->transaction_no}*\n"
            . "• Tanggal: {$date}\n"
            . "• Proyek: {$projectName}\n"
            . "• Supplier: {$supplier}\n"
            . "• Total: *{$total}*\n\n"
            . "Silakan login ke sistem untuk menyetujui atau menolak.";

        $this->sendToAdmins($message);
    }

    /**
     * Kirim notifikasi ke pembuat transaksi setelah approve atau reject.
     */
    public function notifyApprovalResult(PurchaseTransaction $tx): void
    {
        $tx->loadMissing(['user', 'approvedBy']);

        $creatorPhone = $tx->user?->whatsapp_number;

        if (empty($creatorPhone)) {
            return;
        }

        $approverName = $tx->approvedBy?->name ?? 'Admin';
        $date         = $tx->approved_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i');

        if ($tx->approval_status === 'approved') {
            $message = "✅ *Transaksi Disetujui*\n\n"
                . "Transaksi Anda telah *disetujui*:\n"
                . "• No. Transaksi: *{$tx->transaction_no}*\n"
                . "• Disetujui oleh: {$approverName}\n"
                . "• Pada: {$date}\n\n"
                . "Terima kasih!";
        } else {
            $reason  = $tx->rejection_reason ?? '-';
            $message = "❌ *Transaksi Ditolak*\n\n"
                . "Transaksi Anda *ditolak*:\n"
                . "• No. Transaksi: *{$tx->transaction_no}*\n"
                . "• Ditolak oleh: {$approverName}\n"
                . "• Alasan: {$reason}\n"
                . "• Pada: {$date}\n\n"
                . "Silakan login untuk merevisi transaksi.";
        }

        $this->whatsapp->send($creatorPhone, $message);
    }

    /**
     * Kirim alert ke semua admin ketika utilisasi RAB melampaui threshold.
     */
    public function notifyBudgetAlert(RabPlan $plan, float $pct): void
    {
        $plan->loadMissing('project');

        $projectName  = $plan->project?->name ?? '-';
        $totalBudget  = 'Rp ' . number_format((float) $plan->total_budget, 0, ',', '.');
        $pctFormatted = round($pct, 1);

        $emoji = $pct >= 100 ? '🚨' : '⚠️';

        $message = "{$emoji} *Peringatan Anggaran RAB*\n\n"
            . "RAB *{$plan->name}* sudah terpakai *{$pctFormatted}%*:\n"
            . "• Proyek: {$projectName}\n"
            . "• Total Anggaran: {$totalBudget}\n"
            . "• Terpakai: {$pctFormatted}%\n\n"
            . ($pct >= 100
                ? "⛔ Anggaran telah HABIS. Segera evaluasi pembelian."
                : "Segera pantau pembelian agar tidak melebihi anggaran.");

        $this->sendToAdmins($message);
    }

    private function sendToAdmins(string $message): void
    {
        $admins = User::query()
            ->whereIn('role', ['admin', 'pimpinan'])
            ->where('is_active', true)
            ->whereNotNull('whatsapp_number')
            ->where('whatsapp_number', '!=', '')
            ->get();

        foreach ($admins as $admin) {
            $this->whatsapp->send($admin->whatsapp_number, $message);
        }
    }
}
