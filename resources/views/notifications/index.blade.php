@extends('layouts.app')

@section('content')
<x-page-header title="Notifikasi" subtitle="Riwayat pemberitahuan sistem">
    <x-slot name="actions">
        @if($notifications->total() > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
                Tandai Semua Dibaca
            </button>
        </form>
        @endif
    </x-slot>
</x-page-header>


<x-data-card>
    @if($notifications->isEmpty())
        <div class="py-16 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                </svg>
            </div>
            <p class="font-semibold text-slate-600">Tidak ada notifikasi</p>
            <p class="text-sm text-slate-400 mt-1">Notifikasi akan muncul saat ada aktivitas baru</p>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($notifications as $notif)
            @php
                $colorMap = [
                    'blue'   => 'bg-indigo-100 text-indigo-600',
                    'green'  => 'bg-emerald-100 text-emerald-600',
                    'red'    => 'bg-rose-100 text-rose-600',
                    'yellow' => 'bg-amber-100 text-amber-600',
                    'orange' => 'bg-amber-100 text-amber-600',
                    'purple' => 'bg-indigo-100 text-indigo-600',
                    'gray'   => 'bg-slate-100 text-slate-600',
                ];
                $iconBg = $colorMap[$notif->color_class] ?? 'bg-slate-100 text-slate-600';
                $url    = $notif->data['url'] ?? null;
            @endphp
            <div class="flex items-start gap-4 py-4 px-4 {{ $notif->isUnread() ? 'bg-indigo-50/30' : '' }} hover:bg-slate-50 rounded-xl transition-colors">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $iconBg }}">
                    {{ $notif->icon }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-sm text-slate-800">{{ $notif->title }}</span>
                        @if($notif->isUnread())
                            <span class="w-2 h-2 bg-indigo-500 rounded-full flex-shrink-0"></span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-600 mt-0.5">{{ $notif->message }}</p>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="text-xs text-slate-400">{{ $notif->created_at->diffForHumans() }}</span>
                        @if($url)
                            <a href="{{ $url }}" class="text-xs text-indigo-600 hover:underline">Lihat →</a>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                    @if($notif->isUnread())
                    <form method="POST" action="{{ route('notifications.mark-read', $notif) }}">
                        @csrf
                        <button type="submit" title="Tandai dibaca"
                                class="text-xs text-indigo-500 hover:text-indigo-700 px-2 py-1 rounded-lg hover:bg-indigo-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                        </button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('notifications.destroy', $notif) }}">
                        @csrf @method('DELETE')
                        <button type="submit" title="Hapus"
                                class="text-xs text-slate-400 hover:text-rose-600 px-2 py-1 rounded-lg hover:bg-rose-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        <div class="px-5 py-4 border-t border-slate-100">{{ $notifications->links() }}</div>
    @endif
</x-data-card>
@endsection
