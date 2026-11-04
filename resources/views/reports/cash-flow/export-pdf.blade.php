<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 20px; }
    h1 { font-size: 18px; font-weight: bold; margin-bottom: 2px; }
    .subtitle { color: #64748b; font-size: 11px; margin-bottom: 16px; }
    .header-bar { border-bottom: 2px solid #6366f1; padding-bottom: 10px; margin-bottom: 16px; }
    .kpi-row { display: flex; gap: 12px; margin-bottom: 20px; }
    .kpi { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; }
    .kpi-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; }
    .kpi-val { font-size: 14px; font-weight: bold; margin-top: 4px; }
    .kpi-in .kpi-val { color: #059669; }
    .kpi-out .kpi-val { color: #e11d48; }
    .kpi-net .kpi-val { color: #4f46e5; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f1f5f9; font-size: 10px; padding: 8px 10px; text-align: left; border: 1px solid #e2e8f0; }
    td { padding: 7px 10px; border: 1px solid #e2e8f0; font-size: 10px; }
    tr:nth-child(even) td { background: #f8fafc; }
    .text-right { text-align: right; }
    .positive { color: #059669; font-weight: 600; }
    .negative { color: #e11d48; font-weight: 600; }
    .total-row td { background: #e2e8f0 !important; font-weight: bold; }
    .footer { margin-top: 20px; font-size: 9px; color: #94a3b8; text-align: right; }
</style>
</head>
<body>
<div class="header-bar">
    <h1>LAPORAN ARUS KAS</h1>
    <div class="subtitle">
        NOTAGO — Sistem RAB Material
        @if($project) | Proyek: {{ $project->name }} @endif
        | Dicetak: {{ now()->translatedFormat('d F Y') }}
    </div>
</div>

<div class="kpi-row">
    <div class="kpi kpi-in">
        <div class="kpi-label">Total Cash In</div>
        <div class="kpi-val">Rp {{ number_format($totalIn, 0, ',', '.') }}</div>
    </div>
    <div class="kpi kpi-out">
        <div class="kpi-label">Total Cash Out</div>
        <div class="kpi-val">Rp {{ number_format($totalOut, 0, ',', '.') }}</div>
    </div>
    <div class="kpi kpi-net">
        <div class="kpi-label">Net Cash Flow</div>
        <div class="kpi-val">{{ ($totalIn - $totalOut) >= 0 ? '+' : '' }}Rp {{ number_format($totalIn - $totalOut, 0, ',', '.') }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Periode</th>
            <th class="text-right">Cash In (Invoice)</th>
            <th class="text-right">Cash Out (Pembelian)</th>
            <th class="text-right">Net</th>
            <th class="text-right">Saldo Kumulatif</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tableRows as $row)
        <tr>
            <td>{{ $row['label'] }}</td>
            <td class="text-right positive">Rp {{ number_format($row['in'], 0, ',', '.') }}</td>
            <td class="text-right negative">Rp {{ number_format($row['out'], 0, ',', '.') }}</td>
            <td class="text-right {{ $row['net'] >= 0 ? 'positive' : 'negative' }}">
                {{ $row['net'] >= 0 ? '+' : '' }}Rp {{ number_format($row['net'], 0, ',', '.') }}
            </td>
            <td class="text-right {{ $row['runningBal'] >= 0 ? 'positive' : 'negative' }}">
                Rp {{ number_format($row['runningBal'], 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td>TOTAL</td>
            <td class="text-right positive">Rp {{ number_format($totalIn, 0, ',', '.') }}</td>
            <td class="text-right negative">Rp {{ number_format($totalOut, 0, ',', '.') }}</td>
            <td class="text-right {{ ($totalIn - $totalOut) >= 0 ? 'positive' : 'negative' }}">
                {{ ($totalIn - $totalOut) >= 0 ? '+' : '' }}Rp {{ number_format($totalIn - $totalOut, 0, ',', '.') }}
            </td>
            <td class="text-right">—</td>
        </tr>
    </tbody>
</table>

<div class="footer">Laporan ini dibuat otomatis oleh NOTAGO Sistem RAB Material</div>
</body>
</html>
