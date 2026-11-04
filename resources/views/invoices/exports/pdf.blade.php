<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; background: #fff; }

        .page { padding: 28px 32px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
        .brand { }
        .brand-name { font-size: 22px; font-weight: 700; color: #4f46e5; letter-spacing: -0.5px; }
        .brand-sub  { font-size: 9px; color: #94a3b8; margin-top: 1px; }
        .invoice-badge { text-align: right; }
        .invoice-label { font-size: 18px; font-weight: 700; color: #1e293b; }
        .invoice-no    { font-size: 12px; color: #4f46e5; font-weight: 600; margin-top: 2px; }

        /* Status badge */
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; margin-top: 4px; }
        .status-unpaid  { background: #fef3c7; color: #92400e; }
        .status-partial { background: #e0f2fe; color: #075985; }
        .status-paid    { background: #d1fae5; color: #065f46; }

        /* Divider */
        .divider { height: 2px; background: #4f46e5; margin: 16px 0; }

        /* Parties */
        .parties { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .party { width: 48%; }
        .party-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #94a3b8; margin-bottom: 4px; }
        .party-name  { font-size: 12px; font-weight: 700; color: #1e293b; }
        .party-info  { font-size: 9px; color: #64748b; margin-top: 2px; }

        .dates { display: flex; gap: 24px; }
        .date-item label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; display: block; margin-bottom: 2px; }
        .date-item span  { font-size: 10px; font-weight: 600; color: #1e293b; }

        /* Items table */
        table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 9.5px; }
        thead tr { background: #4f46e5; color: white; }
        thead th { padding: 6px 8px; text-align: left; font-size: 8.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
        thead th.text-right { text-align: right; }
        thead th.text-center { text-align: center; }
        tbody tr { border-bottom: 1px solid #f1f5f9; }
        tbody tr:nth-child(even) { background: #fafbff; }
        tbody td { padding: 5px 8px; }
        tbody td.text-right  { text-align: right; }
        tbody td.text-center { text-align: center; }

        /* Totals */
        .totals { float: right; width: 260px; margin-top: 8px; }
        .total-row { display: flex; justify-content: space-between; padding: 3px 0; font-size: 9.5px; }
        .total-row.main { border-top: 2px solid #4f46e5; padding-top: 6px; margin-top: 4px; font-size: 12px; font-weight: 700; color: #4f46e5; }
        .total-label { color: #64748b; }
        .total-value { font-weight: 600; color: #1e293b; }

        /* Notes */
        .notes { clear: both; margin-top: 24px; padding: 8px 10px; background: #f8fafc; border-left: 3px solid #4f46e5; font-size: 9px; color: #64748b; border-radius: 0 4px 4px 0; }
        .notes-label { font-weight: 700; color: #475569; font-size: 8px; text-transform: uppercase; margin-bottom: 3px; }

        /* Footer */
        .footer { margin-top: 32px; border-top: 1px solid #e2e8f0; padding-top: 10px; display: flex; justify-content: space-between; font-size: 8px; color: #94a3b8; }

        .overdue-badge { display: inline-block; padding: 2px 8px; background: #fee2e2; color: #991b1b; border-radius: 20px; font-size: 8px; font-weight: 700; margin-top: 4px; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="brand">
            <div class="brand-name">NOTAGO</div>
            <div class="brand-sub">Sistem RAB & Material</div>
        </div>
        <div class="invoice-badge">
            <div class="invoice-label">INVOICE</div>
            <div class="invoice-no">{{ $invoice->invoice_no }}</div>
            @php
                $statusClass = match($invoice->payment_status) { 'paid' => 'status-paid', 'partial' => 'status-partial', default => 'status-unpaid' };
                $statusLabel = match($invoice->payment_status) { 'paid' => 'LUNAS', 'partial' => 'SEBAGIAN', default => 'BELUM BAYAR' };
            @endphp
            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
            @if ($invoice->isOverdue())
                <br><span class="overdue-badge">TERLAMBAT</span>
            @endif
        </div>
    </div>

    <div class="divider"></div>

    {{-- Parties --}}
    <div class="parties">
        <div class="party">
            <div class="party-label">Dari</div>
            <div class="party-name">NOTAGO</div>
            <div class="party-info">Sistem RAB & Material</div>
            @if ($invoice->project)
                <div class="party-info">Proyek: {{ $invoice->project->name }}</div>
            @endif
        </div>
        <div class="party" style="text-align: right">
            <div class="party-label">Kepada</div>
            <div class="party-name">{{ $invoice->client_name }}</div>
            @if ($invoice->client_phone) <div class="party-info">{{ $invoice->client_phone }}</div> @endif
            @if ($invoice->client_address) <div class="party-info">{{ $invoice->client_address }}</div> @endif
        </div>
    </div>

    {{-- Dates --}}
    <div class="dates" style="margin-bottom: 16px;">
        <div class="date-item">
            <label>Tanggal Invoice</label>
            <span>{{ $invoice->invoice_date->format('d M Y') }}</span>
        </div>
        @if ($invoice->due_date)
        <div class="date-item">
            <label>Jatuh Tempo</label>
            <span style="{{ $invoice->isOverdue() ? 'color:#dc2626;' : '' }}">{{ $invoice->due_date->format('d M Y') }}</span>
        </div>
        @endif
        <div class="date-item">
            <label>Terbayar</label>
            <span>Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
        </div>
        <div class="date-item">
            <label>Sisa Tagihan</label>
            <span>Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Items --}}
    <table>
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th>Deskripsi</th>
                <th class="text-center" style="width:10%">Satuan</th>
                <th class="text-right" style="width:10%">Qty</th>
                <th class="text-right" style="width:18%">Harga Satuan</th>
                <th class="text-right" style="width:18%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $item->description }}</strong>
                    @if ($item->notes) <br><small style="color:#94a3b8">{{ $item->notes }}</small> @endif
                </td>
                <td class="text-center">{{ $item->unit ?: '—' }}</td>
                <td class="text-right">{{ number_format($item->qty, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right"><strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <div class="total-row">
            <span class="total-label">Subtotal</span>
            <span class="total-value">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
        </div>
        @if ($invoice->discount_amount > 0)
        <div class="total-row">
            <span class="total-label">Diskon</span>
            <span class="total-value" style="color:#dc2626">− Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</span>
        </div>
        @endif
        @if ($invoice->tax_percent > 0)
        <div class="total-row">
            <span class="total-label">PPN {{ $invoice->tax_percent }}%</span>
            <span class="total-value">+ Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="total-row main">
            <span>GRAND TOTAL</span>
            <span>Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</span>
        </div>
        <div class="total-row" style="font-size:8.5px; color:#64748b; border-top:1px solid #e2e8f0; padding-top:4px;">
            <span>Terbayar</span>
            <span style="color:#059669">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Notes --}}
    @if ($invoice->notes)
    <div class="notes">
        <div class="notes-label">Catatan & Syarat Pembayaran</div>
        {{ $invoice->notes }}
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <span>Dokumen ini dibuat otomatis oleh sistem NOTAGO pada {{ now()->format('d M Y H:i') }}</span>
        <span>{{ $invoice->invoice_no }}</span>
    </div>
</div>
</body>
</html>
