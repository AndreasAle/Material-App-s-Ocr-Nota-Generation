{{-- Pilihan periode laporan toko --}}
<x-data-card class="mb-4">
    <form method="GET" class="flex flex-wrap items-end gap-3 px-5 py-4" x-data="{ period: @js($period) }">
        <div>
            <label class="form-label text-xs">Periode</label>
            <select name="period" x-model="period" class="form-select" @change="if (period !== 'custom') $el.form.submit()">
                <option value="today">Hari ini</option>
                <option value="yesterday">Kemarin</option>
                <option value="week">Minggu ini</option>
                <option value="month">Bulan ini</option>
                <option value="last_month">Bulan lalu</option>
                <option value="custom">Pilih tanggal</option>
            </select>
        </div>
        <template x-if="period === 'custom'">
            <div class="flex flex-wrap items-end gap-3">
                <div><label class="form-label text-xs">Dari</label><input type="date" name="from" value="{{ $from }}" class="form-input"></div>
                <div><label class="form-label text-xs">Sampai</label><input type="date" name="to" value="{{ $to }}" class="form-input"></div>
                <button class="btn-primary">Tampilkan</button>
            </div>
        </template>
        <span class="ml-auto text-sm text-slate-500">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</span>
    </form>
</x-data-card>
