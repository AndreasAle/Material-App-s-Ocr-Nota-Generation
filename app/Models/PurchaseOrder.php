<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_no', 'project_id', 'supplier_id', 'created_by',
        'material_request_id', 'rfq_id',
        'status', 'delivery_status',
        'approved_by', 'approved_at', 'rejection_reason',
        'expected_date', 'grand_total', 'notes',
        'supplier_ref_no', 'supplier_confirmed_at', 'sent_at',
        'payment_terms', 'dp_percent', 'payment_due_days', 'delivery_terms',
        'closed_at', 'closed_by', 'close_reason',
        'budget_status', 'budget_available', 'budget_override', 'budget_override_reason',
    ];

    protected function casts(): array
    {
        return [
            'expected_date'         => 'date',
            'approved_at'           => 'datetime',
            'supplier_confirmed_at' => 'datetime',
            'sent_at'               => 'datetime',
            'closed_at'             => 'datetime',
            'grand_total'           => 'decimal:2',
            'budget_available'      => 'decimal:2',
            'budget_override'       => 'boolean',
        ];
    }

    public function project()         { return $this->belongsTo(Project::class); }
    public function supplier()        { return $this->belongsTo(Supplier::class); }
    public function creator()         { return $this->belongsTo(User::class, 'created_by'); }
    public function approver()        { return $this->belongsTo(User::class, 'approved_by'); }
    public function closedBy()        { return $this->belongsTo(User::class, 'closed_by'); }
    public function materialRequest() { return $this->belongsTo(MaterialRequest::class); }
    public function rfq()             { return $this->belongsTo(Rfq::class); }
    public function items()           { return $this->hasMany(PurchaseOrderItem::class); }

    // PO bisa punya banyak GR (partial delivery)
    public function goodsReceipts()   { return $this->hasMany(GoodsReceipt::class); }

    // PO bisa punya banyak transactions (partial invoice)
    public function transactions()
    {
        return $this->hasMany(PurchaseTransaction::class, 'purchase_order_id');
    }

    // ── Status helpers ─────────────────────────────────────────────────────
    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isSubmitted(): bool { return $this->status === 'submitted'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function isConverted(): bool { return $this->status === 'converted'; }
    public function isClosed(): bool    { return $this->status === 'closed'; }

    public function isDeliveryComplete(): bool  { return $this->delivery_status === 'complete'; }
    public function isDeliveryPartial(): bool    { return $this->delivery_status === 'partial'; }
    public function isDeliveryNotStarted(): bool { return $this->delivery_status === 'not_started'; }

    // ── Computed ───────────────────────────────────────────────────────────
    public function getQtyReceivedTotalAttribute(): float
    {
        return (float) $this->items->sum('qty_received');
    }

    public function getDeliveryPercentAttribute(): float
    {
        $ordered = (float) $this->items->sum('qty');
        return $ordered > 0 ? ($this->qty_received_total / $ordered) * 100 : 0;
    }

    public function getPaymentTermsLabelAttribute(): string
    {
        return match ($this->payment_terms) {
            'cash'  => 'Tunai',
            'net7'  => 'NET 7 Hari',
            'net14' => 'NET 14 Hari',
            'net30' => 'NET 30 Hari',
            'net45' => 'NET 45 Hari',
            'net60' => 'NET 60 Hari',
            'dp50'  => 'DP 50% + Lunas',
            'dp30'  => 'DP 30% + Lunas',
            'custom'=> 'Negosiasi',
            default => $this->payment_terms ?? '-',
        };
    }

    /** Hitung jatuh tempo berdasarkan tanggal GR terakhir + payment_due_days */
    public function computeDueDate(): ?\Carbon\Carbon
    {
        $lastGr = $this->goodsReceipts()->latest()->first();
        $baseDate = $lastGr?->receipt_date ?? now();
        return $baseDate->copy()->addDays($this->payment_due_days ?? 30);
    }
}
