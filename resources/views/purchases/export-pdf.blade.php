<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Pembelian Material</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .muted { color: #6b7280; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 8px; }
        th { background: #f3f4f6; text-align: left; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Rekap Pembelian Material</h1>
    <div class="muted">
        Status: {{ $filters['status'] ?: 'Semua' }} |
        Periode: {{ $filters['start_date'] ?: '-' }} s/d {{ $filters['end_date'] ?: '-' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Transaksi</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Proyek</th>
                <th>No Nota</th>
                <th>Status</th>
                <th class="right">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; $grand = 0; @endphp
            @forelse($transactions as $trx)
                @php $grand += (float) $trx->grand_total; @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $trx->transaction_no }}</td>
                    <td>{{ optional($trx->transaction_date)->format('d-m-Y') }}</td>
                    <td>{{ $trx->supplier?->name ?: '-' }}</td>
                    <td>{{ $trx->project?->name ?: '-' }}</td>
                    <td>{{ $trx->note_number ?: '-' }}</td>
                    <td>{{ strtoupper($trx->status) }}</td>
                    <td class="right">Rp {{ number_format($trx->grand_total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7" class="right">Total</th>
                <th class="right">Rp {{ number_format($grand, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>