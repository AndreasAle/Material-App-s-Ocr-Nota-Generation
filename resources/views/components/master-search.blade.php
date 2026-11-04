@props([
    'action' => '',
    'value' => '',
    'placeholder' => 'Cari data...'
])

<form method="GET" action="{{ $action }}" class="mb-4">
    <div class="flex flex-col md:flex-row gap-3">
        <input
            type="text"
            name="q"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-slate-500 focus:outline-none"
        >
        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
            Cari
        </button>
    </div>
</form>