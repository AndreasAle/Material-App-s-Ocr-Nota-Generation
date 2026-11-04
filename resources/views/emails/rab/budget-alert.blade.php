@extends('emails.layouts.base')

@section('content')
@php
    $pct = round($utilizationPercent, 1);
    $isCritical = $pct >= 90;
    $remaining = $totalBudget - $totalActual;
@endphp

<div class="greeting">
    @if($isCritical) 🚨 Anggaran RAB Hampir Habis @else ⚠️ Peringatan Anggaran RAB @endif
</div>

<p class="text">
    RAB <strong>{{ $rabPlan->name }}</strong>
    @if($rabPlan->project)untuk proyek <strong>{{ $rabPlan->project->name }}</strong>@endif
    telah terpakai sebesar <strong style="color:{{ $isCritical ? '#b91c1c' : '#a16207' }};">{{ $pct }}%</strong>
    dari total anggaran yang ditetapkan.
</p>

<div class="info-card">
    <div class="info-row">
        <span class="info-label">Nama RAB</span>
        <span class="info-value"><strong>{{ $rabPlan->name }}</strong></span>
    </div>
    @if($rabPlan->project)
    <div class="info-row">
        <span class="info-label">Proyek</span>
        <span class="info-value">{{ $rabPlan->project->name }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="info-label">Total Anggaran</span>
        <span class="info-value">Rp {{ number_format($totalBudget, 0, ',', '.') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Total Realisasi</span>
        <span class="info-value" style="color:#b91c1c;">Rp {{ number_format($totalActual, 0, ',', '.') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Sisa Anggaran</span>
        <span class="info-value" style="color:{{ $remaining < 0 ? '#b91c1c' : '#15803d' }};font-weight:700;">
            @if($remaining < 0)
                - Rp {{ number_format(abs($remaining), 0, ',', '.') }} (OVER BUDGET)
            @else
                Rp {{ number_format($remaining, 0, ',', '.') }}
            @endif
        </span>
    </div>
    <div class="info-row">
        <span class="info-label">Persentase Terpakai</span>
        <span class="info-value" style="color:{{ $isCritical ? '#b91c1c' : '#a16207' }};font-weight:700;">{{ $pct }}%</span>
    </div>
    @if($rabPlan->period_start)
    <div class="info-row">
        <span class="info-label">Periode</span>
        <span class="info-value">
            {{ $rabPlan->period_start->format('d M Y') }}
            @if($rabPlan->period_end) — {{ $rabPlan->period_end->format('d M Y') }} @endif
        </span>
    </div>
    @endif
</div>

<div style="margin:16px 0;">
    <div style="font-size:12px;color:#64748b;margin-bottom:6px;">Tingkat Penggunaan Anggaran</div>
    <div class="progress-bar-wrap">
        <div class="progress-bar-fill" style="width:{{ min($pct, 100) }}%;background:{{ $isCritical ? '#dc2626' : ($pct >= 70 ? '#f59e0b' : '#22c55e') }};"></div>
    </div>
    <div style="font-size:12px;color:#64748b;margin-top:4px;text-align:right;">{{ $pct }}% dari 100%</div>
</div>

@if($isCritical)
<div class="alert-box alert-danger">
    🚨 Anggaran hampir atau sudah habis! Tinjau kembali pengeluaran dan pertimbangkan revisi RAB atau efisiensi pembelian.
</div>
@else
<div class="alert-box alert-warning">
    ⚠️ Penggunaan anggaran sudah melebihi 80%. Monitor lebih ketat dan hindari pembelian yang tidak prioritas.
</div>
@endif

<p class="text">
    Login ke sistem NOTAGO → menu <strong>RAB & Anggaran</strong> untuk melihat detail realisasi per item.
</p>

<div class="divider"></div>
<p style="font-size:12px;color:#94a3b8;">
    Dikirim otomatis pada {{ now()->format('d M Y, H:i') }} WIB
</p>
@endsection
