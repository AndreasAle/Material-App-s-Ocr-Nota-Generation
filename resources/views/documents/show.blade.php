@extends('layouts.app')
@section('content')

<x-page-header :title="$document->title" subtitle="Detail dokumen proyek">
    <x-slot name="actions">
        <a href="{{ route('documents.index', ['project_id' => $document->project_id]) }}" class="btn-secondary">← Kembali</a>
        @if($fileExists)
            <a href="{{ route('documents.download', $document) }}" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 16.5v1.125A2.625 2.625 0 0 0 7.125 20.25h9.75A2.625 2.625 0 0 0 19.5 17.625V16.5"/>
                </svg>
                Unduh
            </a>
        @endif
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="lg:col-span-1">
        <x-data-card title="Informasi">
            <dl class="px-5 py-4 space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-slate-400">Proyek</dt>
                    <dd class="font-semibold text-slate-800">{{ $document->project->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Kategori</dt>
                    <dd><span class="chip">{{ $document->category_label }}</span></dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Nama File</dt>
                    <dd class="text-slate-700 break-all">{{ $document->file_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Ukuran / Tipe</dt>
                    <dd class="text-slate-700">{{ $document->file_size_human }} · {{ $document->file_mime ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Diupload oleh</dt>
                    <dd class="text-slate-700">{{ $document->uploader->name ?? '—' }} · {{ $document->created_at->format('d M Y H:i') }}</dd>
                </div>
                @if($document->description)
                    <div>
                        <dt class="text-xs text-slate-400">Deskripsi</dt>
                        <dd class="text-slate-700 whitespace-pre-line">{{ $document->description }}</dd>
                    </div>
                @endif
            </dl>
            <div class="px-5 py-4 border-t border-slate-100">
                <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Hapus dokumen ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm font-medium text-rose-600 hover:text-rose-800">Hapus dokumen</button>
                </form>
            </div>
        </x-data-card>
    </div>

    <div class="lg:col-span-2">
        <x-data-card title="Pratinjau">
            <div class="p-4">
                @if(! $fileExists)
                    <div class="rounded-xl bg-rose-50 px-4 py-6 text-center text-sm text-rose-700">
                        File fisik tidak ditemukan di server. Silakan upload ulang dokumen ini.
                    </div>
                @elseif(str_starts_with((string) $document->file_mime, 'image/'))
                    <img src="{{ route('documents.preview', $document) }}" alt="{{ $document->title }}" class="mx-auto max-h-[75vh] rounded-lg">
                @elseif($document->file_mime === 'application/pdf')
                    <iframe src="{{ route('documents.preview', $document) }}" class="h-[75vh] w-full rounded-lg border border-slate-200"></iframe>
                @else
                    <div class="rounded-xl bg-slate-50 px-4 py-10 text-center text-sm text-slate-500">
                        Pratinjau tidak tersedia untuk tipe file ini. Gunakan tombol <strong>Unduh</strong>.
                    </div>
                @endif
            </div>
        </x-data-card>
    </div>
</div>

@endsection
