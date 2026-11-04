@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                        Activity Logs
                    </div>

                    <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900">
                        Aktivitas Sistem
                    </h1>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Pantau aktivitas penting pada transaksi pembelian dan hasil scan nota agar alur aplikasi terasa lebih lengkap dan mudah diaudit.
                    </p>
                </div>

                <form method="GET" action="{{ route('activity-logs.index') }}" class="grid w-full grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_220px_auto] xl:max-w-[520px]">
                    <input
                        type="text"
                        name="keyword"
                        value="{{ $filters['keyword'] }}"
                        placeholder="Cari aktivitas..."
                        class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                    >

                    <select
                        name="group"
                        class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                    >
                        <option value="">Semua Aktivitas</option>
                        <option value="transaction" {{ $filters['group'] === 'transaction' ? 'selected' : '' }}>Transaksi</option>
                        <option value="scan" {{ $filters['group'] === 'scan' ? 'selected' : '' }}>Scan Nota</option>
                    </select>

                    <button
                        type="submit"
                        class="inline-flex h-12 items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:bg-slate-800"
                    >
                        Filter
                    </button>
                </form>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Total Aktivitas</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">{{ $summary['total'] }}</div>
                <p class="mt-2 text-sm text-slate-500">Semua log yang tampil sesuai filter.</p>
            </div>

            <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Transaksi</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight text-indigo-700">{{ $summary['transaction'] }}</div>
                <p class="mt-2 text-sm text-slate-500">Aktivitas terkait pembelian material.</p>
            </div>

            <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Scan Nota</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight text-emerald-700">{{ $summary['scan'] }}</div>
                <p class="mt-2 text-sm text-slate-500">Upload, proses, review, dan import scan.</p>
            </div>

            <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Hari Ini</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight text-amber-600">{{ $summary['today'] }}</div>
                <p class="mt-2 text-sm text-slate-500">Aktivitas yang tercatat hari ini.</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-bold tracking-tight text-slate-900">Timeline Aktivitas</h2>
                <p class="mt-1 text-sm text-slate-500">Urutan aktivitas terbaru dari seluruh proses pembelian dan scan nota.</p>
            </div>

            <div class="p-5 sm:p-6">
                @if ($activities->count())
                    <div class="space-y-4">
                        @foreach ($activities as $activity)
                            @php
                                $badgeClass = match ($activity['type']) {
                                    'transaction_created' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
                                    'transaction_updated' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
                                    'scan_uploaded' => 'border-slate-200 bg-slate-50 text-slate-700',
                                    'scan_processed' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    'scan_reviewed' => 'border-amber-200 bg-amber-50 text-amber-700',
                                    'scan_imported' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
                                    'scan_failed' => 'border-rose-200 bg-rose-50 text-rose-700',
                                    default => 'border-slate-200 bg-slate-50 text-slate-700',
                                };

                                $groupLabel = $activity['group'] === 'transaction' ? 'Transaksi' : 'Scan';
                            @endphp

                            <article class="rounded-[24px] border border-slate-200 bg-slate-50/70 p-4 sm:p-5 transition hover:bg-white hover:shadow-sm">
                                <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_260px]">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] {{ $badgeClass }}">
                                                {{ str_replace('_', ' ', $activity['type']) }}
                                            </span>

                                            <span class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                                                {{ $groupLabel }}
                                            </span>
                                        </div>

                                        <h3 class="mt-3 text-lg font-bold tracking-tight text-slate-900">
                                            {{ $activity['title'] }}
                                        </h3>

                                        <p class="mt-2 text-sm leading-6 text-slate-600">
                                            {{ $activity['description'] }}
                                        </p>

                                        @if(!empty($activity['meta']))
                                            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                                @foreach ($activity['meta'] as $label => $value)
                                                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                                                            {{ $label }}
                                                        </div>
                                                        <div class="mt-1 break-words text-sm font-bold text-slate-900">
                                                            {{ $value }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <aside class="xl:border-l xl:border-slate-200 xl:pl-4">
                                        <div class="grid grid-cols-1 gap-3">
                                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                                <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                                                    Pelaku
                                                </div>
                                                <div class="mt-1 text-sm font-bold text-slate-900">
                                                    {{ $activity['actor'] ?? 'System' }}
                                                </div>
                                            </div>

                                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                                <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                                                    Waktu
                                                </div>
                                                <div class="mt-1 text-sm font-bold text-slate-900">
                                                    {{ optional($activity['created_at'])->translatedFormat('d M Y, H:i') ?? '-' }}
                                                </div>
                                            </div>

                                            @if (!empty($activity['url']))
                                                <a
                                                    href="{{ $activity['url'] }}"
                                                    class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                                                >
                                                    Lihat Detail
                                                </a>
                                            @endif
                                        </div>
                                    </aside>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $activities->links() }}
                    </div>
                @else
                    <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2A9 9 0 1 1 3 12a9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Belum ada aktivitas</h3>
                        <p class="mt-2 text-sm text-slate-500">
                            Aktivitas akan tampil di sini setelah ada transaksi atau scan nota yang diproses.
                        </p>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection