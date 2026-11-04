<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRule extends Model
{
    protected $fillable = [
        'document_type', 'amount_min', 'amount_max',
        'approver_role', 'approver_label', 'level', 'is_active', 'notes',
    ];

    protected $casts = [
        'amount_min' => 'decimal:2',
        'amount_max' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    /**
     * Cari rule yang tepat berdasarkan jenis dokumen dan nilai.
     */
    public static function findRule(string $documentType, float $amount): ?self
    {
        return self::where('document_type', $documentType)
            ->where('is_active', true)
            ->where('amount_min', '<=', $amount)
            ->where(function ($q) use ($amount) {
                $q->whereNull('amount_max')
                  ->orWhere('amount_max', '>=', $amount);
            })
            ->orderByDesc('amount_min')
            ->first();
    }

    /**
     * Cek apakah user tertentu boleh approve dokumen ini.
     */
    public static function canApprove(string $documentType, float $amount, string $role): bool
    {
        $rule = self::findRule($documentType, $amount);
        if (!$rule) return in_array($role, ['admin', 'pimpinan']);
        return $rule->approver_role === $role || in_array($role, ['admin', 'pimpinan']);
    }

    /**
     * Ambil semua rules terurut per document_type dan level.
     */
    public static function allGrouped(): array
    {
        return self::where('is_active', true)
            ->orderBy('document_type')
            ->orderBy('level')
            ->get()
            ->groupBy('document_type')
            ->toArray();
    }
}
