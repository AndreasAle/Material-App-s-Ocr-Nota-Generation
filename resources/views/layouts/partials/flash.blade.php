{{-- Pesan hasil aksi: satu tempat untuk semua halaman --}}
@if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)"
         class="flash mb-4 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <span class="flex-1">{{ session('success') }}</span>
        <button type="button" @click="show = false" class="-m-1 p-1 text-emerald-600" aria-label="Tutup">✕</button>
    </div>
@endif

@if(session('error'))
    <div class="flash mb-4 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">
        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
        <span class="flex-1">{{ session('error') }}</span>
    </div>
@endif

@if($errors->any() && ! session('error'))
    <div class="flash mb-4 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
        <div class="flex-1">
            <p class="font-bold">Ada data yang perlu diperbaiki:</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-4">
                @foreach(array_slice($errors->all(), 0, 4) as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
