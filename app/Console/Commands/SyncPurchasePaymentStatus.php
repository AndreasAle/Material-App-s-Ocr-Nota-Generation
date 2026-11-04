<?php

namespace App\Console\Commands;

use App\Models\PurchaseTransaction;
use App\Services\Accounting\SupplierPaymentService;
use Illuminate\Console\Command;

/**
 * Sync payment_status di purchase_transactions berdasarkan supplier_payments yang ada.
 */
class SyncPurchasePaymentStatus extends Command
{
    protected $signature   = 'notago:sync-purchase-payment-status';
    protected $description = 'Sync payment_status purchase_transactions dari supplier_payments';

    public function handle(): int
    {
        $this->info('Sinkronisasi status pembayaran transaksi pembelian...');

        $transactions = PurchaseTransaction::all();
        $updated = 0;

        $bar = $this->output->createProgressBar($transactions->count());
        $bar->start();

        $service = app(SupplierPaymentService::class);

        foreach ($transactions as $tx) {
            // Rumus sama dengan aplikasi: pembayaran + nota kredit retur
            $service->syncTransactionPaidAmount($tx);

            $updated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai! {$updated} transaksi berhasil disinkronisasi.");

        return self::SUCCESS;
    }
}
