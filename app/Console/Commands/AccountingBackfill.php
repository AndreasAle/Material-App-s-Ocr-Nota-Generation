<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\PurchaseTransaction;
use Illuminate\Console\Command;

/**
 * Backfill akuntansi fields (dpp, subtotal, remaining_amount) dari data yang sudah ada.
 */
class AccountingBackfill extends Command
{
    protected $signature   = 'notago:accounting-backfill';
    protected $description = 'Backfill kolom akuntansi (dpp, subtotal, remaining) dari data existing';

    public function handle(): int
    {
        $this->info('Memulai backfill akuntansi...');

        // Backfill invoices.dpp dari (subtotal - discount_amount)
        $invoices = Invoice::whereNull('dpp')->orWhere('dpp', 0)->get();
        $this->info("Backfill DPP untuk {$invoices->count()} invoice...");
        foreach ($invoices as $inv) {
            $dpp = (float) $inv->subtotal - (float) $inv->discount_amount;
            $inv->update(['dpp' => max(0, $dpp)]);
        }

        // Backfill purchase_transactions.subtotal, dpp, remaining_amount
        $txs = PurchaseTransaction::whereNull('subtotal')->orWhere('subtotal', 0)->get();
        $this->info("Backfill subtotal untuk {$txs->count()} transaksi pembelian...");
        foreach ($txs as $tx) {
            $grandTotal = (float) $tx->grand_total;
            $paid       = (float) ($tx->paid_amount ?? 0);
            $tx->update([
                'subtotal'         => $grandTotal,
                'dpp'              => $grandTotal / 1.11, // Asumsi sudah termasuk PPN 11%
                'ppn_amount'       => $grandTotal - ($grandTotal / 1.11),
                'ppn_rate'         => 11,
                'remaining_amount' => max(0, $grandTotal - $paid),
            ]);
        }

        // Set payment_status = 'unpaid' untuk yang null
        PurchaseTransaction::whereNull('payment_status')->update(['payment_status' => 'unpaid']);
        Invoice::whereNull('doc_status')->update(['doc_status' => 'draft']);

        $this->info('Backfill akuntansi selesai!');

        return self::SUCCESS;
    }
}
