@extends('emails.layouts.base')

@section('content')
<div class="greeting">⚠️ Peringatan: Stok Material Menipis</div>

<p class="text">
    Sistem mendeteksi <strong>{{ count($lowStockItems) }} material</strong> yang stoknya sudah berada di bawah
    batas minimum yang ditetapkan. Segera lakukan pembelian atau penyesuaian stok.
</p>

<div class="alert-box alert-warning">
    Berikut adalah daftar material yang perlu segera diperhatikan:
</div>

<table style="margin-top:16px;">
    <thead>
        <tr>
            <th>Material</th>
            <th style="text-align:right;">Stok Saat Ini</th>
            <th style="text-align:right;">Minimum</th>
            <th style="text-align:right;">Kekurangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($lowStockItems as $item)
        @php
            $shortage = $item['minimum_stock'] - $item['current_stock'];
            $pct = $item['minimum_stock'] > 0
                ? round(($item['current_stock'] / $item['minimum_stock']) * 100)
                : 0;
        @endphp
        <tr>
            <td>
                <strong>{{ $item['material']->name }}</strong><br>
                <span style="font-size:12px;color:#64748b;">{{ $item['material']->unit?->name ?? '-' }}</span>
            </td>
            <td style="text-align:right;color:#b91c1c;font-weight:600;">
                {{ number_format($item['current_stock'], 2) }}
            </td>
            <td style="text-align:right;color:#64748b;">
                {{ number_format($item['minimum_stock'], 2) }}
            </td>
            <td style="text-align:right;color:#dc2626;font-weight:700;">
                {{ number_format($shortage, 2) }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="divider"></div>

<p class="text">
    Login ke sistem NOTAGO → menu <strong>Stok & Inventori</strong> untuk melakukan adjustment atau buat Purchase Order baru.
</p>

<div class="divider"></div>
<p style="font-size:12px;color:#94a3b8;">
    Dikirim otomatis pada {{ now()->format('d M Y, H:i') }} WIB
</p>
@endsection
