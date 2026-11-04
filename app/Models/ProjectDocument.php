<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'title', 'file_path', 'file_name',
        'file_size', 'file_mime', 'category', 'description', 'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    /**
     * Upload baru disimpan di disk privat; file lama (sebelum migrasi) masih di disk public.
     */
    public function storageDisk(): string
    {
        return Storage::disk('private')->exists($this->file_path) ? 'private' : 'public';
    }

    public function fileExists(): bool
    {
        return Storage::disk($this->storageDisk())->exists($this->file_path);
    }

    public function getFileUrlAttribute(): string
    {
        return route('documents.preview', $this);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'rab'     => 'RAB',
            'spk'     => 'SPK / Kontrak',
            'laporan' => 'Laporan',
            'gambar'  => 'Gambar / Denah',
            'invoice' => 'Invoice',
            default   => 'Umum',
        };
    }

    public function getCategoryIconAttribute(): string
    {
        return match($this->category) {
            'rab'     => '📋',
            'spk'     => '📝',
            'laporan' => '📊',
            'gambar'  => '🗺️',
            'invoice' => '🧾',
            default   => '📄',
        };
    }
}
