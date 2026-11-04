<?php

namespace App\Console\Commands;

use App\Models\PurchaseTransaction;
use App\Models\Sale;
use App\Models\User;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Console\Command;

/**
 * Pengingat WhatsApp harian:
 * - ke pelanggan: bon jatuh tempo besok, hari ini, dan tiap 7 hari setelah lewat tempo;
 * - ke pemilik: daftar hutang supplier yang jatuh tempo ≤ N hari / sudah lewat, plus bon yang telat.
 */
class SendStoreReminders extends Command
{
    protected $signature = 'store:send-reminders {--dry-run : Tampilkan pesan tanpa mengirim}';

    protected $description = 'Kirim pengingat WhatsApp bon pelanggan & hutang supplier yang jatuh tempo';

    public function handle(WhatsAppService $wa): int
    {
        $dry = (bool) $this->option('dry-run');
        $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
        $store = config('store.name');
        $sent = 0;

        // ── Bon pelanggan ────────────────────────────────────────────────
        $dueSales = Sale::with('customer')
            ->where('remaining_amount', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', now()->addDay())
            ->get()
            ->filter(function (Sale $s) {
                $diff = (int) now()->startOfDay()->diffInDays($s->due_date->startOfDay(), false); // + = belum tempo
                return $diff === 1 || $diff === 0 || ($diff < 0 && abs($diff) % 7 === 0);
            });

        foreach ($dueSales->groupBy('customer_id') as $sales) {
            $customer = $sales->first()->customer;
            if (! $customer?->phone) {
                continue;
            }
            $lines = $sales->map(fn ($s) => "- {$s->sale_no} ({$s->sale_date->format('d/m/Y')}): {$rp($s->remaining_amount)}, tempo {$s->due_date->format('d/m/Y')}")->implode("\n");
            $message = "Halo {$customer->name},\nkami dari {$store} mengingatkan bon yang jatuh tempo:\n{$lines}\nTotal: {$rp($sales->sum('remaining_amount'))}\n\nTerima kasih 🙏";

            $sent += $this->deliver($wa, $customer->phone, $message, $dry);
        }

        // ── Ringkasan untuk pemilik ──────────────────────────────────────
        $payables = PurchaseTransaction::with('supplier')->notRejected()
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', now()->addDays((int) config('store.payable_reminder_days', 3)))
            ->orderBy('due_date')
            ->get();
        $overdueBon = Sale::with('customer')->where('remaining_amount', '>', 0)
            ->whereNotNull('due_date')->whereDate('due_date', '<', now()->toDateString())->get();

        if ($payables->isNotEmpty() || $overdueBon->isNotEmpty()) {
            $parts = ["Pengingat {$store} — " . now()->format('d/m/Y')];
            if ($payables->isNotEmpty()) {
                $parts[] = "\nHutang supplier jatuh tempo:";
                foreach ($payables as $tx) {
                    $label = $tx->due_date->isPast() && ! $tx->due_date->isToday() ? 'LEWAT' : $tx->due_date->format('d/m');
                    $parts[] = "- " . ($tx->supplier->name ?? '-') . " {$tx->transaction_no}: {$rp($tx->remaining_amount)} ({$label})";
                }
                $parts[] = 'Total: ' . $rp($payables->sum('remaining_amount'));
            }
            if ($overdueBon->isNotEmpty()) {
                $parts[] = "\nBon pelanggan lewat tempo: {$overdueBon->count()} nota, total " . $rp($overdueBon->sum('remaining_amount'));
            }
            $message = implode("\n", $parts);

            User::where('role', 'admin')->where('is_active', true)->whereNotNull('whatsapp_number')
                ->each(function (User $owner) use ($wa, $message, $dry, &$sent) {
                    $sent += $this->deliver($wa, $owner->whatsapp_number, $message, $dry);
                });
        }

        $this->info(($dry ? '[dry-run] ' : '') . "Pengingat diproses: {$sent} pesan.");

        return self::SUCCESS;
    }

    private function deliver(WhatsAppService $wa, string $to, string $message, bool $dry): int
    {
        if ($dry) {
            $this->line("→ {$to}\n{$message}\n");

            return 1;
        }

        return $wa->send($to, $message) ? 1 : 0;
    }
}
