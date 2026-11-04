<?php

namespace App\Console\Commands;

use App\Mail\InvoiceDueMail;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendInvoiceDueReminders extends Command
{
    protected $signature = 'invoices:send-due-reminders';
    protected $description = 'Kirim email pengingat untuk invoice yang jatuh tempo besok atau sudah terlambat';

    public function handle(): int
    {
        $today    = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        // Invoice jatuh tempo besok (belum bayar / bayar sebagian)
        $dueTomorrow = Invoice::whereDate('due_date', $tomorrow)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->get();

        // Invoice sudah overdue (belum bayar / bayar sebagian)
        $overdue = Invoice::whereDate('due_date', '<', $today)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->get();

        $admins = User::whereIn('role', ['admin', 'pimpinan'])->whereNotNull('email')->get();

        $sent = 0;

        foreach ($dueTomorrow as $invoice) {
            try {
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->queue(new InvoiceDueMail($invoice, false));
                }
                $sent++;
            } catch (\Throwable $e) {
                $this->error("Gagal kirim reminder invoice {$invoice->invoice_no}: {$e->getMessage()}");
            }
        }

        foreach ($overdue as $invoice) {
            try {
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->queue(new InvoiceDueMail($invoice, true));
                }
                $sent++;
            } catch (\Throwable $e) {
                $this->error("Gagal kirim overdue notice invoice {$invoice->invoice_no}: {$e->getMessage()}");
            }
        }

        $this->info("Selesai: {$dueTomorrow->count()} jatuh tempo besok, {$overdue->count()} terlambat. Total email dikirim: {$sent}.");

        return self::SUCCESS;
    }
}
