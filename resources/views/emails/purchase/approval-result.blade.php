@extends('emails.layouts.base')

@section('content')
@php $isApproved = $result === 'approved'; @endphp

<div class="greeting">
    @if($isApproved) ✅ Transaksi Disetujui @else ❌ Transaksi Ditolak @endif
</div>

<p class="text">
    Transaksi Pembelian <strong>{{ $transaction->transaction_no }}</strong> telah
    @if($isApproved)
        <strong style="color:#15803d;">disetujui</strong>
    @else
        <strong style="color:#b91c1c;">ditolak</strong>
    @endif
    oleh <strong>{{ $transaction->approvedBy?->name ?? 'Admin' }}</strong>.
</p>

<div class="info-card">
    <div class="info-row">
        <span class="info-label">Nomor Transaksi</span>
        <span class="info-value"><strong>{{ $transaction->transaction_no }}</strong></span>
    </div>
    @if($transaction->project)
    <div class="info-row">
        <span class="info-label">Proyek</span>
        <span class="info-value">{{ $transaction->project->name }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="info-label">Total Transaksi</span>
        <span class="info-value">Rp {{ number_format($transaction->grand_total ?? 0, 0, ',', '.') }}</span>
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
        <span class="info-value">{{ $transaction->approvedBy?->name ?? '-' }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Waktu Proses</span>
        <span class="info-value">{{ optional($transaction->approved_at)->format('d M Y, H:i') ?? '-' }} WIB</span>
    </div>
</div>

@if(!$isApproved && $transaction->rejection_reason)
<div class="alert-box alert-danger">
    <strong>Alasan Penolakan:</strong><br>
    {{ $transaction->rejection_reason }}
</div>
@endif

@if($isApproved)
<div class="alert-box alert-success">
    Transaksi Anda telah disetujui. Data pembelian akan tercatat dalam sistem.
</div>
@else
<p class="text">
    Silakan hubungi admin atau pimpinan untuk informasi lebih lanjut mengenai penolakan ini.
</p>
@endif

<div class="divider"></div>
<p style="font-size:12px;color:#94a3b8;">
    Dikirim pada {{ now()->format('d M Y, H:i') }} WIB
</p>
@endsection
