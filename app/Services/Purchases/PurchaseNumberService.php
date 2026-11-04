<?php

namespace App\Services\Purchases;

use App\Models\PurchaseTransaction;

class PurchaseNumberService
{
    public function generate(?string $date = null): string
    {
        $date = $date ?: now()->format('Y-m-d');
        $prefix = 'PUR-' . date('Ym', strtotime($date));

        $last = PurchaseTransaction::query()
            ->where('transaction_no', 'like', $prefix . '-%')
            ->latest('id')
            ->value('transaction_no');

        $nextNumber = 1;

        if ($last) {
            $lastSeq = (int) substr($last, -4);
            $nextNumber = $lastSeq + 1;
        }

        return sprintf('%s-%04d', $prefix, $nextNumber);
    }
}