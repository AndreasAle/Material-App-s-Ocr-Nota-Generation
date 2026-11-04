@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showStockModal: false, modalType: 'in' }">

    {{-- ─── Page Header ─── --}}
    <x-page-header :title="$warehouse->name" :subtitle="$warehouse->location ?? 'Lokasi belum diatur'">
        <x-slot name="actions">
            <a href="{{ route('warehouses.index') }}" class="btn-secondary">← Semua Gudang</a>
            <button @click="showStockModal = true; modalType = 'in'"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Stok Masuk
            </button>
            <button @click="showStockModal = true; modalType = 'out'"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold bg-rose-500 text-white hover:bg-rose-600 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
                </svg>
                Stok Keluar
            </button>
            <a href="{{ route('warehouses.qr-label', $warehouse) }}" class="btn-secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zm0 9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zm9.75-9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z"/>
                </svg>
                QR Label
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn-secondary">Edit</a>
            @endif
        </x-slot>
    </x-page-header>


    {{-- ─── Stats ─── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $summary['materials'] }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Jenis Material</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-sm text-center">
            <p class="text-2xl font-bold text-emerald-700">{{ number_format($summary['total_in'], 0) }}</p>
            <p class="text-xs text-emerald-600 mt-0.5">Total Masuk</p>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 shadow-sm text-center">
            <p class="text-2xl font-bold text-rose-600">{{ number_format($summary['total_out'], 0) }}</p>
            <p class="text-xs text-rose-500 mt-0.5">Total Keluar</p>
        </div>
        <div class="rounded-2xl border {{ $summary['low_stock'] > 0 ? 'border-amber-200 bg-amber-50' : 'border-slate-200 bg-white' }} px-5 py-4 shadow-sm text-center">
            <p class="text-2xl font-bold {{ $summary['low_stock'] > 0 ? 'text-amber-700' : 'text-slate-800' }}">{{ $summary['low_stock'] }}</p>
            <p class="text-xs {{ $summary['low_stock'] > 0 ? 'text-amber-600' : 'text-slate-500' }} mt-0.5">Stok Rendah</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ─── Info Gudang ─── --}}
        <x-data-card title="Info Gudang">
            <div class="px-5 pb-5 space-y-3 text-sm">
                <div class="flex justify-between gap-2">
                    <span class="text-slate-500">Nama</span>
                    <span class="font-semibold text-slate-800">{{ $warehouse->name }}</span>
                </div>
                @if($warehouse->code)
                <div class="flex justify-between gap-2">
                    <span class="text-slate-500">Kode</span>
                    <span class="font-mono text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-lg text-xs">{{ $warehouse->code }}</span>
                </div>
                @endif
                @if($warehouse->location)
                <div class="flex justify-between gap-2">
                    <span class="text-slate-500">Lokasi</span>
                    <span class="text-slate-700">{{ $warehouse->location }}</span>
                </div>
                @endif
                @if($warehouse->manager_name)
                <div class="flex justify-between gap-2">
                    <span class="text-slate-500">Penanggung Jawab</span>
                    <span class="font-semibold text-slate-700">{{ $warehouse->manager_name }}</span>
                </div>
                @endif
                @if($warehouse->phone)
                <div class="flex justify-between gap-2">
                    <span class="text-slate-500">Telepon</span>
                    <a href="tel:{{ $warehouse->phone }}" class="text-indigo-600 hover:underline">{{ $warehouse->phone }}</a>
                </div>
                @endif
                <div class="flex justify-between gap-2">
                    <span class="text-slate-500">Status</span>
                    @if($warehouse->is_active)
                        <span class="badge-success">Aktif</span>
                    @else
                        <span class="badge-neutral">Nonaktif</span>
                    @endif
                </div>
                @if($warehouse->description)
                <div class="pt-2 border-t border-slate-100 text-xs text-slate-500">{{ $warehouse->description }}</div>
                @endif
            </div>

            {{-- QR shortcut --}}
            <div class="px-5 pb-5">
                <a href="{{ route('warehouses.qr-label', $warehouse) }}"
                   class="flex items-center justify-center gap-2 w-full rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zm0 9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zm9.75-9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z"/>
                    </svg>
                    Cetak QR Label Material
                </a>
            </div>
        </x-data-card>

        {{-- ─── Stok per Material ─── --}}
        <div class="lg:col-span-2">
            <x-data-card title="Stok per Material" subtitle="{{ $materialStocks->count() }} jenis material">
                @if($materialStocks->isEmpty())
                <div class="px-5 pb-8 text-center text-slate-400 text-sm py-10">
                    <div class="text-4xl mb-2">📦</div>
                    <p>Belum ada stok di gudang ini.</p>
                    <button @click="showStockModal = true; modalType = 'in'"
                            class="mt-3 text-indigo-600 text-sm font-semibold hover:underline">+ Tambah stok masuk</button>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th class="th-center">Satuan</th>
                                <th class="th-right">Stok Saat Ini</th>
                                <th class="th-center">Status</th>
                                <th class="th-right">Pergerakan Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materialStocks as $ms)
                            @php $stock = (float)$ms->current_stock; @endphp
                            <tr>
                                <td class="font-medium text-slate-800">{{ $ms->material_name_snapshot }}</td>
                                <td class="text-center text-slate-500 text-sm">{{ $ms->unit_name_snapshot }}</td>
                                <td class="text-right font-bold font-mono {{ $stock > 10 ? 'text-emerald-700' : ($stock > 0 ? 'text-amber-600' : 'text-rose-600') }}">
                                    {{ number_format($stock, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($stock > 10)
                                        <span class="badge-success">Normal</span>
                                    @elseif($stock > 0)
                                        <span class="badge-warning">Rendah</span>
                                    @else
                                        <span class="badge-danger">Habis</span>
                                    @endif
                                </td>
                                <td class="text-right text-xs text-slate-400">
                                    {{ $ms->last_movement ? \Carbon\Carbon::parse($ms->last_movement)->translatedFormat('d M Y') : '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </x-data-card>
        </div>
    </div>

    {{-- ─── Riwayat Pergerakan ─── --}}
    <x-data-card title="Riwayat Pergerakan Stok" subtitle="{{ $movements->total() }} transaksi">
        @if($movements->isEmpty())
        <div class="px-5 pb-8 text-center text-slate-400 text-sm py-6">Belum ada pergerakan stok</div>
        @else
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Material</th>
                        <th class="th-center">Tipe</th>
                        <th class="th-right">Qty</th>
                        <th>Referensi</th>
                        <th>Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $mv)
                    <tr>
                        <td class="text-slate-500 text-xs whitespace-nowrap">{{ $mv->movement_date->translatedFormat('d M Y') }}</td>
                        <td class="font-medium text-slate-800">{{ $mv->material_name_snapshot }}</td>
                        <td class="text-center">
                            @if($mv->movement_type === 'in')
                                <span class="badge-success">↑ Masuk</span>
                            @elseif($mv->movement_type === 'out')
                                <span class="badge-danger">↓ Keluar</span>
                            @else
                                <span class="badge-neutral">⇄ Koreksi</span>
                            @endif
                        </td>
                        <td class="text-right font-semibold font-mono {{ $mv->movement_type === 'in' ? 'text-emerald-700' : ($mv->movement_type === 'out' ? 'text-rose-600' : 'text-slate-700') }}">
                            {{ $mv->movement_type === 'in' ? '+' : ($mv->movement_type === 'out' ? '−' : '±') }}{{ number_format($mv->qty, 2, ',', '.') }}
                        </td>
                        <td class="text-xs text-slate-500">{{ $mv->reference_no ?? '—' }}</td>
                        <td class="text-xs text-slate-500">{{ $mv->creator?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100">{{ $movements->links() }}</div>
        @endif
    </x-data-card>

</div>

{{-- ═══ MODAL: Quick Stock In/Out ═══ --}}
<div x-show="showStockModal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center px-4"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showStockModal = false"></div>

    {{-- Modal box --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold text-slate-800"
                    x-text="modalType === 'in' ? '+ Stok Masuk' : '− Stok Keluar'"></h3>
                <p class="text-sm text-slate-400">Gudang: {{ $warehouse->name }}</p>
            </div>
            <button @click="showStockModal = false" class="h-8 w-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('stock.store') }}">
            @csrf
            <input type="hidden" name="warehouse_id" value="{{ $warehouse->id }}">
            <input type="hidden" name="warehouse" value="{{ $warehouse->name }}">
            <input type="hidden" name="movement_type" :value="modalType">

            <div class="space-y-4">
                <div>
                    <label class="form-label">Material <span class="text-rose-500">*</span></label>
                    <select name="material_id" class="form-select" required>
                        <option value="">— Pilih material —</option>
                        @foreach($allMaterials as $m)
                        <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->unit?->name ?? '—' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Jumlah (Qty) <span class="text-rose-500">*</span></label>
                        <input type="number" name="qty" min="0.01" step="0.01" class="form-input" required placeholder="0">
                    </div>
                    <div>
                        <label class="form-label">Tanggal <span class="text-rose-500">*</span></label>
                        <input type="date" name="movement_date" class="form-input" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div>
                    <label class="form-label">Catatan / No. Referensi</label>
                    <input type="text" name="notes" class="form-input" placeholder="No. PO / Surat Jalan / keterangan...">
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="button" @click="showStockModal = false" class="btn-secondary flex-1">Batal</button>
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white transition-colors shadow-sm"
                        :class="modalType === 'in' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-500 hover:bg-rose-600'">
                    <span x-text="modalType === 'in' ? 'Simpan Stok Masuk' : 'Simpan Stok Keluar'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
