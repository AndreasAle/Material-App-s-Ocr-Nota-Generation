<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChartOfAccountService
{
    /**
     * Get all COA accounts grouped by type for display.
     */
    public function getGrouped(): Collection
    {
        return ChartOfAccount::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->groupBy('account_type');
    }

    /**
     * Get only postable (non-header) accounts for journal entry dropdowns.
     */
    public function getPostableAccounts(): Collection
    {
        return ChartOfAccount::where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();
    }

    /**
     * Get account by code (commonly used in AutoJournalService).
     */
    public function findByCode(string $code): ?ChartOfAccount
    {
        return ChartOfAccount::where('code', $code)->first();
    }

    /**
     * Store a new COA account.
     */
    public function store(array $data): ChartOfAccount
    {
        return DB::transaction(function () use ($data) {
            $parent = isset($data['parent_id']) ? ChartOfAccount::find($data['parent_id']) : null;

            return ChartOfAccount::create([
                'code'            => $data['code'],
                'name'            => $data['name'],
                'account_type'    => $data['account_type'],
                'account_subtype' => $data['account_subtype'] ?? null,
                'normal_balance'  => $data['normal_balance'] ?? ($this->guessNormalBalance($data['account_type'])),
                'parent_id'       => $data['parent_id'] ?? null,
                'level'           => $parent ? ($parent->level + 1) : 1,
                'is_header'       => (bool) ($data['is_header'] ?? false),
                'is_active'       => true,
                'description'     => $data['description'] ?? null,
                'sort_order'      => $data['sort_order'] ?? 0,
            ]);
        });
    }

    /**
     * Update an existing COA account.
     */
    public function update(ChartOfAccount $account, array $data): ChartOfAccount
    {
        $account->update([
            'code'            => $data['code'],
            'name'            => $data['name'],
            'account_type'    => $data['account_type'],
            'account_subtype' => $data['account_subtype'] ?? null,
            'normal_balance'  => $data['normal_balance'],
            'is_header'       => (bool) ($data['is_header'] ?? false),
            'is_active'       => (bool) ($data['is_active'] ?? true),
            'description'     => $data['description'] ?? null,
            'sort_order'      => $data['sort_order'] ?? 0,
        ]);

        return $account->refresh();
    }

    private function guessNormalBalance(string $type): string
    {
        return in_array($type, ['liability', 'equity', 'revenue']) ? 'credit' : 'debit';
    }
}
