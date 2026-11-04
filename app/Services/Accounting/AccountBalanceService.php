<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccountBalanceService
{
    /**
     * Get account balance for a specific period.
     * Returns: ['debit' => x, 'credit' => y, 'balance' => z]
     */
    public function getBalance(ChartOfAccount $account, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = JournalEntryLine::where('account_id', $account->id)
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.status', 'posted');

        if ($dateFrom) {
            $query->where('journal_entries.entry_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('journal_entries.entry_date', '<=', $dateTo);
        }

        $result = $query->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')->first();

        $debit  = (float) ($result->total_debit ?? 0);
        $credit = (float) ($result->total_credit ?? 0);

        // Balance direction depends on normal_balance
        $balance = $account->normal_balance === 'debit'
            ? ($debit - $credit)
            : ($credit - $debit);

        return compact('debit', 'credit', 'balance');
    }

    /**
     * Build Trial Balance for a date range.
     * Returns collection of accounts with debit/credit/balance.
     */
    public function getTrialBalance(string $dateFrom, string $dateTo): Collection
    {
        $accounts = ChartOfAccount::where('is_header', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        // Aggregate all lines in range
        $totals = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->where('je.status', 'posted')
            ->whereBetween('je.entry_date', [$dateFrom, $dateTo])
            ->selectRaw('jel.account_id, SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit')
            ->groupBy('jel.account_id')
            ->get()
            ->keyBy('account_id');

        return $accounts->map(function ($account) use ($totals) {
            $row    = $totals->get($account->id);
            $debit  = (float) ($row?->total_debit  ?? 0);
            $credit = (float) ($row?->total_credit ?? 0);
            $balance = $account->normal_balance === 'debit'
                ? ($debit - $credit)
                : ($credit - $debit);

            return (object) [
                'account'      => $account,
                'total_debit'  => $debit,
                'total_credit' => $credit,
                'balance'      => $balance,
            ];
        })->filter(fn ($row) => $row->total_debit > 0 || $row->total_credit > 0);
    }

    /**
     * Get total balance for a specific account type and date range.
     * Used for Balance Sheet / P&L calculations.
     */
    public function getTypeBalance(string $accountType, ?string $dateFrom = null, ?string $dateTo = null): float
    {
        $query = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jel.account_id')
            ->where('je.status', 'posted')
            ->where('coa.account_type', $accountType);

        if ($dateFrom) {
            $query->where('je.entry_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('je.entry_date', '<=', $dateTo);
        }

        $result = $query->selectRaw('SUM(jel.debit) as d, SUM(jel.credit) as c')->first();

        $debit  = (float) ($result?->d ?? 0);
        $credit = (float) ($result?->c ?? 0);

        // Revenue/Liability/Equity: credit normal → net = credit - debit
        if (in_array($accountType, ['revenue', 'liability', 'equity'])) {
            return $credit - $debit;
        }

        // Asset/Expense: debit normal → net = debit - credit
        return $debit - $credit;
    }
}
