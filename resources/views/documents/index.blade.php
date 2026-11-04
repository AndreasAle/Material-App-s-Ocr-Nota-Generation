@extends('layouts.app')
@section('content')

<x-page-header title="Dokumen Proyek" subtitle="Kelola dokumen & lampiran per proyek">
    <x-slot name="actions">
        <a href="{{ route('documents.create') }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
            </svg>
            Upload Dokumen
        </a>
    </x-slot>
</x-page-header>

{{-- Filter --}}
<x-data-card class="mb-5">
    <form method="GET" class="px-5 py-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="form-label text-xs">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Judul dokumen..."
                   class="form-input">
        </div>
        <div class="min-w-[160px]">
            <label class="form-label text-xs">Proyek</label>
            <select name="project_id" class="form-select">
                <option value="">Semua Proyek</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Kategori</label>
            <select name="category" class="form-select">
                <option value="">Semua</option>
                @foreach(['general' => 'Umum', 'rab' => 'RAB', 'spk' => 'SPK / Kontrak', 'laporan' => 'Laporan', 'gambar' => 'Gambar', 'invoice' => 'Invoice'] as $val => $label)
                    <option value="{{ $val }}" {{ request('category') == $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
            </svg>
            Filter
        </button>
        @if(request()->hasAny(['search','project_id','category']))
            <a href="{{ route('documents.index') }}" class="btn-secondary">Reset</a>
        @endif
    </form>
</x-data-card>

<x-data-card>
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Dokumen</th>
                    <th>Proyek</th>
                    <th class="th-center">Kategori</th>
                    <th class="th-right">Ukuran</th>
                    <th>Upload oleh</th>
                    <th>Tanggal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 shrink-0 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                    </svg>
                                </div>
                                <div>
                                    <a href="{{ route('documents.show', $doc) }}" class="font-semibold text-slate-900 hover:text-indigo-600">{{ $doc->title }}</a>
                                    <div class="text-xs text-slate-400">{{ $doc->file_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-slate-700">{{ $doc->project->name ?? '—' }}</td>
                        <td class="text-center">
                            <span class="chip">{{ $doc->category_label }}</span>
                        </td>
                        <td class="text-right text-slate-600 text-xs">{{ $doc->file_size_human }}</td>
                        <td class="text-xs text-slate-600">{{ $doc->uploader->name ?? '—' }}</td>
                        <td class="text-xs text-slate-500">{{ $doc->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="flex items-center gap-2 justify-end">
                                <a href="{{ route('documents.download', $doc) }}"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 16.5v1.125A2.625 2.625 0 0 0 7.125 20.25h9.75A2.625 2.625 0 0 0 19.5 17.625V16.5"/>
                                    </svg>
                                    Unduh
                                </a>
                                <form method="POST" action="{{ route('documents.destroy', $doc) }}"
                                      onsubmit="return confirm('Hapus dokumen ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="p-1 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-14 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                </svg>
                            </div>
                            <p class="font-semibold text-slate-600">Belum ada dokumen</p>
                            <p class="text-sm text-slate-400 mt-1">
                                <a href="{{ route('documents.create') }}" class="text-indigo-600 hover:underline">Upload sekarang</a>
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-slate-100">{{ $documents->links() }}</div>
</x-data-card>

@endsection
