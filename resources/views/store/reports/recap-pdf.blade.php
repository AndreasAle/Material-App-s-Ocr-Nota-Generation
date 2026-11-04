@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
        h1 { font-size: 16px; margin: 0; } .muted { color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 4px 6px; text-align: left; }
        th { background: #f1f5f9; } .r { text-align: right; }
        .grid td { border: 1px solid #e2e8f0; padding: 8px; width: 25%; }
        .big { font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Rekap Toko — {{ config('store.name') }}</h1>
    <p class="muted">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($to)->format('d M Y') }} · dicetak {{ now()->format('d/m/Y H:i') }}</p>

    <table class="grid">
        <tr>
            <td>Omzet bersih<br><span class="big">{{ $rp($summary['omzet']) }}</span><br><span class="muted">{{ $summary['transactions'] }} transaksi</span></td>
            <td>Laba kotor<br><span class="big">{{ $rp($summary['gross_profit']) }}</span><br><span class="muted">Margin {{ $summary['margin_pct'] }}%</span></td>
            <td>Uang masuk<br><span class="big">{{ $rp($summary['cash_in']) }}</span></td>
            <td>Uang keluar<br><span class="big">{{ $rp($summary['cash_out']) }}</span></td>
        </tr>
        <tr>
            <td>HPP<br><b>{{ $rp($summary['hpp']) }}</b></td>
            <td>Kulakan<br><b>{{ $rp($summary['purchases']) }}</b></td>
            <td>Bon pelanggan<br><b>{{ $rp($summary['receivable_total']) }}</b></td>
            <td>Hutang supplier<br><b>{{ $rp($summary['payable_total']) }}</b></td>
        </tr>
    </table>

    <h3>Per hari</h3>
    <table>
        <thead><tr><th>Tanggal</th><th class="r">Trx</th><th class="r">Omzet</th><th class="r">Laba</th><th class="r">Masuk</th><th class="r">Keluar</th></tr></thead>
        <tbody>
        @foreach($daily as $d)
            <tr><td>{{ \Carbon\Carbon::parse($d['date'])->format('d/m/Y') }}</td><td class="r">{{ $d['count'] }}</td><td class="r">{{ $rp($d['omzet']) }}</td><td class="r">{{ $rp($d['profit']) }}</td><td class="r">{{ $rp($d['cash_in']) }}</td><td class="r">{{ $rp($d['cash_out']) }}</td></tr>
        @endforeach
        </tbody>
    </table>

    <h3>Barang terlaris</h3>
    <table>
        <thead><tr><th>#</th><th>Barang</th><th class="r">Qty</th><th class="r">Omzet</th><th class="r">Laba</th></tr></thead>
        <tbody>
        @foreach($top as $i => $t)
            <tr><td>{{ $i + 1 }}</td><td>{{ $t['name'] }}</td><td class="r">{{ $qty($t['qty_base']) }}</td><td class="r">{{ $rp($t['omzet']) }}</td><td class="r">{{ $rp($t['profit']) }}</td></tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
