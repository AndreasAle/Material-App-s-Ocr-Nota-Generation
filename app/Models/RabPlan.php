<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RabPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'created_by',
        'name',
        'period_start',
        'period_end',
        'description',
        'status',
        'total_budget',
        // versioning
        'version',
        'original_plan_id',
        'is_latest_version',
        // template
        'is_template',
        'template_name',
    ];

    protected function casts(): array
    {
        return [
            'period_start'      => 'date',
            'period_end'        => 'date',
            'total_budget'      => 'decimal:2',
            'is_latest_version' => 'boolean',
            'is_template'       => 'boolean',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(RabPlanItem::class);
    }

    /** Versi asal (jika ini adalah revisi) */
    public function originalPlan()
    {
        return $this->belongsTo(RabPlan::class, 'original_plan_id');
    }

    /** Semua revisi dari RAB asal ini */
    public function revisions()
    {
        return $this->hasMany(RabPlan::class, 'original_plan_id');
    }

    /** Semua versi (asal + revisi) untuk proyek & RAB ini */
    public function allVersions()
    {
        $originId = $this->original_plan_id ?? $this->id;
        return RabPlan::where(fn ($q) => $q->where('id', $originId)->orWhere('original_plan_id', $originId))
            ->orderBy('version')
            ->get();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
