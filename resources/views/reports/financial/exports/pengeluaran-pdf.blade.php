<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengeluaran</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; background: #fff; }

        .header { padding: 16px 20px 12px; border-bottom: 2px solid #4f46e5; margin-bottom: 14px; }
        .header-flex { display: flex; justify-content: space-between; align-items: flex-start; }
        .app-name { font-size: 18px; font-weight: 700; color: #4f46e5; letter-spacing: -0.5px; }
        .report-title { font-size: 13px; font-weight: 700; color: #1e293b; margin-top: 2px; }
        .report-meta { font-size: 9px; color: #64748b; margin-top: 2px; }
        .print-date { text-align: right; font-size: 9px; color: #94a3b8; }

        .summary-bar { display: flex; gap: 12px; margin-bottom: 14px; padding: 0 20px; }
        .summary-item { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 10px; }
        .summary-label { font-size: 8px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-value { font-size: 12px; font-weight: 700; color: #1e293b; margin-top: 2px; }
        .summary-value.green { color: #059669; }

        .content { padding: 0 20px 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        thead tr { background: #4f46e5; color: white; }
        thead th { padding: 6px 8px; text-align: left; font-weight: 600; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.3px; }
        thead th.text-right { text-align: right; }
        thead th.text-center { text-align: center; }

        tbody tr { border-bottom: 1px solid #f1f5f9; }
        tbody tr:nth-child(even) { background: #fafbff; }
        tbody td { padding: 5px 8px; vertical-align: top; }
        tbody td.text-right { text-align: right; }
        tbody td.text-center { text-align: center; }

        .tx-no { font-weight: 600; color: #4f46e5; }
        .supplier { color: #475569; }
        .project { color: #475569; }
        .amount { font-weight: 700; }

        .badge { display: inline-block; padding: 1px 6px; border-radius: 20px; font-size: 8px; font-weight: 600; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-pending  { background: #fef3c7; color: #92400e; }
        .badge-draft    { background: #f1f5f9; color: #475569; }

        tfoot tr { background: #eef2ff; }
        tfoot td { padding: 6px 8px; font-weight: 700; font-size: 10px; border-top: 2px solid #4f46e5; }
        tfoot td.text-right { text-align: right; color: #4f46e5; }

        .footer { margin-top: 20px; padding-top: 8px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-flex">
            <div>
                <div class="app-name">NOTAGO</div>
                <div class="report-title">Laporan Pengeluaran</div>
                <div class="report-meta">
                    @if ($startDate || $endDate)
                        Periode:
                        @if ($startDate) {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} @else Awal @endif
                        –
                        @if ($endDate) {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }} @else Sekarang @endif
                    @else
                        Semua Periode
                    @endif
                    @if ($projectName) · Proyek: {{ $projectName }} @endif
                    @if ($supplierName) · Supplier: {{ $supplierName }} @endif
                    @if ($approvalStatus) · Status: {{ ucfirst($approvalStatus) }} @endif
                </div>
            </div>
            <div class="print-date">
                Dicetak: {{ now()->format('d M Y, H:i') }}<br>
                Oleh: {{ auth()->user()?->name ?? '—' }}
            </div>
        </div>
    </div>

    <div class="summary-bar">
        <div class="summary-item">
            <div class="summary-label">Jumlah Transaksi</div>
            <div class="summary-value">{{ $transactions->count() }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Pengeluaran</div>
            <div class="summary-value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Disetujui</div>
            <div class="summary-value green">Rp {{ number_format($totalApproved, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="content">
        <table>
            <thead>
                <tr>
                    <th style="width:4%">#</th>
                    <th style="width:12%">No. Transaksi</th>
                    <th style="width:10%">Tanggal</th>
                    <th style="width:18%">Supplier</th>
                    <th style="width:18%">Proyek</th>
                    <th style="width:12%">Input Oleh</th>
                    <th class="text-right" style="width:16%">Total (Rp)</th>
                    <th class="text-center" style="width:10%">Approval</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $i => $tx)
                    @php
                        $badgeClass = match($tx->approval_status) {
                            'approved' => 'badge-approved',
                            'rejected' => 'badge-rejected',
                            'pending'  => 'badge-pending',
                            default    => 'badge-draft',
                        };
                        $badgeLabel = match($tx->approval_status) {
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            'pending'  => 'Menunggu',
                            default    => 'Draft',
                        };
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="tx-no">{{ $tx->transaction_no }}</td>
                        <td>{{ $tx->transaction_date->format('d/m/Y') }}</td>
                        <td class="supplier">{{ $tx->supplier?->name ?? '—' }}</td>
                        <td class="project">{{ $tx->project?->name ?? '—' }}</td>
                        <td>{{ $tx->user?->name ?? '—' }}</td>
                        <td class="text-right amount">{{ number_format($tx->grand_total, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding: 20px; color: #94a3b8;">
                            Tidak ada data transaksi
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6">Total ({{ $transactions->count() }} transaksi)</td>
                    <td class="text-right">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        Dokumen ini digenerate otomatis oleh sistem NOTAGO pada {{ now()->format('d M Y H:i:s') }}
    </div>
</body>
</html>
