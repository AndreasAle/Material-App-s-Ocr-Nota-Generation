<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'description',
        'is_active',
        // Contract fields (added in 2026_05_30 migration)
        'client_name',
        'start_date',
        'end_date',
        'contract_value',
        'status',
        'progress_pct',
    ];

    protected function casts(): array
    {
        return [
            'is_active'      => 'boolean',
            'start_date'     => 'date',
            'end_date'       => 'date',
            'contract_value' => 'decimal:2',
            'progress_pct'   => 'integer',
        ];
    }

    public function purchaseTransactions()
    {
        return $this->hasMany(PurchaseTransaction::class);
    }

    public function rabPlans()
    {
        return $this->hasMany(\App\Models\RabPlan::class);
    }

    public function subcontracts()
    {
        return $this->hasMany(\App\Models\Subcontract::class);
    }

    public function documents()
    {
        return $this->hasMany(\App\Models\ProjectDocument::class);
    }
}