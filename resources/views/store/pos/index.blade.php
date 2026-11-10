@extends('layouts.app')
@section('content')

<div x-data="posApp()" x-init="init()" class="pos-dashboard">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-brand-600">Point of sale</p>
            <h1 class="mt-0.5 text-xl font-extrabold leading-tight tracking-tight text-charcoal-900">Kasir</h1>
            <p class="mt-0.5 text-[11px] text-charcoal-500">Pilih barang, atur jumlah, lalu bayar</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="$refs.cartPanel.scrollIntoView({ behavior: 'smooth', block: 'start' })" class="inline-flex h-10 items-center gap-1.5 rounded-xl bg-brand-50 px-3 text-xs font-bold text-brand-700 lg:hidden">
                <x-icon name="cart" class="h-4 w-4" /><span x-text="cart.length"></span>
            </button>
            <a href="{{ route('store.sales.index') }}" class="btn-secondary !min-h-[40px] !px-3 !text-xs">Riwayat</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
        {{-- Cari barang --}}
        <div class="lg:col-span-3">
            <div class="sticky top-0 z-10 -mx-3 bg-[#f3f4f6]/95 px-3 pb-3 pt-1 backdrop-blur sm:-mx-5 sm:px-5 md:-mx-8 md:px-8 lg:mx-0 lg:bg-white lg:px-0">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-charcoal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                    <input type="search" x-ref="search" x-model="q" @input.debounce.250ms="search()"
                           @keydown.enter.prevent="addFirst()"
                           placeholder="Cari nama / kode / scan barcode…" autocomplete="off"
                           class="form-input h-11 pl-10 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <template x-for="m in results" :key="m.id">
                    <button type="button" @click="add(m)"
                            class="pos-product relative rounded-xl border bg-white p-3 text-left shadow-sm transition active:scale-[0.99]"
                            :class="[m.stock <= 0 ? 'opacity-50' : '', inCart(m.id) ? 'border-brand-500 ring-1 ring-brand-500' : 'border-charcoal-200 hover:border-brand-300']">
                        <div class="min-w-0">
                            <span class="line-clamp-2 text-xs font-bold leading-4 text-charcoal-900" x-text="m.name"></span>
                            <span class="mt-1 block text-sm font-extrabold text-brand-700" x-text="rp(m.units[0].price)"></span>
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-1 text-[10px] text-charcoal-500">
                            <span class="truncate" x-text="m.code"></span>
                            <span class="shrink-0" :class="m.stock <= 0 ? 'font-bold text-rose-600' : ''" x-text="'Stok ' + num(m.stock)"></span>
                        </div>
                        <span x-show="inCart(m.id)" x-text="'×' + num(inCart(m.id))"
                              class="absolute -right-1.5 -top-1.5 inline-flex h-6 min-w-[24px] items-center justify-center rounded-full bg-brand-600 px-1.5 text-xs font-extrabold text-white"></span>
                    </button>
                </template>
            </div>
            <p x-show="!loading && results.length === 0" class="py-8 text-center text-sm text-charcoal-400">Barang tidak ditemukan.</p>
        </div>

        {{-- Keranjang --}}
        <div class="scroll-mt-4 lg:col-span-2" x-ref="cartPanel">
            <div class="card overflow-hidden lg:sticky lg:top-4">
                <div class="flex items-center justify-between border-b border-charcoal-100 px-4 py-3">
                    <span class="text-sm font-bold text-charcoal-900">Keranjang <span class="ml-1 rounded-full bg-charcoal-100 px-2 py-0.5 text-[10px]" x-text="cart.length"></span></span>
                    <button type="button" x-show="cart.length" @click="if (confirm('Kosongkan keranjang?')) cart = []" class="text-[11px] font-semibold text-rose-600">Kosongkan</button>
                </div>
                <div class="divide-y divide-charcoal-100 lg:max-h-[55vh] lg:overflow-y-auto">
                    <template x-for="(row, i) in cart" :key="row.key">
                        <div class="px-3.5 py-3">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-xs font-bold text-charcoal-900" x-text="row.name"></span>
                                <button type="button" class="-m-1 p-1 text-[10px] font-semibold text-rose-500" @click="cart.splice(i, 1)">Hapus</button>
                            </div>
                            <div class="mt-2 grid grid-cols-12 gap-2">
                                <div class="col-span-5 flex h-10 items-center rounded-xl border border-charcoal-200">
                                    <button type="button" class="h-full px-2.5 text-lg font-bold text-charcoal-600" @click="row.qty = Math.max(0.01, +(row.qty - 1).toFixed(2)); reprice(row)">−</button>
                                    <input type="number" step="0.01" min="0.01" x-model.number="row.qty" @input="reprice(row)" class="w-full border-0 p-0 text-center text-sm font-bold focus:ring-0">
                                    <button type="button" class="h-full px-2.5 text-lg font-bold text-charcoal-600" @click="row.qty = +(row.qty + 1).toFixed(2); reprice(row)">+</button>
                                </div>
                                <select class="col-span-3 h-10 rounded-xl border border-charcoal-200 px-2 text-xs" x-model="row.unit" @change="reprice(row, true)">
                                    <template x-for="u in row.units" :key="u.name">
                                        <option :value="u.name" x-text="u.name" :selected="u.name === row.unit"></option>
                                    </template>
                                </select>
                                <input type="number" min="0" step="100" x-model.number="row.price" @input="row.manualPrice = true"
                                       class="col-span-4 h-10 rounded-xl border border-charcoal-200 px-2 text-right text-xs" aria-label="Harga">
                            </div>
                            <div class="mt-1.5 flex justify-between text-xs">
                                <span class="font-bold text-rose-600" x-show="stockLeft(row) < 0">Stok kurang!</span>
                                <span x-show="row.isWholesale" class="font-semibold text-emerald-600">Harga grosir</span>
                                <span class="ml-auto font-extrabold text-charcoal-800" x-text="rp(row.qty * row.price)"></span>
                            </div>
                        </div>
                    </template>
                    <div x-show="cart.length === 0" class="px-4 py-8 text-center">
                        <svg class="mx-auto h-10 w-10 text-charcoal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                        <p class="mt-2 text-sm text-charcoal-500">Keranjang kosong. Ketuk barang di atas.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bar total: hanya total + tombol Bayar, isian pembayaran ada di lembar Bayar (tidak menutupi apa pun) --}}
    <div x-show="cart.length" x-cloak class="mt-3 lg:sticky lg:bottom-4 lg:z-30 lg:mt-4">
        <div class="flex items-center gap-3 rounded-xl border border-charcoal-200 bg-white px-3 py-2 text-charcoal-900 shadow-sm lg:rounded-2xl lg:border-0 lg:bg-charcoal-900 lg:px-4 lg:py-2.5 lg:text-white lg:shadow-xl lg:shadow-charcoal-900/25">
            <div class="min-w-0 flex-1">
                <div class="text-[10px] text-charcoal-500 lg:text-charcoal-300"><span x-text="totalQtyLabel()"></span></div>
                <div class="truncate text-base font-extrabold lg:text-lg" x-text="rp(total())"></div>
            </div>
            <button type="button" @click="openCheckout()" :disabled="cart.length === 0"
                    class="inline-flex h-10 items-center gap-1.5 rounded-xl bg-brand-500 px-4 text-xs font-extrabold text-white disabled:opacity-40 lg:h-12 lg:px-5 lg:text-sm">
                Bayar
                <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </button>
        </div>
    </div>

    {{-- Lembar pembayaran --}}
    <template x-teleport="body">
        <div x-show="checkoutOpen" x-cloak class="fixed inset-0 z-[90] flex items-end justify-center bg-charcoal-950/60 sm:items-center sm:p-4" @keydown.escape.window="checkoutOpen = false">
            <div x-show="checkoutOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-8 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                 class="flex max-h-[92vh] w-full max-w-lg flex-col rounded-t-3xl bg-white sm:rounded-3xl" @click.outside="checkoutOpen = false">
                <div class="flex items-center justify-between border-b border-charcoal-100 px-5 py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-charcoal-500">Total belanja</p>
                        <p class="text-2xl font-extrabold text-charcoal-900" x-text="rp(total())"></p>
                    </div>
                    <button type="button" @click="checkoutOpen = false" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-charcoal-100 text-charcoal-600" aria-label="Tutup">✕</button>
                </div>

                <div class="flex-1 space-y-4 overflow-y-auto px-5 py-4">
                    {{-- Metode --}}
                    <div>
                        <p class="form-label">Cara bayar</p>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="m in [['cash','Tunai'],['transfer','Transfer'],['bon','Bon']]" :key="m[0]">
                                <button type="button" @click="method = m[0]"
                                        class="h-12 rounded-xl border text-[15px] font-bold"
                                        :class="method === m[0] ? 'border-brand-600 bg-brand-600 text-white' : 'border-charcoal-200 bg-white text-charcoal-700'"
                                        x-text="m[1]"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Pelanggan --}}
                    <div>
                        <p class="form-label" x-text="method === 'bon' ? 'Pelanggan (wajib untuk bon)' : 'Pelanggan (opsional)'"></p>
                        <div class="flex gap-2">
                            <select x-model="customerId" class="form-select flex-1">
                                <option value="">Pembeli umum</option>
                                <template x-for="c in customers" :key="c.id">
                                    <option :value="c.id" x-text="c.name + (c.phone ? ' · ' + c.phone : '')"></option>
                                </template>
                            </select>
                            <button type="button" class="btn-secondary shrink-0" @click="newCustomerOpen = true">+ Baru</button>
                        </div>
                    </div>

                    {{-- Uang diterima (tunai) --}}
                    <div x-show="method === 'cash'">
                        <p class="form-label">Uang diterima</p>
                        <input type="number" inputmode="numeric" min="0" step="1000" x-model.number="cashReceived" class="form-input h-14 text-xl font-extrabold" :placeholder="total()">
                        <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <template x-for="v in quickCash()" :key="v">
                                <button type="button" @click="cashReceived = v"
                                        class="h-11 rounded-xl border text-sm font-bold"
                                        :class="Number(cashReceived) === v ? 'border-brand-600 bg-brand-50 text-brand-700' : 'border-charcoal-200 text-charcoal-700'"
                                        x-text="v === total() ? 'Uang pas' : rp(v)"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Uang muka (bon) --}}
                    <div x-show="method === 'bon'">
                        <p class="form-label">Uang muka (opsional)</p>
                        <input type="number" inputmode="numeric" min="0" step="1000" x-model.number="downPayment" class="form-input" placeholder="0">
                    </div>

                    {{-- Diskon --}}
                    <details class="rounded-xl border border-charcoal-200 px-4" :open="discount > 0">
                        <summary class="flex min-h-[48px] cursor-pointer items-center text-sm font-bold text-charcoal-700">Beri diskon</summary>
                        <div class="pb-4">
                            <input type="number" inputmode="numeric" min="0" step="100" x-model.number="discount" class="form-input" placeholder="0">
                        </div>
                    </details>

                    {{-- Ringkasan --}}
                    <div class="space-y-1.5 rounded-xl bg-charcoal-50 px-4 py-3 text-sm">
                        <div class="flex justify-between"><span class="text-charcoal-500">Subtotal</span><span x-text="rp(subtotal())"></span></div>
                        <div class="flex justify-between" x-show="discount > 0"><span class="text-charcoal-500">Diskon</span><span x-text="'-' + rp(discount)"></span></div>
                        <div class="flex justify-between text-base font-extrabold"><span>Total</span><span x-text="rp(total())"></span></div>
                        <div class="flex justify-between text-base font-extrabold text-emerald-700" x-show="method === 'cash'"><span>Kembalian</span><span x-text="rp(change())"></span></div>
                        <div class="flex justify-between font-bold text-amber-700" x-show="method === 'bon'"><span>Sisa bon</span><span x-text="rp(Math.max(0, total() - Number(downPayment || 0)))"></span></div>
                    </div>

                    <div x-show="error" class="rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700" x-text="error"></div>
                </div>

                <div class="border-t border-charcoal-100 px-5 pb-[calc(1rem+env(safe-area-inset-bottom))] pt-3">
                    <button type="button" @click="submit()" :disabled="saving"
                            class="h-14 w-full rounded-xl bg-emerald-600 text-[17px] font-extrabold text-white shadow-lg shadow-emerald-600/30 disabled:opacity-50">
                        <span x-show="!saving">Selesaikan Pembayaran</span><span x-show="saving">Menyimpan…</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal pelanggan baru --}}
    <template x-teleport="body">
        <div x-show="newCustomerOpen" x-cloak class="fixed inset-0 z-[95] flex items-end justify-center bg-charcoal-950/60 p-0 sm:items-center sm:p-4">
            <div class="w-full max-w-md rounded-t-3xl bg-white p-5 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:rounded-3xl" @click.outside="newCustomerOpen = false">
                <h3 class="text-lg font-bold text-charcoal-900">Pelanggan baru</h3>
                <div class="mt-3 space-y-3">
                    <input type="text" x-model="newCustomer.name" placeholder="Nama *" class="form-input">
                    <input type="tel" x-model="newCustomer.phone" placeholder="No. WhatsApp (08…)" class="form-input">
                    <input type="text" x-model="newCustomer.address" placeholder="Alamat" class="form-input">
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button type="button" class="btn-secondary" @click="newCustomerOpen = false">Batal</button>
                    <button type="button" class="btn-primary" @click="saveCustomer()">Simpan</button>
                </div>
            </div>
        </div>
    </template>
</div>

<style>
    .pos-dashboard .pos-product { min-height: 104px; }
    .pos-dashboard .card { box-shadow: 0 1px 3px rgb(15 23 42 / .05); }
    .pos-dashboard select,
    .pos-dashboard input { outline: none; }
    @media (min-width: 640px) {
        .pos-dashboard .pos-product { min-height: 96px; }
    }
    @media (min-width: 1024px) {
        .pos-dashboard .pos-product { min-height: 100px; }
    }
</style>

@push('scripts')
<script>
function posApp() {
    return {
        q: '', results: [], loading: false, cart: [], error: '', saving: false,
        customers: @json($customers), customerId: '', method: 'cash',
        discount: 0, cashReceived: null, downPayment: null,
        newCustomerOpen: false, newCustomer: { name: '', phone: '', address: '' },
        checkoutOpen: false,

        init() { this.search(); this.$refs.search.focus(); },
        rp(v) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(Number(v || 0))); },
        num(v) { return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(v || 0)); },

        async search() {
            this.loading = true;
            try {
                const res = await fetch(@json(route('store.pos.search')) + '?q=' + encodeURIComponent(this.q), { headers: { Accept: 'application/json' } });
                this.results = await res.json();
            } finally { this.loading = false; }
        },
        addFirst() {
            // Scanner barcode mengetik lalu Enter: tambahkan hasil pertama
            if (this.results.length) { this.add(this.results[0]); this.q = ''; this.search(); }
        },
        add(m) {
            const unit = m.units[0];
            const existing = this.cart.find(r => r.id === m.id && r.unit === unit.name);
            if (existing) { existing.qty = +(existing.qty + 1).toFixed(2); this.reprice(existing); return; }
            const row = { key: m.id + '-' + Date.now(), id: m.id, name: m.name, units: m.units, unit: unit.name, qty: 1,
                          price: unit.price, stock: m.stock, wholesale_price: m.wholesale_price, wholesale_min_qty: m.wholesale_min_qty,
                          manualPrice: false, isWholesale: false };
            this.cart.push(row);
            this.reprice(row);
        },
        factor(row) { return Number((row.units.find(u => u.name === row.unit) || { factor: 1 }).factor); },
        reprice(row, unitChanged = false) {
            if (unitChanged) row.manualPrice = false;
            if (row.manualPrice) return;
            const unit = row.units.find(u => u.name === row.unit) || row.units[0];
            const qtyBase = row.qty * Number(unit.factor);
            row.isWholesale = !!(row.wholesale_price && row.wholesale_min_qty && qtyBase >= row.wholesale_min_qty && Number(unit.factor) === 1);
            row.price = row.isWholesale ? row.wholesale_price : unit.price;
        },
        stockLeft(row) {
            const used = this.cart.filter(r => r.id === row.id).reduce((s, r) => s + r.qty * this.factor(r), 0);
            return row.stock - used;
        },
        inCart(id) { return this.cart.filter(r => r.id === id).reduce((s, r) => s + Number(r.qty || 0), 0); },
        totalQtyLabel() { return this.cart.length ? this.cart.length + ' barang di keranjang' : 'Keranjang kosong'; },
        openCheckout() {
            this.error = '';
            if (this.cart.some(r => this.stockLeft(r) < 0)) { this.error = 'Ada barang yang stoknya kurang. Kurangi jumlahnya dulu.'; }
            if (this.cashReceived === null || this.cashReceived === '') this.cashReceived = null;
            this.checkoutOpen = true;
        },
        subtotal() { return this.cart.reduce((s, r) => s + r.qty * r.price, 0); },
        total() { return Math.max(0, this.subtotal() - Number(this.discount || 0)); },
        change() { return Math.max(0, Number(this.cashReceived || 0) - this.total()); },
        quickCash() {
            const t = this.total(); if (!t) return [];
            const opts = [t, Math.ceil(t / 10000) * 10000, Math.ceil(t / 50000) * 50000, Math.ceil(t / 100000) * 100000];
            return [...new Set(opts)].slice(0, 4);
        },

        async saveCustomer() {
            if (!this.newCustomer.name.trim()) return;
            const res = await fetch(@json(route('store.customers.store')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                body: JSON.stringify(this.newCustomer),
            });
            if (!res.ok) { this.error = 'Gagal menyimpan pelanggan.'; return; }
            const c = await res.json();
            this.customers.push(c); this.customerId = String(c.id);
            this.newCustomer = { name: '', phone: '', address: '' }; this.newCustomerOpen = false;
        },

        async submit() {
            this.error = '';
            if (this.method === 'bon' && !this.customerId) { this.error = 'Pilih pelanggan untuk penjualan bon.'; return; }
            if (this.method === 'cash' && this.cashReceived !== null && this.cashReceived !== '' && Number(this.cashReceived) < this.total()) {
                this.error = 'Uang diterima kurang dari total.'; return;
            }
            this.saving = true;
            try {
                const res = await fetch(@json(route('store.pos.store')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                    body: JSON.stringify({
                        customer_id: this.customerId || null, payment_method: this.method,
                        discount_amount: this.discount || 0,
                        cash_received: this.method === 'cash' ? (this.cashReceived || this.total()) : null,
                        down_payment: this.method === 'bon' ? (this.downPayment || 0) : null,
                        items: this.cart.map(r => ({ material_id: r.id, unit_name: r.unit, qty: r.qty, price: r.price })),
                    }),
                });
                const data = await res.json();
                if (!res.ok) { this.error = data.message || Object.values(data.errors || {})[0]?.[0] || 'Gagal menyimpan.'; return; }
                window.location = data.receipt_url;
            } catch (e) {
                this.error = 'Koneksi bermasalah, coba lagi.';
            } finally { this.saving = false; }
        },
    };
}
</script>
@endpush
@endsection
