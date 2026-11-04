@extends('layouts.app')
@section('content')

<x-page-header title="Upload Dokumen" subtitle="Tambah dokumen baru ke proyek">
    <x-slot name="actions">
        <a href="{{ route('documents.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<div class="max-w-2xl">
    <x-data-card title="Detail Dokumen" subtitle="Pilih proyek, kategori, dan file yang akan diupload">
        <div class="px-6 py-5">
            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="form-label-required">Proyek</label>
                    <select name="project_id" class="form-select @error('project_id') border-rose-400 @enderror">
                        <option value="">— Pilih Proyek —</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ old('project_id', $projectId) == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label-required">Judul Dokumen</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           class="form-input @error('title') border-rose-400 @enderror"
                           placeholder="Laporan Progress Mei 2026...">
                    @error('title')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label-required">Kategori</label>
                    <select name="category" class="form-select">
                        @foreach(['general' => 'Umum', 'rab' => 'RAB', 'spk' => 'SPK / Kontrak', 'laporan' => 'Laporan', 'gambar' => 'Gambar / Denah', 'invoice' => 'Invoice'] as $val => $label)
                            <option value="{{ $val }}" {{ old('category', 'general') == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label-required">File <span class="text-slate-400 font-normal">(maks 20 MB)</span></label>
                    <div class="rounded-xl border-2 border-dashed border-slate-200 p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/30 transition-colors cursor-pointer"
                         onclick="document.getElementById('file-input').click()">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-600">Klik untuk pilih file atau drag & drop</p>
                        <p class="text-xs text-slate-400 mt-1">PDF, Word, Excel, PowerPoint, gambar (JPG/PNG/WEBP), DWG, ZIP/RAR, TXT/CSV</p>
                        <p id="file-name" class="mt-2 text-xs text-indigo-600 font-semibold hidden"></p>
                    </div>
                    <input type="file" id="file-input" name="file" class="hidden" required
                           onchange="document.getElementById('file-name').textContent = this.files[0]?.name; document.getElementById('file-name').classList.remove('hidden')">
                    @error('file')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="3" class="form-input resize-none"
                              placeholder="Keterangan singkat tentang dokumen ini...">{{ old('description') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-1">
                    <a href="{{ route('documents.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                        </svg>
                        Upload Dokumen
                    </button>
                </div>
            </form>
        </div>
    </x-data-card>
</div>

@endsection
