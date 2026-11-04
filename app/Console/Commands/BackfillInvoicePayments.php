<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backfill invoice_payments dari paid_amount yang sudah ada di invoices.
 * Berguna untuk data lama sebelum modul payment record ditambahkan.
 */
class BackfillInvoicePayments extends Command
{
    protected $signature   = 'notago:backfill-invoice-payments {--dry-run : Hanya tampilkan, tidak simpan}';
    protected $description = 'Backfill invoice_payments dari invoices.paid_amount yang sudah ada';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $invoices = Invoice::whereIn('payment_status', ['paid', 'partial'])
            ->where('paid_amount', '>', 0)
            ->whereDoesntHave('payments')
            ->get();

        $this->info("Ditemukan {$invoices->count()} invoice dengan paid_amount belum punya record pembayaran.");

        if ($invoices->isEmpty()) {
            $this->info('Tidak ada yang perlu di-backfill.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($invoices->count());
        $bar->start();

        $created = 0;
        foreach ($invoices as $inv) {
            if (! $dryRun) {
                InvoicePayment::create([
                    'invoice_id'     => $inv->id,
                    'payment_date'   => $inv->invoice_date,
                    'amount'         => (float) $inv->paid_amount,
                    'payment_method' => 'transfer',
                    'reference_no'   => null,
                    'notes'          => '[Backfill dari data lama]',
                    'created_by'     => $inv->created_by,
                ]);
            }
            $created++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($dryRun) {
            $this->warn("DRY RUN: {$created} record akan dibuat (gunakan tanpa --dry-run untuk menyimpan).");
        } else {
            $this->info("Berhasil membuat {$created} record pembayaran.");
        }

        return self::SUCCESS;
    }
}
