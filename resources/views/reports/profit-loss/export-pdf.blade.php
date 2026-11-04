<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Laba Rugi — {{ $project->name }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1e293b; padding: 24px; }
    .header { border-bottom: 2px solid #1e40af; padding-bottom: 12px; margin-bottom: 16px; }
    .company { font-size: 20px; font-weight: 700; color: #1e40af; }
    .doc-title { font-size: 14px; font-weight: 600; margin-top: 4px; }
    .meta { font-size: 10px; color: #64748b; margin-top: 4px; }
    .kpi-row { display: flex; gap: 8px; margin-bottom: 16px; }
    .kpi { flex: 1; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; text-align: center; }
    .kpi-label { font-size: 9px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
    .kpi-value { font-size: 13px; font-weight: 700; color: #1e40af; }
    .kpi-profit { color: #15803d; }
    .kpi-loss   { color: #b91c1c; }
    .section-title { font-size: 11px; font-weight: 700; color: #1e293b; margin: 16px 0 8px; background: #f1f5f9; padding: 6px 8px; border-radius: 4px; }
    table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 12px; }
    th { background: #1e40af; color: #fff; padding: 6px 8px; text-align: left; font-weight: 600; }
    td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
    tr:nth-child(even) td { background: #f8fafc; }
    .tfoot td { font-weight: 700; border-top: 2px solid #1e40af; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .profit { color: #15803d; font-weight: 700; }
    .loss   { color: #b91c1c; font-weight: 700; }
    .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>

<div class="header">
    <div class="company">NOTAGO</div>
    <div class="doc-title">Laporan Laba Rugi — {{ $project->name }}</div>
    <div class="meta">
        Periode Tahun: {{ $year }} &nbsp;|&nbsp;
        Dicetak: {{ now()->format('d M Y, H:i') }} WIB
    </div>
</div>

<div class="kpi-row">
    <div class="kpi">
        <div class="kpi-label">Pendapatan</div>
        <div class="kpi-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Pengeluaran</div>
        <div class="kpi-value" style="color:#ea580c;">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Anggaran RAB</div>
        <div class="kpi-value" style="color:#7c3aed;">
            {{ $totalBudget > 0 ? 'Rp ' . number_format($totalBudget, 0, ',', '.') : '—' }}
        </div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Laba Kotor</div>
        <div class="kpi-value {{ $grossProfit >= 0 ? 'kpi-profit' : 'kpi-loss' }}">
            {{ $grossProfit >= 0 ? '+' : '-' }}Rp {{ number_format(abs($grossProfit), 0, ',', '.') }}
        </div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Margin</div>
        <div class="kpi-value {{ $marginPct >= 0 ? 'kpi-profit' : 'kpi-loss' }}">
            {{ number_format($marginPct, 1) }}%
        </div>
    </div>
</div>

<div class="section-title">Invoice / Pendapatan ({{ $invoices->count() }})</div>
<table>
    <thead>
        <tr>
            <th>No Invoice</th>
            <th>Klien</th>
            <th>Tanggal</th>
            <th class="text-right">Ditagihkan</th>
            <th class="text-right">Diterima</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoices as $inv)
        <tr>
            <td>{{ $inv->invoice_no }}</td>
            <td>{{ $inv->client_name }}</td>
            <td>{{ $inv->invoice_date->format('d M Y') }}</td>
            <td class="text-right">Rp {{ number_format($inv->grand_total, 0, ',', '.') }}</td>
            <td class="text-right profit">Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</td>
            <td class="text-center">{{ ucfirst($inv->payment_status) }}</td>
        </tr>
        @endforeach
        @if($invoices->isEmpty())
        <tr><td colspan="6" class="text-center" style="color:#94a3b8;padding:12px;">Tidak ada invoice</td></tr>
        @endif
    </tbody>
    <tfoot>
        <tr class="tfoot">
            <td colspan="4">TOTAL PENDAPATAN DITERIMA</td>
            <td class="text-right profit">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

<div class="section-title">Transaksi Pembelian / Pengeluaran ({{ $transactions->count() }})</div>
<table>
    <thead>
        <tr>
            <th>No Transaksi</th>
            <th>Tanggal</th>
            <th>Supplier</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $tx)
        <tr>
            <td>{{ $tx->transaction_no }}</td>
            <td>{{ $tx->transaction_date->format('d M Y') }}</td>
            <td>{{ $tx->supplier?->name ?? '-' }}</td>
            <td class="text-right" style="color:#ea580c;">Rp {{ number_format($tx->grand_total, 0, ',', '.') }}</td>
        </tr>
        @endforeach
        @if($transactions->isEmpty())
        <tr><td colspan="4" class="text-center" style="color:#94a3b8;padding:12px;">Tidak ada transaksi</td></tr>
        @endif
    </tbody>
    <tfoot>
        <tr class="tfoot">
            <td colspan="3">TOTAL PENGELUARAN</td>
            <td class="text-right" style="color:#ea580c;">Rp {{ number_format($totalCost, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight:700;padding:6px 8px;">LABA KOTOR</td>
            <td class="text-right {{ $grossProfit >= 0 ? 'profit' : 'loss' }}" style="padding:6px 8px;font-weight:700;font-size:12px;">
                {{ $grossProfit >= 0 ? '+' : '-' }}Rp {{ number_format(abs($grossProfit), 0, ',', '.') }}
                ({{ number_format($marginPct, 1) }}%)
            </td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    Dicetak otomatis oleh sistem NOTAGO &nbsp;·&nbsp; {{ now()->format('d M Y, H:i') }} WIB
</div>

</body>
</html>
