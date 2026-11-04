<?php

namespace App\Services\Accounting;

use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PurchaseTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Generates double-entry journal entries automatically from source documents.
 *
 * Indonesian Construction COA mapping (standard):
 *  1101 - Kas / Bank
 *  1201 - Piutang Usaha
 *  2101 - Hutang Usaha
 *  4101 - Pendapatan Jasa Konstruksi
 *  5101 - Beban Material
 *  1301 - PPN Masukan (Pajak Dibayar di Muka)
 *  2201 - PPN Keluaran (Hutang Pajak)
 */
class AutoJournalService
{
    private ChartOfAccountService $coa;

    public function __construct(ChartOfAccountService $coa)
    {
        $this->coa = $coa;
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Post a sales invoice to the general ledger.
     * DR Piutang Usaha (1201) | CR Pendapatan (4101) + CR PPN Keluaran (2201)
     */
    public function postInvoice(Invoice $invoice, int $userId): JournalEntry
    {
        return DB::transaction(function () use ($invoice, $userId) {
            $dpp        = (float) ($invoice->dpp ?: ($invoice->subtotal - $invoice->discount_amount));
            $ppn        = (float) $invoice->tax_amount;
            $total      = (float) $invoice->grand_total;

            $entry = $this->createEntry(
                date   : $invoice->invoice_date->toDateString(),
                refType: 'invoice',
                refId  : $invoice->id,
                desc   : "Invoice {$invoice->invoice_no} — {$invoice->client_name}",
                userId : $userId
            );

            $lines = [
                ['code' => '1201', 'debit' => $total,  'credit' => 0,    'desc' => "Piutang — {$invoice->invoice_no}"],
                ['code' => '4101', 'debit' => 0,        'credit' => $dpp, 'desc' => "Pendapatan — {$invoice->invoice_no}"],
            ];

            if ($ppn > 0) {
                $lines[] = ['code' => '2201', 'debit' => 0, 'credit' => $ppn, 'desc' => "PPN Keluaran — {$invoice->invoice_no}"];
            }

            $this->createLines($entry, $lines, $invoice->project_id);
            $this->postEntry($entry, $userId);

            $invoice->update(['journal_entry_id' => $entry->id, 'posted_at' => now(), 'posted_by' => $userId, 'doc_status' => 'posted']);

            return $entry;
        });
    }

    /**
     * Post a supplier payment receipt (cash out).
     * DR Hutang Usaha (2101) | CR Kas/Bank (1101) + CR PPN Masukan reversal if needed
     */
    public function postPurchase(PurchaseTransaction $tx, int $userId): JournalEntry
    {
        return DB::transaction(function () use ($tx, $userId) {
            $dpp    = (float) ($tx->dpp ?: $tx->grand_total);
            $ppn    = (float) $tx->ppn_amount;
            $total  = (float) $tx->grand_total;

            $supplierName = $tx->supplier?->name ?? 'Supplier';

            $entry = $this->createEntry(
                date   : $tx->transaction_date->toDateString(),
                refType: 'purchase_transaction',
                refId  : $tx->id,
                desc   : "Pembelian {$tx->transaction_no} — {$supplierName}",
                userId : $userId
            );

            $lines = [
                ['code' => '5101', 'debit' => $dpp,   'credit' => 0,     'desc' => "Beban Material — {$tx->transaction_no}"],
                ['code' => '2101', 'debit' => 0,       'credit' => $total, 'desc' => "Hutang — {$tx->transaction_no}"],
            ];

            if ($ppn > 0) {
                $lines[] = ['code' => '1301', 'debit' => $ppn, 'credit' => 0, 'desc' => "PPN Masukan — {$tx->transaction_no}"];
                // Adjust Hutang to exclude PPN from credit side
                $lines[1]['credit'] = $total;
            }

            $this->createLines($entry, $lines, $tx->project_id);
            $this->postEntry($entry, $userId);

            $tx->update(['journal_entry_id' => $entry->id, 'posted_at' => now(), 'posted_by' => $userId]);

            return $entry;
        });
    }

    /**
     * Post invoice payment received (cash in).
     * DR Kas/Bank (1101) | CR Piutang Usaha (1201)
     */
    public function postInvoicePayment(Invoice $invoice, float $amount, string $date, int $userId): JournalEntry
    {
        return DB::transaction(function () use ($invoice, $amount, $date, $userId) {
            $entry = $this->createEntry(
                date   : $date,
                refType: 'invoice_payment',
                refId  : $invoice->id,
                desc   : "Pembayaran Invoice {$invoice->invoice_no}",
                userId : $userId
            );

            $this->createLines($entry, [
                ['code' => '1101', 'debit' => $amount, 'credit' => 0,      'desc' => "Terima Bayar — {$invoice->invoice_no}"],
                ['code' => '1201', 'debit' => 0,       'credit' => $amount, 'desc' => "Hapus Piutang — {$invoice->invoice_no}"],
            ], $invoice->project_id);

            $this->postEntry($entry, $userId);

            return $entry;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function createEntry(string $date, string $refType, int $refId, string $desc, int $userId): JournalEntry
    {
        $no = $this->generateEntryNo();

        return JournalEntry::create([
            'entry_no'       => $no,
            'entry_date'     => $date,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'description'    => $desc,
            'total_debit'    => 0,
            'total_credit'   => 0,
            'status'         => 'draft',
            'currency'       => 'IDR',
            'created_by'     => $userId,
        ]);
    }

    private function createLines(JournalEntry $entry, array $lines, ?int $projectId = null): void
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($lines as $i => $line) {
            $account = $this->coa->findByCode($line['code']);
            if (! $account) {
                continue; // skip if COA not seeded yet
            }

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $account->id,
                'account_code'     => $account->code,
                'account_name'     => $account->name,
                'debit'            => $line['debit'],
                'credit'           => $line['credit'],
                'description'      => $line['desc'] ?? null,
                'project_id'       => $projectId,
                'line_order'       => $i + 1,
            ]);

            $totalDebit  += (float) $line['debit'];
            $totalCredit += (float) $line['credit'];
        }

        $entry->update([
            'total_debit'  => $totalDebit,
            'total_credit' => $totalCredit,
        ]);
    }

    private function postEntry(JournalEntry $entry, int $userId): void
    {
        $entry->update([
            'status'    => 'posted',
            'posted_by' => $userId,
            'posted_at' => now(),
        ]);
    }

    private function generateEntryNo(): string
    {
        $prefix = 'JE-' . now()->format('Ym') . '-';

        $last = JournalEntry::where('entry_no', 'like', $prefix . '%')
            ->orderByDesc('entry_no')
            ->first();

        $seq = $last ? ((int) substr($last->entry_no, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}
