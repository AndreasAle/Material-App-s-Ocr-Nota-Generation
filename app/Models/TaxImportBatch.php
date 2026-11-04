<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxImportBatch extends Model
{
    protected $table = 'tax_import_batches';

    protected $fillable = [
        'jenis',
        'npwp_pemotong',
        'nama_pemotong',
        'filename',
        'masa_pajak',
        'total_rows',
        'success_rows',
        'error_rows',
        'duplicate_rows',
        'status',
        'errors',
        'preview_data',
        'created_by',
    ];

    protected $casts = [
        'errors'       => 'array',
        'preview_data' => 'array',
        'total_rows'   => 'integer',
        'success_rows' => 'integer',
        'error_rows'   => 'integer',
        'duplicate_rows' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pphFinalRecords(): HasMany
    {
        return $this->hasMany(TaxPphFinalRecord::class, 'import_batch_id');
    }

    public function pph23Records(): HasMany
    {
        return $this->hasMany(TaxPph23Record::class, 'import_batch_id');
    }

    public function bp21Records(): HasMany
    {
        return $this->hasMany(TaxBp21Record::class, 'import_batch_id');
    }

    public function getLabelJenis(): string
    {
        return match($this->jenis) {
            'bppu'              => 'BPPU (PPh Unifikasi)',
            'dok_lain_masukan'  => 'Dok Lain PPN Masukan',
            'dok_lain_keluaran' => 'Dok Lain PPN Keluaran',
            'bp21'              => 'BP21 (PPh 21)',
            default             => $this->jenis,
        };
    }

    public function isCommitted(): bool
    {
        return $this->status === 'committed';
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows === 0) return 0.0;
        return round($this->success_rows / $this->total_rows * 100, 1);
    }
}
