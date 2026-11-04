@extends('layouts.app')

@section('title', 'Perbandingan Proyek')

@section('content')

{{-- ====================================================================== --}}
{{-- PAGE HEADER                                                              --}}
{{-- ====================================================================== --}}
<div class="mb-7">
    <div class="flex items-center gap-2 mb-2">
        <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
            </svg>
        </div>
        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Laporan Analitik</span>
    </div>
    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 leading-tight">
        Perbandingan Proyek
    </h1>
    <p class="mt-1 text-sm text-slate-500">
        Bandingkan performa finansial hingga 4 proyek — pengeluaran, revenue, profit, dan utilisasi anggaran
    </p>
</div>

{{-- ====================================================================== --}}
{{-- STEP INDICATOR                                                           --}}
{{-- ====================================================================== --}}
<div class="flex items-center gap-0 mb-7">
    <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white text-sm font-extrabold flex items-center justify-center shadow-md shadow-indigo-200">
            1
        </div>
        <div>
            <p class="text-sm font-bold text-indigo-700 leading-none">Pilih Proyek</p>
            <p class="text-xs text-indigo-400 mt-0.5">Centang 2–4 proyek</p>
        </div>
    </div>
    <div class="flex-1 mx-4 h-px bg-gradient-to-r from-indigo-300 to-slate-200 max-w-20"></div>
    <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-400 text-sm font-extrabold flex items-center justify-center">
            2
        </div>
        <div>
            <p class="text-sm font-semibold text-slate-400 leading-none">Lihat Perbandingan</p>
            <p class="text-xs text-slate-300 mt-0.5">Analisis & grafik</p>
        </div>
    </div>
</div>

{{-- Flash error --}}

{{-- ====================================================================== --}}
{{-- SELECTION FORM — Alpine.js                                               --}}
{{-- ====================================================================== --}}
<div x-data="{
        selected: [],
        maxSelect: 4,
        minSelect: 2,
        showError: false,
        toggle(id) {
            const i = this.selected.indexOf(id);
            if (i === -1) {
                if (this.selected.length < this.maxSelect) {
                    this.selected.push(id);
                    this.showError = false;
                }
            } else {
                this.selected.splice(i, 1);
                this.showError = false;
            }
        },
        isSelected(id) { return this.selected.includes(id); },
        isDisabled(id) { return this.selected.length >= this.maxSelect && !this.isSelected(id); },
        submit() {
            if (this.selected.length < this.minSelect) {
                this.showError = true;
                return;
            }
            this.showError = false;
            this.$refs.form.submit();
        }
    }">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ============================================================== --}}
        {{-- LEFT PANEL — Selection Slots + Guide                            --}}
        {{-- ============================================================== --}}
        <div class="lg:col-span-1">

            {{-- Selection Slots Visual --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 mb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-800">Proyek Terpilih</h3>
                    <span class="text-xs font-semibold text-slate-400"
                          x-text="selected.length + ' / ' + maxSelect"></span>
                </div>

                {{-- 4 Slots --}}
                <div class="space-y-2.5">
                    @php
                        $slotColors = [
                            ['bg' => 'bg-indigo-600',  'light' => 'bg-indigo-50',  'text' => 'text-indigo-700',  'ring' => 'ring-indigo-300'],
                            ['bg' => 'bg-emerald-600', 'light' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-300'],
                            ['bg' => 'bg-amber-500',   'light' => 'bg-amber-50',   'text' => 'text-amber-700',   'ring' => 'ring-amber-300'],
                            ['bg' => 'bg-rose-600',    'light' => 'bg-rose-50',    'text' => 'text-rose-700',    'ring' => 'ring-rose-300'],
                        ];
                        $projectMap = $projects->keyBy('id');
                    @endphp

                    @foreach ($slotColors as $slot => $sc)
                    <div class="flex items-center gap-3 p-2.5 rounded-xl transition-all"
                         :class="selected.length > {{ $slot }}
                             ? '{{ $sc['light'] }} ring-1 {{ $sc['ring'] }}'
                             : 'bg-slate-50 border border-dashed border-slate-200'">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-all"
                             :class="selected.length > {{ $slot }} ? '{{ $sc['bg'] }}' : 'bg-slate-200'">
                            <template x-if="selected.length > {{ $slot }}">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                            </template>
                            <template x-if="selected.length <= {{ $slot }}">
                                <span class="text-xs font-bold text-slate-400">{{ $slot + 1 }}</span>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold truncate"
                               :class="selected.length > {{ $slot }} ? '{{ $sc['text'] }}' : 'text-slate-400'">
                                <span x-show="selected.length > {{ $slot }}" x-text="
                                    @js($projectMap->map(fn($p) => $p->name))[selected[{{ $slot }}]] ?? 'Proyek {{ $slot + 1 }}'
                                "></span>
                                <span x-show="selected.length <= {{ $slot }}">Slot Proyek {{ $slot + 1 }}</span>
                            </p>
                        </div>
                        <button type="button"
                                x-show="selected.length > {{ $slot }}"
                                @click="selected.splice({{ $slot }}, 1)"
                                class="w-5 h-5 rounded-full bg-white/70 flex items-center justify-center hover:bg-white transition-colors shrink-0">
                            <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    @endforeach
                </div>

                {{-- Progress bar --}}
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-2 rounded-full transition-all duration-300"
                             :style="'width: ' + (selected.length / maxSelect * 100) + '%'"></div>
                    </div>
                    <p class="text-xs text-slate-400 mt-1.5 text-center"
                       x-text="selected.length < minSelect
                           ? 'Pilih minimal ' + minSelect + ' proyek lagi'
                           : selected.length + ' proyek siap dibandingkan'"></p>
                </div>
            </div>

            {{-- Guide Info --}}
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4">
                <h4 class="text-xs font-bold text-indigo-700 uppercase tracking-wide mb-2.5">Cara Menggunakan</h4>
                <div class="space-y-2">
                    @foreach ([
                        ['1', 'Centang proyek yang ingin dibandingkan di panel kanan'],
                        ['2', 'Pilih minimal 2 dan maksimal 4 proyek'],
                        ['3', 'Klik tombol "Bandingkan Sekarang"'],
                        ['4', 'Lihat grafik dan tabel perbandingan lengkap'],
                    ] as [$num, $text])
                    <div class="flex items-start gap-2">
                        <span class="w-4 h-4 rounded-full bg-indigo-200 text-indigo-700 text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">{{ $num }}</span>
                        <p class="text-xs text-indigo-600 leading-relaxed">{{ $text }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Metrics Preview --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 mt-4">
                <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-3">Metrik yang Dibandingkan</h4>
                <div class="space-y-1.5">
                    @foreach ([
                        ['💰', 'Budget RAB & Pengeluaran'],
                        ['📈', 'Revenue & Profit'],
                        ['📊', 'Margin & Utilisasi Anggaran'],
                        ['📋', 'Transaksi & Purchase Order'],
                        ['🏗️', 'RAB & Material Unik'],
                    ] as [$icon, $label])
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span>{{ $icon }}</span>
                        <span>{{ $label }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- RIGHT PANEL — Project List                                       --}}
        {{-- ============================================================== --}}
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Daftar Proyek Aktif</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $projects->count() }} proyek tersedia</p>
                    </div>
                    {{-- Inline error --}}
                    <div x-show="showError" x-transition
                         class="inline-flex items-center gap-1.5 rounded-xl bg-rose-50 border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-700">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                        </svg>
                        Pilih minimal 2 proyek
                    </div>
                </div>

                @if ($projects->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                    <div class="w-16 h-16 rounded-3xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 11.204 3.045c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-500 mb-1">Belum ada proyek aktif</h3>
                    <p class="text-sm text-slate-400 max-w-xs leading-relaxed">Aktifkan proyek terlebih dahulu untuk dapat membandingkannya di sini</p>
                    <a href="{{ route('projects.index') }}"
                       class="mt-5 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-all">
                        Kelola Proyek
                    </a>
                </div>
                @else

                {{-- Search & Select All (decorative / optional) --}}
                <div class="px-5 py-3 border-b border-slate-50 bg-slate-50/30">
                    <p class="text-xs text-slate-400">
                        <span class="font-semibold text-slate-600" x-text="selected.length"></span>
                        dari {{ $projects->count() }} proyek dipilih
                        <span x-show="selected.length >= maxSelect" class="ml-1.5 font-semibold text-amber-600">(Maksimum tercapai)</span>
                    </p>
                </div>

                <form method="GET" action="{{ route('project-comparison.result') }}" x-ref="form">
                    {{-- Hidden inputs for selected projects --}}
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="project_ids[]" :value="id">
                    </template>

                    <div class="divide-y divide-slate-50 max-h-[520px] overflow-y-auto">
                        @php
                            $projectColorDots = [
                                'bg-indigo-600', 'bg-emerald-600', 'bg-amber-500', 'bg-rose-600',
                                'bg-violet-600', 'bg-cyan-600', 'bg-pink-600', 'bg-teal-600',
                            ];
                        @endphp
                        @foreach ($projects as $idx => $p)
                        @php $dotColor = $projectColorDots[$idx % count($projectColorDots)]; @endphp
                        <div class="px-5 py-3 flex items-center gap-4 cursor-pointer transition-all duration-150 group"
                             :class="{
                                 'bg-indigo-50/50 border-l-4 border-indigo-500': isSelected('{{ $p->id }}') && selected.indexOf('{{ $p->id }}') === 0,
                                 'bg-emerald-50/50 border-l-4 border-emerald-500': isSelected('{{ $p->id }}') && selected.indexOf('{{ $p->id }}') === 1,
                                 'bg-amber-50/50 border-l-4 border-amber-500': isSelected('{{ $p->id }}') && selected.indexOf('{{ $p->id }}') === 2,
                                 'bg-rose-50/50 border-l-4 border-rose-500': isSelected('{{ $p->id }}') && selected.indexOf('{{ $p->id }}') === 3,
                                 'hover:bg-slate-50 border-l-4 border-transparent': !isSelected('{{ $p->id }}') && !isDisabled('{{ $p->id }}'),
                                 'opacity-40 cursor-not-allowed border-l-4 border-transparent': isDisabled('{{ $p->id }}')
                             }"
                             @click="!isDisabled('{{ $p->id }}') && toggle('{{ $p->id }}')">

                            {{-- Custom Checkbox --}}
                            <div class="w-5 h-5 rounded-md border-2 flex items-center justify-center shrink-0 transition-all"
                                 :class="isSelected('{{ $p->id }}')
                                     ? 'bg-indigo-600 border-indigo-600'
                                     : 'border-slate-300 bg-white group-hover:border-indigo-400'">
                                <svg x-show="isSelected('{{ $p->id }}')" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                            </div>

                            {{-- Project avatar dot --}}
                            <div class="w-9 h-9 rounded-xl {{ $dotColor }} flex items-center justify-center text-white text-xs font-extrabold shrink-0">
                                {{ strtoupper(mb_substr($p->name, 0, 2)) }}
                            </div>

                            {{-- Project info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-slate-800 truncate">{{ $p->name }}</p>
                                    @if ($p->code)
                                    <span class="inline-block shrink-0 rounded-lg bg-slate-100 border border-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-500">
                                        {{ $p->code }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Selection indicator --}}
                            <div x-show="isSelected('{{ $p->id }}')" class="shrink-0">
                                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 border border-indigo-200 px-2.5 py-1 text-xs font-bold text-indigo-700">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                    </svg>
                                    Slot
                                    <span x-text="selected.indexOf('{{ $p->id }}') + 1"></span>
                                </span>
                            </div>
                            <div x-show="!isSelected('{{ $p->id }}') && !isDisabled('{{ $p->id }}')" class="shrink-0">
                                <span class="text-xs text-slate-300 group-hover:text-slate-500 transition-colors font-medium">Pilih</span>
                            </div>
                            <div x-show="isDisabled('{{ $p->id }}')" class="shrink-0">
                                <span class="text-xs text-slate-300 font-medium">Penuh</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </form>

                {{-- Bottom Action Bar --}}
                <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-700"
                               x-text="selected.length + ' proyek dipilih'"></p>
                            <p class="text-xs text-slate-400 mt-0.5"
                               x-show="selected.length < minSelect">Pilih {{ 2 }} proyek lagi untuk melanjutkan</p>
                            <p class="text-xs text-emerald-600 font-medium mt-0.5"
                               x-show="selected.length >= minSelect">Siap untuk dibandingkan!</p>
                        </div>
                        <button type="button"
                                @click="submit()"
                                :disabled="selected.length < minSelect"
                                :class="selected.length >= minSelect
                                    ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-200 active:scale-95'
                                    : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                class="inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-bold transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                            </svg>
                            Bandingkan Sekarang
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>{{-- /grid --}}
</div>{{-- /alpine --}}

@endsection
