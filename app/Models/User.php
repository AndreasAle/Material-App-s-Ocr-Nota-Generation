<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'whatsapp_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function purchaseTransactions()
    {
        return $this->hasMany(PurchaseTransaction::class);
    }

    public function uploadedAttachments()
    {
        return $this->hasMany(PurchaseAttachment::class, 'uploaded_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    public function isPurchasing(): bool
    {
        return $this->role === 'purchasing';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    /** Label role; di Mode Toko admin disebut Pemilik. */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'admin'      => config('store.mode') ? 'Pemilik' : 'Admin',
            'pimpinan'   => 'Pimpinan',
            'purchasing' => 'Purchasing',
            'kasir'      => 'Kasir',
            default      => 'Staff',
        };
    }

    /** True jika boleh lihat laporan keuangan (admin atau pimpinan saja) */
    public function canViewFinancials(): bool
    {
        return in_array($this->role, ['admin', 'pimpinan']);
    }

    /** True jika bisa approve dokumen */
    public function canApprove(): bool
    {
        return in_array($this->role, ['admin', 'pimpinan']);
    }
}