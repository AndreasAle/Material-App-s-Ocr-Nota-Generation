@extends('layouts.app')

@section('content')
<x-page-header
    title="Report OCR & Scanner"
    subtitle="Insight performa input scanner, batch review, mismatch supplier, keberhasilan import, serta statistik feedback learning."
/>

<div class="space-y-6">
    {{-- Filter --}}
    <x-data-card class="p-5">
        <form method="GET" action="{{ route('scanner-reports.index') }}" class="flex flex-wrap gap-3 items-end px-5 py-4">
            <div class="flex-1 min-w-[160px]">
                <label class="form-label text-xs">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-input">
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="form-label text-xs">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-input">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">Terapkan</button>
                <a href="{{ route('scanner-reports.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </x-data-card>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-data-card class="p-5">
            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Manual vs Scanner</div>
            <div class="mt-3 space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Manual</span>
                    <span class="font-bold text-slate-900">{{ $scanModeBreakdown['manual'] }}</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                    @php $total = $scanModeBreakdown['manual'] + $scanModeBreakdown['scanner']; @endphp
                    <div class="h-full rounded-full bg-slate-900" style="width: {{ $total > 0 ? max(5, round($scanModeBreakdown['manual'] / $total * 100)) : 0 }}%"></div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Scanner</span>
                    <span class="font-bold text-slate-900">{{ $scanModeBreakdown['scanner'] }}</span>
                </div>
            </div>
        </x-data-card>

        <x-data-card class="p-5">
            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Single vs Multiple</div>
            <div class="mt-3 space-y-2">
                @php $total2 = $scanModeBreakdown['single'] + $scanModeBreakdown['multiple']; @endphp
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Single</span>
                    <span class="font-bold text-slate-900">{{ $scanModeBreakdown['single'] }}</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ $total2 > 0 ? max(5, round($scanModeBreakdown['single'] / $total2 * 100)) : 0 }}%"></div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Multiple</span>
                    <span class="font-bold text-slate-900">{{ $scanModeBreakdown['multiple'] }}</span>
                </div>
            </div>
        </x-data-card>

        <x-data-card class="p-5">
            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">OCR Success Rate</div>
            <div class="mt-3 flex items-center gap-4">
                <div class="relative h-20 w-20">
                    <svg viewBox="0 0 36 36" class="h-20 w-20 -rotate-90">
                        <path d="M18 2.5 a 15.5 15.5 0 1 1 0 31 a 15.5 15.5 0 1 1 0 -31" fill="none" stroke="#e5e7eb" stroke-width="3.5" />
                        <path d="M18 2.5 a 15.5 15.5 0 1 1 0 31 a 15.5 15.5 0 1 1 0 -31" fill="none" stroke="#10b981" stroke-width="3.5" stroke-dasharray="{{ $ocrSuccessRate }}, 100" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center text-sm font-extrabold text-slate-900">{{ $ocrSuccessRate }}%</div>
                </div>
                <div class="text-sm text-slate-600">Tingkat draft/batch yang berhasil diproses.</div>
            </div>
        </x-data-card>

        <x-data-card class="p-5">
            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Import Success Rate</div>
            <div class="mt-3 flex items-center gap-4">
                <div class="relative h-20 w-20">
                    <svg viewBox="0 0 36 36" class="h-20 w-20 -rotate-90">
                        <path d="M18 2.5 a 15.5 15.5 0 1 1 0 31 a 15.5 15.5 0 1 1 0 -31" fill="none" stroke="#e5e7eb" stroke-width="3.5" />
                        <path d="M18 2.5 a 15.5 15.5 0 1 1 0 31 a 15.5 15.5 0 1 1 0 -31" fill="none" stroke="#6366f1" stroke-width="3.5" stroke-dasharray="{{ $importSuccessRate }}, 100" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center text-sm font-extrabold text-slate-900">{{ $importSuccessRate }}%</div>
                </div>
                <div class="text-sm text-slate-600">Tingkat scan yang berhasil jadi transaksi.</div>
            </div>
        </x-data-card>
    </div>

    {{-- Feedback Learning Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-data-card class="p-5">
            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Koreksi Dipelajari</div>
            <div class="mt-2 text-3xl font-extrabold text-indigo-600">{{ number_format($correctionStats['total']) }}</div>
            <div class="mt-1 text-xs text-slate-500">pola nama material yang diingat sistem</div>
        </x-data-card>
        <x-data-card class="p-5">
            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total Pemakaian Koreksi</div>
            <div class="mt-2 text-3xl font-extrabold text-emerald-600">{{ number_format($correctionStats['total_usages']) }}</div>
            <div class="mt-1 text-xs text-slate-500">kali koreksi diaplikasikan ulang otomatis</div>
        </x-data-card>
        <x-data-card class="p-5">
            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Duplikat Terdeteksi</div>
            <div class="mt-2 text-3xl font-extrabold text-amber-600">{{ number_format($duplicateCount) }}</div>
            <div class="mt-1 text-xs text-slate-500">scan dengan file yang sama ditemukan</div>
        </x-data-card>
    </div>

    {{-- Single / Batch / Anomali --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <x-data-card class="p-0 overflow-hidden xl:col-span-4">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-bold text-slate-900">Single Scan</h3>
            </div>
            <div class="space-y-3 p-5">
                <div class="flex justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"><span>Total Draft</span><span class="font-bold">{{ $summary['single_draft_total'] }}</span></div>
                <div class="flex justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"><span>Processed</span><span class="font-bold">{{ $summary['single_draft_processed'] }}</span></div>
                <div class="flex justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"><span>Reviewed</span><span class="font-bold">{{ $summary['single_draft_reviewed'] }}</span></div>
                <div class="flex justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"><span>Imported</span><span class="font-bold">{{ $summary['single_draft_imported'] }}</span></div>
                <div class="flex justify-between rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3"><span>Failed</span><span class="font-bold text-rose-600">{{ $summary['single_draft_failed'] }}</span></div>
            </div>
        </x-data-card>

        <x-data-card class="p-0 overflow-hidden xl:col-span-4">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-bold text-slate-900">Multiple Notes</h3>
            </div>
            <div class="space-y-3 p-5">
                <div class="flex justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"><span>Total Batch</span><span class="font-bold">{{ $summary['batch_total'] }}</span></div>
                <div class="flex justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"><span>Processed</span><span class="font-bold">{{ $summary['batch_processed'] }}</span></div>
                <div class="flex justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"><span>Reviewed</span><span class="font-bold">{{ $summary['batch_reviewed'] }}</span></div>
                <div class="flex justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"><span>Imported</span><span class="font-bold">{{ $summary['batch_imported'] }}</span></div>
            </div>
        </x-data-card>

        <x-data-card class="p-0 overflow-hidden xl:col-span-4">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-bold text-slate-900">Anomali Scanner</h3>
            </div>
            <div class="space-y-3 p-5">
                <div class="flex justify-between rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3"><span>Supplier Mismatch</span><span class="font-bold text-amber-700">{{ $summary['batch_supplier_mismatch'] }}</span></div>
                <div class="flex justify-between rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3"><span>Batch Failed</span><span class="font-bold text-rose-600">{{ $summary['batch_failed'] }}</span></div>
                <div class="flex justify-between rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3"><span>Duplikat File</span><span class="font-bold text-amber-600">{{ $duplicateCount }}</span></div>
            </div>
        </x-data-card>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        {{-- Top koreksi yang dipelajari --}}
        <x-data-card class="p-0 overflow-hidden xl:col-span-7">
            <div class="border-b border-slate-200 px-5 py-4 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900">Koreksi Paling Sering Dipelajari</h3>
                <span class="rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                    Feedback Loop
                </span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($correctionStats['top_corrections'] as $correction)
                    <div class="flex items-start justify-between px-5 py-4 gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-mono text-slate-600 truncate max-w-[160px]">
                                    {{ $correction->source_text }}
                                </span>
                                <svg class="h-3.5 w-3.5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                                <span class="font-semibold text-sm text-slate-900 truncate max-w-[200px]">
                                    {{ $correction->corrected_material_name }}
                                </span>
                            </div>
                            <div class="mt-1 text-[11px] text-slate-400">
                                oleh {{ $correction->corrector?->name ?? 'Sistem' }} · terakhir {{ $correction->updated_at->translatedFormat('d M Y') }}
                            </div>
                        </div>
                        <div class="shrink-0 rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">
                            {{ $correction->usage_count }}×
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-slate-500">
                        Belum ada koreksi yang dipelajari. Koreksi akan muncul saat user mengubah nama material di Mode Compare.
                    </div>
                @endforelse
            </div>
        </x-data-card>

        {{-- Per-user activity --}}
        <x-data-card class="p-0 overflow-hidden xl:col-span-5">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-bold text-slate-900">Aktivitas per Pengguna</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($userActivity as $user)
                    <div class="flex items-center justify-between px-5 py-4 gap-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">{{ $user->creator?->name ?? 'User #' . $user->created_by }}</div>
                            <div class="text-xs text-slate-500">{{ $user->total }} scan · {{ $user->imported }} berhasil import</div>
                        </div>
                        <div class="shrink-0">
                            @php $pct = $user->total > 0 ? round($user->imported / $user->total * 100) : 0; @endphp
                            <div class="text-right text-sm font-bold {{ $pct >= 70 ? 'text-emerald-600' : ($pct >= 40 ? 'text-amber-600' : 'text-slate-500') }}">
                                {{ $pct }}%
                            </div>
                            <div class="mt-1 h-1.5 w-24 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $pct >= 70 ? 'bg-emerald-500' : ($pct >= 40 ? 'bg-amber-400' : 'bg-slate-300') }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-slate-500">Belum ada data aktivitas.</div>
                @endforelse
            </div>
        </x-data-card>
    </div>

    {{-- Batch supplier mismatch terbaru --}}
    <x-data-card class="p-0 overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4">
            <h3 class="text-base font-bold text-slate-900">Batch Supplier Mismatch Terbaru</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($topMismatchBatches as $batch)
                <div class="flex items-center justify-between px-5 py-4">
                    <div>
                        <div class="text-sm font-bold text-slate-900">{{ $batch->batch_code }}</div>
                        <div class="text-xs text-slate-500">{{ $batch->created_at?->format('d M Y H:i') }} · {{ $batch->total_files }} file</div>
                    </div>
                    <div class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">supplier_mismatch</div>
                </div>
            @empty
                <div class="px-5 py-6 text-sm text-slate-500">Belum ada batch mismatch.</div>
            @endforelse
        </div>
    </x-data-card>
</div>
@endsection
