@php
    $rp = fn ($v) => number_format((float) $v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');

    $lines = [config('store.name'), 'No: ' . $sale->sale_no, $sale->sale_date->format('d/m/Y H:i'), str_repeat('-', 24)];
    foreach ($sale->items as $item) {
        $lines[] = $item->material_name_snapshot;
        $lines[] = '  ' . $qty($item->qty) . ' ' . $item->unit_name . ' x ' . $rp($item->price) . ' = ' . $rp($item->subtotal);
    }
    $lines[] = str_repeat('-', 24);
    if ((float) $sale->discount_amount > 0) {
        $lines[] = 'Diskon: -' . $rp($sale->discount_amount);
    }
    $lines[] = '*TOTAL: Rp ' . $rp($sale->grand_total) . '*';
    $lines[] = 'Bayar: ' . $sale->paymentMethodLabel() . ($sale->payment_method === 'cash' ? ' Rp ' . $rp($sale->cash_received) . ', kembali Rp ' . $rp($sale->change_amount) : '');
    if ((float) $sale->remaining_amount > 0) {
        $lines[] = 'Sisa bon: Rp ' . $rp($sale->remaining_amount) . ' (jatuh tempo ' . $sale->due_date?->format('d/m/Y') . ')';
    }
    $lines[] = config('store.footer');
    $waText = rawurlencode(implode("\n", $lines));

    $phone = preg_replace('/\D/', '', (string) $sale->customer?->phone);
    if (str_starts_with($phone, '0')) {
        $phone = '62' . substr($phone, 1);
    }
    $waUrl = 'https://wa.me/' . $phone . '?text=' . $waText;
    $isCredit = (float) $sale->remaining_amount > 0;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1f252f">
    <title>Struk {{ $sale->sale_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
        [x-cloak] { display: none !important; }

        /* Kertas struk thermal */
        .paper { --w: 58mm; width: var(--w); max-width: 100%; font-family: 'Courier New', monospace; font-size: 11px; line-height: 1.4; color: #111; }
        .paper.w80 { --w: 80mm; font-size: 12px; }
        .paper .hr { border-top: 1px dashed #000; margin: 6px 0; }
        .paper .row { display: flex; justify-content: space-between; gap: 6px; }
        .paper .c { text-align: center; }
        .paper .b { font-weight: 700; }

        /* Tepi sobekan kertas */
        .torn { position: relative; }
        .torn::after {
            content: ''; position: absolute; left: 0; right: 0; bottom: -8px; height: 8px;
            background:
                linear-gradient(-45deg, transparent 6px, #fff 0) 0 0 / 12px 8px repeat-x,
                linear-gradient(45deg, transparent 6px, #fff 0) 0 0 / 12px 8px repeat-x;
        }

        /* Saat dicetak hanya struk yang keluar */
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .print-area { padding: 0 !important; margin: 0 !important; box-shadow: none !important; }
            .torn::after { display: none; }
            @page { margin: 0; }
        }
    </style>
    <style id="page-size">@media print { @page { size: 58mm auto; } }</style>
</head>
<body class="min-h-screen antialiased" x-data="{ wide: false }" x-effect="document.getElementById('page-size').textContent = '@media print { @page { size: ' + (wide ? 80 : 58) + 'mm auto; } }'">
    <div class="mx-auto flex min-h-screen max-w-md flex-col px-4 pb-[calc(1.5rem+env(safe-area-inset-bottom))] pt-6">

        {{-- Status pembayaran --}}
        <div class="no-print text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full {{ $isCredit ? 'bg-amber-500 shadow-amber-500/30' : 'bg-emerald-500 shadow-emerald-500/30' }} text-white shadow-lg">
                <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            </div>
            <h1 class="mt-3 text-xl font-extrabold text-charcoal-900">{{ $isCredit ? 'Penjualan bon tersimpan' : 'Pembayaran berhasil' }}</h1>
            <p class="mt-0.5 text-sm text-charcoal-500">{{ $sale->sale_no }} · {{ $sale->sale_date->format('d M Y, H:i') }}</p>
        </div>

        <div class="no-print mt-5 overflow-hidden rounded-2xl bg-charcoal-900 text-white shadow-lg shadow-charcoal-900/20">
            <div class="hazard-strip"></div>
            <div class="grid grid-cols-2">
                <div class="px-4 py-4">
                    <p class="text-xs text-charcoal-300">Total belanja</p>
                    <p class="mt-1 text-xl font-extrabold">Rp {{ $rp($sale->grand_total) }}</p>
                    <p class="mt-0.5 truncate text-xs text-charcoal-300">{{ $sale->paymentMethodLabel() }}{{ $sale->customer ? ' · ' . $sale->customer->name : '' }}</p>
                </div>
                @if($sale->payment_method === 'cash')
                    <div class="bg-brand-500 px-4 py-4">
                        <p class="text-xs text-white/85">Kembalian</p>
                        <p class="mt-1 text-xl font-extrabold">Rp {{ $rp($sale->change_amount) }}</p>
                        <p class="mt-0.5 text-xs text-white/85">Diterima Rp {{ $rp($sale->cash_received) }}</p>
                    </div>
                @elseif($isCredit)
                    <div class="bg-amber-500 px-4 py-4">
                        <p class="text-xs text-white/90">Sisa bon</p>
                        <p class="mt-1 text-xl font-extrabold">Rp {{ $rp($sale->remaining_amount) }}</p>
                        <p class="mt-0.5 text-xs text-white/90">Tempo {{ $sale->due_date?->format('d M Y') }}</p>
                    </div>
                @else
                    <div class="bg-emerald-600 px-4 py-4">
                        <p class="text-xs text-white/85">Status</p>
                        <p class="mt-1 text-xl font-extrabold">Lunas</p>
                        <p class="mt-0.5 text-xs text-white/85">{{ $sale->paymentMethodLabel() }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Struk --}}
        <div class="no-print mt-6 flex items-center justify-between">
            <p class="text-sm font-bold text-charcoal-800">Pratinjau struk</p>
            <div class="inline-flex rounded-xl bg-charcoal-200/70 p-1 text-xs font-bold">
                <button type="button" @click="wide = false" class="rounded-lg px-3 py-1.5" :class="!wide ? 'bg-white text-charcoal-900 shadow-sm' : 'text-charcoal-500'">58 mm</button>
                <button type="button" @click="wide = true" class="rounded-lg px-3 py-1.5" :class="wide ? 'bg-white text-charcoal-900 shadow-sm' : 'text-charcoal-500'">80 mm</button>
            </div>
        </div>

        <div class="print-area torn mx-auto mt-3 rounded-t-xl bg-white px-3 pb-4 pt-4 shadow-sm">
            <div class="paper" :class="wide ? 'w80' : ''">
                <div class="c b" style="font-size:1.2em">{{ config('store.name') }}</div>
                @if(config('store.address'))<div class="c">{{ config('store.address') }}</div>@endif
                @if(config('store.phone'))<div class="c">Telp/WA {{ config('store.phone') }}</div>@endif
                <div class="hr"></div>
                <div class="row"><span>{{ $sale->sale_no }}</span><span>{{ $sale->sale_date->format('d/m/y H:i') }}</span></div>
                <div>Kasir: {{ $sale->user?->name }}</div>
                @if($sale->customer)<div>Pelanggan: {{ $sale->customer->name }}</div>@endif
                <div class="hr"></div>
                @foreach($sale->items as $item)
                    <div>{{ $item->material_name_snapshot }}</div>
                    <div class="row"><span>{{ $qty($item->qty) }} {{ $item->unit_name }} x {{ $rp($item->price) }}</span><span>{{ $rp($item->subtotal) }}</span></div>
                @endforeach
                <div class="hr"></div>
                <div class="row"><span>Subtotal</span><span>{{ $rp($sale->subtotal) }}</span></div>
                @if((float) $sale->discount_amount > 0)
                    <div class="row"><span>Diskon</span><span>-{{ $rp($sale->discount_amount) }}</span></div>
                @endif
                <div class="row b" style="font-size:1.15em"><span>TOTAL</span><span>{{ $rp($sale->grand_total) }}</span></div>
                <div class="row"><span>{{ $sale->paymentMethodLabel() }}</span><span>{{ $rp($sale->payment_method === 'bon' ? $sale->paid_amount : $sale->cash_received) }}</span></div>
                @if($sale->payment_method === 'cash')
                    <div class="row"><span>Kembali</span><span>{{ $rp($sale->change_amount) }}</span></div>
                @endif
                @if($isCredit)
                    <div class="row b"><span>SISA BON</span><span>{{ $rp($sale->remaining_amount) }}</span></div>
                    <div>Jatuh tempo: {{ $sale->due_date?->format('d/m/Y') }}</div>
                @endif
                <div class="hr"></div>
                <div class="c">{{ config('store.footer') }}</div>
            </div>
        </div>

        {{-- Aksi --}}
        <div class="no-print mt-8 space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <button type="button" onclick="window.print()"
                        class="inline-flex h-14 items-center justify-center gap-2 rounded-xl border border-charcoal-200 bg-white text-[15px] font-bold text-charcoal-800 active:scale-[0.98]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                    Cetak
                </button>
                <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                   class="inline-flex h-14 items-center justify-center gap-2 rounded-xl bg-[#25D366] text-[15px] font-bold text-white active:scale-[0.98]">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    WhatsApp
                </a>
            </div>
            <a href="{{ route('store.pos') }}"
               class="inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl bg-brand-600 text-[17px] font-extrabold text-white shadow-lg shadow-brand-600/30 active:scale-[0.98]">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Transaksi Baru
            </a>
            <div class="flex items-center justify-center gap-4 pt-1 text-sm font-semibold text-charcoal-600">
                <a href="{{ route('store.sales.show', $sale) }}" class="underline-offset-4 hover:underline">Detail penjualan</a>
                <span class="text-charcoal-300">·</span>
                <a href="{{ route('dashboard') }}" class="underline-offset-4 hover:underline">Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>
