@extends('emails.layouts.base')

@section('content')
@php
    $isApproved = $result === 'approved';
@endphp

<div class="greeting">
    @if($isApproved) ✅ Purchase Order Disetujui @else ❌ Purchase Order Ditolak @endif
</div>

<p class="text">
    Purchase Order <strong>{{ $po->po_no }}</strong> telah
    @if($isApproved)
        <strong style="color:#15803d;">disetujui</strong> oleh
    @else
        <strong style="color:#b91c1c;">ditolak</strong> oleh
    @endif
    <strong>{{ $po->approver?->name ?? 'Admin' }}</strong>
    pada {{ $po->approved_at?->format('d M Y, H:i') }} WIB.
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
        <span class="info-label">Status</span>
        <span class="info-value">
            @if($isApproved)
                <span class="badge badge-success">Disetujui</span>
            @else
                <span class="badge badge-danger">Ditolak</span>
            @endif
        </span>
    </div>
    <div class="info-row">
        <span class="info-label">Diproses Oleh</span>
        <span class="info-value">{{ $po->approver?->name ?? '-' }}</span>
    </div>
</div>

@if(!$isApproved && $po->rejection_reason)
<div class="alert-box alert-danger">
    <strong>Alasan Penolakan:</strong><br>
    {{ $po->rejection_reason }}
</div>
@endif

@if($isApproved)
<div class="alert-box alert-success">
    PO ini telah disetujui. Anda bisa melanjutkan ke langkah berikutnya: konversi PO menjadi Transaksi Pembelian melalui sistem NOTAGO.
</div>
@else
<p class="text">
    Silakan login ke sistem untuk merevisi PO atau membuat PO baru sesuai masukan dari approver.
</p>
@endif

<div class="divider"></div>
<p style="font-size:12px;color:#94a3b8;">
    Dikirim pada {{ now()->format('d M Y, H:i') }} WIB
</p>
@endsection
