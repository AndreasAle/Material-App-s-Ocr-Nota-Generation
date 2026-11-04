@extends('emails.layouts.base')

@section('content')
@php $isOverdue = $isOverdue ?? false; @endphp

<div class="greeting">
    @if($isOverdue) ⚠️ Invoice Melewati Jatuh Tempo @else 🔔 Pengingat Jatuh Tempo Invoice @endif
</div>

<p class="text">
    @if($isOverdue)
        Invoice <strong>{{ $invoice->invoice_no }}</strong> telah <strong style="color:#b91c1c;">melewati tanggal jatuh tempo</strong>
        pada <strong>{{ $invoice->due_date->format('d M Y') }}</strong> dan belum lunas.
        Segera lakukan pembayaran untuk menghindari keterlambatan lebih lanjut.
    @else
        Invoice <strong>{{ $invoice->invoice_no }}</strong> akan <strong>jatuh tempo besok</strong>
        pada <strong>{{ $invoice->due_date->format('d M Y') }}</strong>.
        Harap segera lakukan pembayaran.
    @endif
</p>

<div class="info-card">
    <div class="info-row">
        <span class="info-label">Nomor Invoice</span>
        <span class="info-value"><strong>{{ $invoice->invoice_no }}</strong></span>
    </div>
    <div class="info-row">
        <span class="info-label">Klien</span>
        <span class="info-value">{{ $invoice->client_name }}</span>
    </div>
    @if($invoice->project)
    <div class="info-row">
        <span class="info-label">Proyek</span>
        <span class="info-value">{{ $invoice->project->name }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="info-label">Tanggal Invoice</span>
        <span class="info-value">{{ $invoice->invoice_date->format('d M Y') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Jatuh Tempo</span>
        <span class="info-value" @if($isOverdue) style="color:#b91c1c;font-weight:700;" @endif>
            {{ $invoice->due_date->format('d M Y') }}
        </span>
    </div>
    <div class="info-row">
        <span class="info-label">Total Tagihan</span>
        <span class="info-value">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Sudah Dibayar</span>
        <span class="info-value" style="color:#15803d;">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Sisa Tagihan</span>
        <span class="info-value" style="color:#b91c1c;font-weight:700;">
            Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}
        </span>
    </div>
    <div class="info-row">
        <span class="info-label">Status</span>
        <span class="info-value">
            @if($invoice->isPaid())
                <span class="badge badge-success">Lunas</span>
            @elseif($invoice->isPartiallyPaid())
                <span class="badge badge-warning">Bayar Sebagian</span>
            @else
                <span class="badge badge-danger">Belum Dibayar</span>
            @endif
        </span>
    </div>
</div>

@if($isOverdue)
<div class="alert-box alert-danger">
    ⚠️ Invoice ini sudah melewati jatuh tempo. Segera hubungi klien untuk menyelesaikan pembayaran.
</div>
@else
<div class="alert-box alert-warning">
    🕐 Besok adalah batas terakhir pembayaran. Pastikan klien telah menyelesaikan pembayarannya.
</div>
@endif

<p class="text">
    Login ke sistem NOTAGO untuk melihat detail invoice dan mencatat pembayaran.
</p>

<div class="divider"></div>
<p style="font-size:12px;color:#94a3b8;">
    Dikirim pada {{ now()->format('d M Y, H:i') }} WIB
</p>
@endsection
