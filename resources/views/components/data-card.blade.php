@props(['title' => null, 'subtitle' => null, 'actions' => null, 'flush' => false])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-charcoal-200/80 bg-white shadow-sm overflow-hidden']) }}>
    @if($title)
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-charcoal-100 px-4 py-3.5 sm:px-6 sm:py-4">
        <div class="min-w-0">
            <h3 class="text-[15px] font-bold text-charcoal-900">{{ $title }}</h3>
            @if($subtitle)<p class="mt-0.5 text-xs text-charcoal-500">{{ $subtitle }}</p>@endif
        </div>
        @if(isset($actions))
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
        @endif
    </div>
    @endif
    {{ $slot }}
</div>
