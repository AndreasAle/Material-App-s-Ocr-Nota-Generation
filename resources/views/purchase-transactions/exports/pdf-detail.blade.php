<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Detail Transaksi Pembelian</title>
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

        .page {
            width: 100%;
        }

        .topbar {
            border-bottom: 3px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .topbar-table {
            width: 100%;
            border-collapse: collapse;
        }

        .topbar-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .brand {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.5px;
        }

        .brand-subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 3px;
        }

        .doc-meta {
            text-align: right;
            font-size: 10px;
            color: #475569;
        }

        .doc-meta strong {
            color: #0f172a;
        }

        .report-title {
            margin: 18px 0 6px 0;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
        }

        .report-subtitle {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 18px;
        }

        .section {
            margin-bottom: 18px;
        }

        .section-title {
            background: #0f172a;
            color: #ffffff;
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            vertical-align: top;
        }

        .meta-table td.label {
            width: 22%;
            background: #f8fafc;
            font-weight: 700;
            color: #334155;
        }

        .meta-table td.value {
            width: 28%;
        }

        .items-table th,
        .items-table td,
        .attachment-table th,
        .attachment-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            vertical-align: top;
        }

        .items-table th,
        .attachment-table th {
            background: #e2e8f0;
            color: #0f172a;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
        }

        .items-table tbody tr:nth-child(even),
        .attachment-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .grand-total-wrap {
            margin-top: 10px;
        }

        .grand-total-table {
            width: 100%;
            border-collapse: collapse;
        }

        .grand-total-table td {
            border: 1px solid #d6d3d1;
            padding: 10px 12px;
        }

        .grand-total-label {
            width: 70%;
            background: #fef3c7;
            font-weight: 700;
            text-transform: uppercase;
            text-align: right;
        }

        .grand-total-value {
            background: #fff7ed;
            font-size: 15px;
            font-weight: 800;
            color: #111827;
            text-align: right;
        }

        .muted {
            color: #64748b;
        }

        .notes-box {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 10px 12px;
            margin-top: 10px;
        }

        .signature-section {
            margin-top: 28px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 50%;
            border: none;
            text-align: center;
            vertical-align: top;
            padding: 8px 16px;
        }

        .signature-title {
            font-weight: 700;
            margin-bottom: 50px;
        }

        .signature-name {
            font-weight: 700;
            border-top: 1px solid #334155;
            display: inline-block;
            min-width: 180px;
            padding-top: 6px;
        }

        .footer-note {
            margin-top: 16px;
            font-size: 9px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <table class="topbar-table">
                <tr>
                    <td>
                        <div class="brand">NOTAGO</div>
                        <div class="brand-subtitle">Laporan Detail Transaksi Pembelian</div>
                    </td>
                    <td class="doc-meta">
                        <div><strong>No. Dokumen:</strong> {{ $purchaseTransaction->transaction_no }}</div>
                        <div><strong>Tanggal Transaksi:</strong> {{ $purchaseTransaction->transaction_date?->format('d/m/Y') }}</div>
                        <div><strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="report-title">Laporan Detail Transaksi Pembelian</div>
        <div class="report-subtitle">
            Dokumen ini berisi informasi transaksi, rincian material, total pembelian, serta data lampiran yang terkait.
        </div>

        <div class="section">
            <div class="section-title">Informasi Transaksi</div>
            <table class="meta-table">
                <tr>
                    <td class="label">No. Transaksi</td>
                    <td class="value">{{ $purchaseTransaction->transaction_no }}</td>
                    <td class="label">Tanggal</td>
                    <td class="value">{{ $purchaseTransaction->transaction_date?->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td class="value">{{ ucfirst($purchaseTransaction->status) }}</td>
                    <td class="label">Supplier</td>
                    <td class="value">{{ $purchaseTransaction->supplier?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Proyek</td>
                    <td class="value">{{ $purchaseTransaction->project?->name ?? '-' }}</td>
                    <td class="label">No. Nota</td>
                    <td class="value">{{ $purchaseTransaction->note_number ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Input Oleh</td>
                    <td class="value">{{ $purchaseTransaction->user?->name ?? '-' }}</td>
                    <td class="label">Jumlah Item</td>
                    <td class="value">{{ $purchaseTransaction->items->count() }}</td>
                </tr>
            </table>

            <div class="notes-box">
                <strong>Catatan:</strong><br>
                {{ $purchaseTransaction->notes ?: '-' }}
            </div>
        </div>

        <div class="section">
            <div class="section-title">Rincian Material</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 26%;">Material</th>
                        <th style="width: 10%;">Satuan</th>
                        <th style="width: 8%;" class="text-center">Qty</th>
                        <th style="width: 14%;" class="text-right">Harga</th>
                        <th style="width: 14%;" class="text-right">Subtotal</th>
                        <th style="width: 28%;">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseTransaction->items as $item)
                        <tr>
                            <td>{{ $item->material_name_snapshot }}</td>
                            <td>{{ $item->unit_name_snapshot }}</td>
                            <td class="text-center">{{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            <td>{{ $item->notes ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada item pembelian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="grand-total-wrap">
                <table class="grand-total-table">
                    <tr>
                        <td class="grand-total-label">Grand Total</td>
                        <td class="grand-total-value">Rp {{ number_format($purchaseTransaction->grand_total, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Lampiran Dokumen</div>
            @if($purchaseTransaction->attachments->count())
                <table class="attachment-table">
                    <thead>
                        <tr>
                            <th>Nama File</th>
                            <th style="width: 18%;">Tipe</th>
                            <th style="width: 18%;" class="text-right">Ukuran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseTransaction->attachments as $attachment)
                            <tr>
                                <td>{{ $attachment->file_name }}</td>
                                <td>{{ strtoupper(pathinfo($attachment->file_name, PATHINFO_EXTENSION) ?: 'FILE') }}</td>
                                <td class="text-right">{{ number_format($attachment->file_size / 1024, 1) }} KB</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="notes-box">Tidak ada lampiran.</div>
            @endif
        </div>

        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-title">Dibuat Oleh</div>
                        <div class="signature-name">{{ $purchaseTransaction->user?->name ?? 'Administrator' }}</div>
                    </td>
                    <td>
                        <div class="signature-title">Diperiksa Oleh</div>
                        <div class="signature-name">....................................</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer-note">
            Dokumen ini dihasilkan otomatis oleh sistem NOTAGO.
        </div>
    </div>
</body>
</html>