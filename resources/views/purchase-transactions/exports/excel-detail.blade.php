<table>
    <tr>
        <td>LAPORAN DETAIL TRANSAKSI PEMBELIAN</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>Dokumen transaksi pembelian material</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr><td></td></tr>

    <tr>
        <td>INFORMASI TRANSAKSI</td>
        <td>No. Transaksi</td>
        <td>{{ $purchaseTransaction->transaction_no }}</td>
        <td>Tanggal</td>
        <td colspan="2">{{ $purchaseTransaction->transaction_date?->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td></td>
        <td>Status</td>
        <td>{{ ucfirst($purchaseTransaction->status) }}</td>
        <td>Supplier</td>
        <td colspan="2">{{ $purchaseTransaction->supplier?->name ?? '-' }}</td>
    </tr>
    <tr>
        <td></td>
        <td>Proyek</td>
        <td>{{ $purchaseTransaction->project?->name ?? '-' }}</td>
        <td>No. Nota</td>
        <td colspan="2">{{ $purchaseTransaction->note_number ?: '-' }}</td>
    </tr>
    <tr>
        <td></td>
        <td>Input Oleh</td>
        <td>{{ $purchaseTransaction->user?->name ?? '-' }}</td>
        <td>Catatan</td>
        <td colspan="2">{{ $purchaseTransaction->notes ?: '-' }}</td>
    </tr>
    <tr>
        <td></td>
        <td>Grand Total</td>
        <td>{{ $purchaseTransaction->grand_total }}</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr><td></td></tr>
    <tr><td>RINCIAN MATERIAL</td></tr>

    <tr>
        <th>Material</th>
        <th>Satuan</th>
        <th>Qty</th>
        <th>Harga</th>
        <th>Subtotal</th>
        <th>Catatan</th>
    </tr>

    @forelse($purchaseTransaction->items as $item)
        <tr>
            <td>{{ $item->material_name_snapshot }}</td>
            <td>{{ $item->unit_name_snapshot }}</td>
            <td>{{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}</td>
            <td>{{ $item->price }}</td>
            <td>{{ $item->subtotal }}</td>
            <td>{{ $item->notes ?: '-' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6">Belum ada item pembelian.</td>
        </tr>
    @endforelse

    <tr>
        <td colspan="4">GRAND TOTAL</td>
        <td colspan="2">{{ $purchaseTransaction->grand_total }}</td>
    </tr>

    <tr><td></td></tr>
    <tr><td>LAMPIRAN DOKUMEN</td></tr>

    <tr>
        <th>Nama File Lampiran</th>
        <th>Tipe</th>
        <th>Ukuran (KB)</th>
    </tr>

    @forelse($purchaseTransaction->attachments as $attachment)
        <tr>
            <td>{{ $attachment->file_name }}</td>
            <td>{{ strtoupper(pathinfo($attachment->file_name, PATHINFO_EXTENSION) ?: 'FILE') }}</td>
            <td>{{ number_format($attachment->file_size / 1024, 1) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="3">Tidak ada lampiran.</td>
        </tr>
    @endforelse
</table>