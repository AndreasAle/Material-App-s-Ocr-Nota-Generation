<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan {{ $goodsReceipt->receipt_no }}</title>
    <style>
        @page {
            margin: 24px 28px 28px 28px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.45;
        }

        .page { width: 100%; }

        /* ── Header ── */
        .topbar {
            border-bottom: 3px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .topbar-table { width: 100%; border-collapse: collapse; }
        .topbar-table td { border: none; padding: 0; vertical-align: top; }

        .brand { font-size: 22px; font-weight: 700; color: #0f172a; letter-spacing: 0.5px; }
        .brand-subtitle { font-size: 11px; color: #64748b; margin-top: 3px; }

        .doc-meta { text-align: right; font-size: 10px; color: #475569; }
        .doc-meta strong { color: #0f172a; }

        .report-title {
            margin: 14px 0 4px 0;
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Status badge ── */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 14px;
        }
        .status-complete { background: #d1fae5; color: #065f46; }
        .status-partial  { background: #fef3c7; color: #92400e; }
        .status-pending  { background: #f1f5f9; color: #475569; }

        /* ── Section ── */
        .section { margin-bottom: 16px; }

        .section-title {
            background: #0f172a;
            color: #ffffff;
            padding: 7px 10px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        table { width: 100%; border-collapse: collapse; }

        .meta-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 9px;
            vertical-align: top;
        }
        .meta-table td.label {
            width: 20%;
            background: #f8fafc;
            font-weight: 700;
            color: #334155;
        }
        .meta-table td.value { width: 30%; }

        /* ── Items table ── */
        .items-table th,
        .items-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            vertical-align: top;
        }
        .items-table th {
            background: #e2e8f0;
            color: #0f172a;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
        }
        .items-table tbody tr:nth-child(even) { background: #f8fafc; }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }

        /* ── Qty comparison ── */
        .qty-ok      { color: #065f46; font-weight: 700; }
        .qty-partial { color: #92400e; font-weight: 700; }
        .qty-zero    { color: #9ca3af; }

        /* ── Summary ── */
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .summary-table td { border: 1px solid #d6d3d1; padding: 8px 10px; }
        .summary-label {
            background: #f8fafc;
            font-weight: 700;
            color: #334155;
            text-align: right;
        }
        .summary-total {
            background: #fff7ed;
            font-size: 12px;
            font-weight: 800;
            color: #111827;
        }

        /* ── Catatan ── */
        .notes-box {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 8px 10px;
            margin-top: 8px;
        }

        /* ── Tanda Tangan ── */
        .signature-section { margin-top: 28px; }
        .signature-table { width: 100%; border-collapse: collapse; }
        .signature-table td {
            width: 33%;
            border: none;
            text-align: center;
            vertical-align: top;
            padding: 8px 12px;
        }
        .signature-title { font-weight: 700; margin-bottom: 50px; font-size: 10px; }
        .signature-name {
            font-weight: 700;
            border-top: 1px solid #334155;
            display: inline-block;
            min-width: 150px;
            padding-top: 5px;
            font-size: 10px;
        }

        .footer-note {
            margin-top: 14px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ── HEADER ── --}}
    <div class="topbar">
        <table class="topbar-table">
            <tr>
                <td>
                    <div class="brand">NOTAGO</div>
                    <div class="brand-subtitle">Sistem RAB & Manajemen Material</div>
                </td>
                <td class="doc-meta">
                    <div><strong>No. Surat Jalan:</strong> {{ $goodsReceipt->receipt_no }}</div>
                    <div><strong>Tanggal Terima:</strong> {{ $goodsReceipt->receipt_date->format('d/m/Y') }}</div>
                    <div><strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">Surat Jalan / Bukti Penerimaan Barang</div>

    @php
        $supplier    = $goodsReceipt->purchaseOrder?->supplier
                    ?? $goodsReceipt->purchaseTransaction?->supplier;
        $project     = $goodsReceipt->purchaseOrder?->project
                    ?? $goodsReceipt->purchaseTransaction?->project;
        $docRef      = $goodsReceipt->purchaseOrder?->po_no
                    ?? $goodsReceipt->purchaseTransaction?->transaction_no
                    ?? '-';
        $docDate     = $goodsReceipt->purchaseOrder?->po_date
                    ?? $goodsReceipt->purchaseTransaction?->transaction_date;
        $docLabel    = $goodsReceipt->purchase_order_id ? 'No. PO' : 'No. Transaksi';

        $statusLabel = match($goodsReceipt->status) {
            'complete' => 'Lengkap',
            'partial'  => 'Sebagian',
            default    => 'Belum Diterima',
        };
        $statusClass = match($goodsReceipt->status) {
            'complete' => 'status-complete',
            'partial'  => 'status-partial',
            default    => 'status-pending',
        };
    @endphp
    <div class="status-badge {{ $statusClass }}">Status: {{ $statusLabel }}</div>

    {{-- ── INFO ── --}}
    <div class="section">
        <div class="section-title">Informasi Penerimaan</div>
        <table class="meta-table">
            <tr>
                <td class="label">No. Surat Jalan</td>
                <td class="value">{{ $goodsReceipt->receipt_no }}</td>
                <td class="label">Tanggal Terima</td>
                <td class="value">{{ $goodsReceipt->receipt_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Diterima Oleh</td>
                <td class="value">{{ $goodsReceipt->received_by }}</td>
                <td class="label">Dicatat Oleh</td>
                <td class="value">{{ $goodsReceipt->creator?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">{{ $docLabel }}</td>
                <td class="value">{{ $docRef }}</td>
                <td class="label">Tgl Dokumen</td>
                <td class="value">{{ $docDate?->format('d/m/Y') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Supplier</td>
                <td class="value">{{ $supplier?->name ?? '-' }}</td>
                <td class="label">Proyek</td>
                <td class="value">{{ $project?->name ?? '-' }}</td>
            </tr>
        </table>

        @if ($goodsReceipt->notes)
            <div class="notes-box"><strong>Catatan:</strong> {{ $goodsReceipt->notes }}</div>
        @endif
    </div>

    {{-- ── TABEL ITEM ── --}}
    <div class="section">
        <div class="section-title">Rincian Barang</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 4%;"  class="text-center">No</th>
                    <th style="width: 32%;">Nama Material</th>
                    <th style="width: 9%;"  class="text-center">Satuan</th>
                    <th style="width: 13%;" class="text-right">Qty Dipesan</th>
                    <th style="width: 13%;" class="text-right">Qty Diterima</th>
                    <th style="width: 10%;" class="text-right">Selisih</th>
                    <th style="width: 10%;" class="text-center">Status</th>
                    <th style="width: 9%;">Ket.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($goodsReceipt->items as $i => $item)
                    @php
                        $selisih    = (float) $item->qty_received - (float) $item->qty_ordered;
                        $isComplete = (float) $item->qty_received >= (float) $item->qty_ordered;
                        $isPartial  = (float) $item->qty_received > 0 && ! $isComplete;
                        $qtyClass   = $isComplete ? 'qty-ok' : ($isPartial ? 'qty-partial' : 'qty-zero');
                        $itemStatus = $isComplete ? 'Lengkap' : ($isPartial ? 'Sebagian' : 'Belum');
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $item->material_name_snapshot }}</td>
                        <td class="text-center">{{ $item->unit_name_snapshot }}</td>
                        <td class="text-right">{{ number_format((float) $item->qty_ordered, 2, ',', '.') }}</td>
                        <td class="text-right {{ $qtyClass }}">{{ number_format((float) $item->qty_received, 2, ',', '.') }}</td>
                        <td class="text-right {{ $selisih < 0 ? 'qty-partial' : 'qty-ok' }}">
                            {{ $selisih >= 0 ? '+' : '' }}{{ number_format($selisih, 2, ',', '.') }}
                        </td>
                        <td class="text-center">{{ $itemStatus }}</td>
                        <td>{{ $item->notes ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Ringkasan --}}
        @php
            $totalOrdered  = $goodsReceipt->items->sum(fn($i) => (float) $i->qty_ordered);
            $totalReceived = $goodsReceipt->items->sum(fn($i) => (float) $i->qty_received);
            $totalPct      = $totalOrdered > 0 ? min(100, round(($totalReceived / $totalOrdered) * 100)) : 0;
        @endphp
        <table class="summary-table">
            <tr>
                <td class="summary-label" style="width: 55%;">Total Qty Dipesan</td>
                <td style="width: 45%;">{{ number_format($totalOrdered, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="summary-label">Total Qty Diterima</td>
                <td class="summary-total">{{ number_format($totalReceived, 2, ',', '.') }} ({{ $totalPct }}%)</td>
            </tr>
        </table>
    </div>

    {{-- ── TANDA TANGAN ── --}}
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-title">Supplier / Pengirim</div>
                    <div class="signature-name">{{ $supplier?->name ?? '............................' }}</div>
                </td>
                <td>
                    <div class="signature-title">Diterima Oleh</div>
                    <div class="signature-name">{{ $goodsReceipt->received_by }}</div>
                </td>
                <td>
                    <div class="signature-title">Diketahui Oleh</div>
                    <div class="signature-name">....................................</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-note">
        Dokumen ini dihasilkan otomatis oleh sistem NOTAGO &middot; {{ now()->format('d/m/Y H:i') }}
    </div>
</div>
</body>
</html>
