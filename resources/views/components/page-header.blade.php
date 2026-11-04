@props(['title', 'subtitle' => null, 'back' => null])

<div class="mb-4 flex flex-col gap-3 sm:mb-6 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex min-w-0 flex-1 items-start gap-2">
        @if($back)
        <a href="{{ $back }}" class="-ml-1 mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-charcoal-600 hover:bg-charcoal-100" aria-label="Kembali">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
        </a>
        @endif
        <div class="min-w-0">
            <h1 class="text-[22px] font-extrabold leading-tight tracking-tight text-charcoal-900 sm:text-2xl">{{ $title }}</h1>
            @if($subtitle)
            <p class="mt-1 text-sm leading-relaxed text-charcoal-500">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @if(isset($actions))
    {{-- Di HP tombol aksi memenuhi lebar layar agar mudah ditekan --}}
    <div class="grid grid-cols-1 gap-2 empty:hidden min-[400px]:flex min-[400px]:flex-wrap sm:flex-shrink-0 sm:items-center [&>*]:w-full min-[400px]:[&>*]:w-auto">
        {{ $actions }}
    </div>
    @endif
</div>
