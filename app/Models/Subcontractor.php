<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcontractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'contact_person', 'phone', 'email',
        'address', 'specialization', 'notes', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subcontracts(): HasMany
    {
        return $this->hasMany(Subcontract::class);
    }

    public function activeSubcontracts(): HasMany
    {
        return $this->hasMany(Subcontract::class)->where('status', 'active');
    }

    public function getTotalContractValueAttribute(): float
    {
        return (float) $this->subcontracts()->sum('contract_value');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->subcontracts()->sum('paid_amount');
    }
}
