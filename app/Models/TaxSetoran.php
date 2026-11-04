<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxSetoran extends Model
{
    protected $table = 'tax_setoran';

    protected $fillable = [
        'periode',
        'jenis_pajak',
        'kap',
        'kjs',
        'uraian',
        'jumlah',
        'jatuh_tempo',
        'status',
        'tanggal_setor',
        'ntpn',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'jatuh_tempo'    => 'date',
        'tanggal_setor'  => 'date',
        'jumlah'         => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'belum_bayar' && $this->jatuh_tempo->isPast();
    }

    public function getLabelJenisPajak(): string
    {
        return match($this->jenis_pajak) {
            'ppn'           => 'PPN Masa',
            'pph21'         => 'PPh Pasal 21',
            'pph23'         => 'PPh Pasal 23',
            'pph_final_4_2' => 'PPh Final Ps. 4(2)',
            default         => $this->jenis_pajak,
        };
    }

    public function getBadgeStatus(): string
    {
        if ($this->status === 'sudah_bayar') return 'badge-success';
        if ($this->isOverdue())               return 'badge-danger';
        return 'badge-warning';
    }

    public function getLabelStatus(): string
    {
        if ($this->status === 'sudah_bayar') return 'Lunas';
        if ($this->isOverdue())               return 'Jatuh Tempo';
        return 'Belum Bayar';
    }
}
