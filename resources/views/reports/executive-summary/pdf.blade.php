<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Executive Summary</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #1e293b; background: #fff; padding: 18px; }
    .header { border-bottom: 2px solid #6366f1; padding-bottom: 10px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; }
    .header h1 { font-size: 16px; font-weight: 800; color: #4f46e5; }
    .header p { font-size: 9px; color: #64748b; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 8px; font-weight: 700; }
    .kpi-grid { display: flex; gap: 8px; margin-bottom: 14px; }
    .kpi-box { flex: 1; border-radius: 10px; padding: 8px; border: 1px solid #e2e8f0; background: #f8fafc; }
    .kpi-box .label { font-size: 7.5px; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin-bottom: 3px; }
    .kpi-box .value { font-size: 11px; font-weight: 800; color: #0f172a; }
    .section { margin-bottom: 12px; }
    .section-title { font-size: 9px; font-weight: 800; text-transform: uppercase; color: #4f46e5; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; margin-bottom: 6px; letter-spacing: 0.08em; }
    table { width: 100%; border-collapse: collapse; font-size: 9px; }
    th { background: #f1f5f9; padding: 4px 6px; text-align: left; font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 7.5px; }
    td { padding: 4px 6px; border-bottom: 1px solid #f1f5f9; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .prog-bar-bg { background: #e2e8f0; border-radius: 4px; height: 5px; width: 100%; }
    .prog-bar { border-radius: 4px; height: 5px; }
    .two-col { display: flex; gap: 10px; }
    .two-col > div { flex: 1; }
    .footer { border-top: 1px solid #e2e8f0; padding-top: 6px; margin-top: 10px; font-size: 7.5px; color: #94a3b8; display: flex; justify-content: space-between; }
</style>
</head>
<body>

<div class="header">
    <div>
        <h1>Executive Summary</h1>
        <p>{{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }} &nbsp;|&nbsp; Dicetak: {{ now()->format('d M Y H:i') }}</p>
    </div>
    <div style="text-align: right;">
        <p style="font-size:11px; font-weight:800; color:#64748b;">NOTAGO</p>
        <p style="font-size:8px; color:#94a3b8;">Sistem RAB Material</p>
    </div>
</div>

{{-- KPIs --}}
<div class="kpi-grid">
    <div class="kpi-box">
        <div class="label">Pengeluaran Bulan</div>
        <div class="value" style="color:#e11d48;">Rp {{ number_format($spendingMonth, 0, ',', '.') }}</div>
        <div style="font-size:7.5px;color:#94a3b8;">YTD: Rp {{ number_format($spendingYtd, 0, ',', '.') }}</div>
    </div>
    <div class="kpi-box">
        <div class="label">Revenue Bulan</div>
        <div class="value" style="color:#059669;">Rp {{ number_format($revenueMonth, 0, ',', '.') }}</div>
        <div style="font-size:7.5px;color:#94a3b8;">YTD: Rp {{ number_format($revenueYtd, 0, ',', '.') }}</div>
    </div>
    <div class="kpi-box">
        <div class="label">Net Bulan</div>
        <div class="value" style="color: {{ $netMonth >= 0 ? '#059669' : '#e11d48' }};">
            {{ $netMonth >= 0 ? '+' : '' }}Rp {{ number_format($netMonth, 0, ',', '.') }}
        </div>
    </div>
    <div class="kpi-box">
        <div class="label">Stok Kritis</div>
        <div class="value" style="color:{{ $lowStock->count() > 0 ? '#d97706' : '#059669' }};">{{ $lowStock->count() }} material</div>
    </div>
    <div class="kpi-box">
        <div class="label">Invoice Overdue</div>
        <div class="value" style="color:{{ $overdueInvoices->count() > 0 ? '#e11d48' : '#059669' }};">{{ $overdueInvoices->count() }} invoice</div>
    </div>
</div>

<div class="two-col">
    {{-- RAB Utilization --}}
    <div class="section">
        <div class="section-title">RAB Aktif — Utilisasi</div>
        @if($rabSummary->isEmpty())
        <p style="color:#94a3b8;font-size:8.5px;">Tidak ada RAB aktif.</p>
        @else
        @foreach($rabSummary as $r)
        <div style="margin-bottom:5px;">
            <div style="display:flex;justify-content:space-between;">
                <span style="font-size:8.5px;font-weight:600;">{{ $r['name'] }}</span>
                <span style="font-size:8.5px;font-weight:700;color:{{ $r['pct'] > 90 ? '#e11d48' : ($r['pct'] > 70 ? '#d97706' : '#059669') }};">{{ $r['pct'] }}%</span>
            </div>
            <div class="prog-bar-bg">
                <div class="prog-bar" style="width:{{ min($r['pct'],100) }}%;background:{{ $r['pct'] > 90 ? '#f43f5e' : ($r['pct'] > 70 ? '#f59e0b' : '#10b981') }};"></div>
            </div>
        </div>
        @endforeach
        @endif
    </div>

    {{-- Top Supplier --}}
    <div class="section">
        <div class="section-title">Top Supplier Bulan Ini</div>
        @if($topSuppliers->isEmpty())
        <p style="color:#94a3b8;font-size:8.5px;">Tidak ada transaksi.</p>
        @else
        <table>
            <thead><tr><th>#</th><th>Supplier</th><th class="text-right">Total</th></tr></thead>
            <tbody>
                @foreach($topSuppliers as $i => $s)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $s->name }}</td>
                    <td class="text-right">Rp {{ number_format($s->total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

<div class="two-col">
    {{-- Stok Kritis --}}
    <div class="section">
        <div class="section-title">⚠ Stok Material Kritis</div>
        @if($lowStock->isEmpty())
        <p style="color:#059669;font-size:8.5px;">Semua stok aman ✓</p>
        @else
        <table>
            <thead><tr><th>Material</th><th class="text-right">Stok</th><th class="text-right">Min</th></tr></thead>
            <tbody>
                @foreach($lowStock as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td class="text-right" style="color:#e11d48;font-weight:700;">{{ $s->stock }}</td>
                    <td class="text-right">{{ $s->minimum_stock }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- SPK Aktif --}}
    <div class="section">
        <div class="section-title">👷 SPK Aktif</div>
        @if($activeSpk->isEmpty())
        <p style="color:#94a3b8;font-size:8.5px;">Tidak ada SPK aktif.</p>
        @else
        <table>
            <thead><tr><th>Pekerjaan</th><th>Subkon</th><th class="text-right">Progress</th></tr></thead>
            <tbody>
                @foreach($activeSpk as $spk)
                <tr>
                    <td>{{ Str::limit($spk->work_name, 28) }}</td>
                    <td style="color:#64748b;">{{ Str::limit($spk->subcontractor->name ?? '—', 18) }}</td>
                    <td class="text-right">{{ $spk->progress_pct }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

<div class="footer">
    <span>Digenerate otomatis oleh Sistem RAB Material</span>
    <span>Halaman 1/1</span>
</div>

</body>
</html>
