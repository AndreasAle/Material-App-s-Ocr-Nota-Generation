@props(['title' => 'Cara pakai halaman ini', 'open' => false])

{{-- Panduan singkat per halaman: bisa dibuka/tutup, status diingat per halaman di HP pengguna --}}
<details class="help-box" @if($open) open @endif
         x-data="{
             k: 'help:' + location.pathname,
             load(el) { try { const v = localStorage.getItem(this.k); if (v !== null) el.open = v === '1'; } catch (e) {} },
             save(el) { try { localStorage.setItem(this.k, el.open ? '1' : '0'); } catch (e) {} },
         }"
         x-init="load($el)"
         @toggle="save($el)">
    <summary>
        <svg class="h-5 w-5 shrink-0 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/></svg>
        <span class="flex-1">{{ $title }}</span>
        <svg class="h-4 w-4 shrink-0 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
    </summary>
    <ol>{{ $slot }}</ol>
</details>
