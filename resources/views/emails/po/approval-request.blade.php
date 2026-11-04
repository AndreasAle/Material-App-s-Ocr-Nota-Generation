@extends('emails.layouts.base')

@section('content')
<div class="greeting">Halo, {{ $approver->name }} 👋</div>
<p class="text">
    Ada Purchase Order baru yang membutuhkan persetujuan Anda.
    Silakan tinjau detail di bawah ini dan berikan keputusan Anda.
</p>

<div class="info-card">
    <div class="info-row">
        <span class="info-label">Nomor PO</span>
        <span class="info-value"><strong>{{ $po->po_no }}</strong></span>
    </div>
    @if($po->project)
    <div class="info-row">
        <span class="info-label">Proyek</span>
        <span class="info-value">{{ $po->project->name }}</span>
    </div>
    @endif
    @if($po->supplier)
    <div class="info-row">
        <span class="info-label">Supplier</span>
        <span class="info-value">{{ $po->supplier->name }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="info-label">Total Nilai</span>
        <span class="info-value">Rp {{ number_format($po->grand_total, 0, ',', '.') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Dibuat Oleh</span>
        <span class="info-value">{{ $po->creator?->name ?? '-' }}</span>
    </div>
    @if($po->expected_date)
    <div class="info-row">
        <span class="info-label">Tanggal Estimasi</span>
        <span class="info-value">{{ $po->expected_date->format('d M Y') }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="info-label">Jumlah Item</span>
        <span class="info-value">{{ $po->items->count() }} item</span>
    </div>
    <div class="info-row">
        <span class="info-label">Status</span>
        <span class="info-value"><span class="badge badge-warning">Menunggu Persetujuan</span></span>
    </div>
</div>

@if($po->notes)
<div class="alert-box alert-warning">
    <strong>Catatan:</strong> {{ $po->notes }}
</div>
@endif

<p class="text">
    Silakan login ke sistem NOTAGO untuk melakukan tindakan Approve atau Reject terhadap PO ini.
</p>

<div class="divider"></div>
<p style="font-size:12px;color:#94a3b8;">
    Dikirim pada {{ now()->format('d M Y, H:i') }} WIB
</p>
@endsection
