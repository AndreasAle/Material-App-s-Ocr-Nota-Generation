@extends('emails.layouts.base')

@section('content')
<div class="greeting">Halo, {{ $approver->name }} 👋</div>
<p class="text">
    Ada Transaksi Pembelian baru yang membutuhkan persetujuan Anda.
    Silakan tinjau detail di bawah dan berikan keputusan Anda sesegera mungkin.
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
    @if($transaction->supplier)
    <div class="info-row">
        <span class="info-label">Supplier</span>
        <span class="info-value">{{ $transaction->supplier->name }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="info-label">Total Transaksi</span>
        <span class="info-value">Rp {{ number_format($transaction->grand_total ?? 0, 0, ',', '.') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Dibuat Oleh</span>
        <span class="info-value">{{ $transaction->creator?->name ?? $transaction->user?->name ?? '-' }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Tanggal Transaksi</span>
        <span class="info-value">{{ optional($transaction->transaction_date)->format('d M Y') ?? '-' }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Status</span>
        <span class="info-value"><span class="badge badge-warning">Menunggu Persetujuan</span></span>
    </div>
</div>

<p class="text">
    Login ke sistem NOTAGO untuk menyetujui atau menolak transaksi ini.
</p>

<div class="divider"></div>
<p style="font-size:12px;color:#94a3b8;">
    Dikirim pada {{ now()->format('d M Y, H:i') }} WIB
</p>
@endsection
