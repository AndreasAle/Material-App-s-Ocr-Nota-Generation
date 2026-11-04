@extends('layouts.app')

@section('content')
    @php
        $tabItems = [
            'all' => 'All',
            'tips' => 'Tips',
            'guide' => 'Guide',
            'faq' => 'FAQ',
            'announcement' => 'Announcement',
            'about' => 'About',
        ];

        if (! function_exists('contentIcon')) {
        function contentIcon($icon) {
            return match ($icon) {
                'scan' => '
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-white/95" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5V6A2.25 2.25 0 0 1 6 3.75h1.5m9 0H18A2.25 2.25 0 0 1 20.25 6v1.5m0 9V18A2.25 2.25 0 0 1 18 20.25h-1.5m-9 0H6A2.25 2.25 0 0 1 3.75 18v-1.5M8.25 12h7.5"/>
                    </svg>
                ',
                'export' => '
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-white/95" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 20.25h15"/>
                    </svg>
                ',
                'faq' => '
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-white/95" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9a3.75 3.75 0 1 1 7.5 0c0 1.51-.878 2.357-1.786 2.965-.848.568-1.714 1.137-1.714 2.285m.038 3h.008v.008h-.008v-.008Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                ',
                'announcement' => '
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-white/95" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 3.94A1.5 1.5 0 0 1 11.82 3h.36a1.5 1.5 0 0 1 1.48.94l.29.72a1.5 1.5 0 0 0 1.74.9l.76-.16a1.5 1.5 0 0 1 1.58.67l.18.31a1.5 1.5 0 0 1-.06 1.71l-.43.65a1.5 1.5 0 0 0 0 1.66l.43.65a1.5 1.5 0 0 1 .06 1.71l-.18.31a1.5 1.5 0 0 1-1.58.67l-.76-.16a1.5 1.5 0 0 0-1.74.9l-.29.72a1.5 1.5 0 0 1-1.48.94h-.36a1.5 1.5 0 0 1-1.48-.94l-.29-.72a1.5 1.5 0 0 0-1.74-.9l-.76.16a1.5 1.5 0 0 1-1.58-.67l-.18-.31a1.5 1.5 0 0 1 .06-1.71l.43-.65a1.5 1.5 0 0 0 0-1.66l-.43-.65a1.5 1.5 0 0 1-.06-1.71l.18-.31a1.5 1.5 0 0 1 1.58-.67l.76.16a1.5 1.5 0 0 0 1.74-.9l.29-.72Z"/>
                    </svg>
                ',
                'about' => '
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-white/95" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 10.5h1.5v6h-1.5zM12 7.5h.008v.008H12V7.5Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                ',
                'checklist' => '
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-white/95" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 11.25 11.25 13.5 15 9.75M6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75A2.25 2.25 0 0 1 6.75 4.5Z"/>
                    </svg>
                ',
                'master' => '
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-white/95" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5 12 3l8.25 4.5M3.75 7.5V16.5L12 21m-8.25-13.5L12 12m8.25-4.5V16.5L12 21m0-9v9"/>
                    </svg>
                ',
                default => '
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-white/95" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6l4.5 4.5v12A1.5 1.5 0 0 1 16.5 21h-9A1.5 1.5 0 0 1 6 19.5v-14A1.5 1.5 0 0 1 7.5 3.75Z"/>
                    </svg>
                ',
            };
        }
        }
    @endphp

    <div class="space-y-6">
        <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                        Content Management
                    </div>

                    <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900">
                        Pusat Konten Aplikasi
                    </h1>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Kelola tips, panduan, FAQ, pengumuman, dan informasi aplikasi agar panel admin terasa lebih lengkap dan informatif.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:min-w-[420px]">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Semua</div>
                        <div class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">{{ $stats['all'] }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Published</div>
                        <div class="mt-2 text-2xl font-extrabold tracking-tight text-emerald-700">{{ $stats['published'] }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Draft</div>
                        <div class="mt-2 text-2xl font-extrabold tracking-tight text-amber-600">{{ $stats['draft'] }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Tips</div>
                        <div class="mt-2 text-2xl font-extrabold tracking-tight text-indigo-700">{{ $stats['tips'] }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex flex-wrap gap-2">
                    @foreach ($tabItems as $key => $label)
                        <a
                            href="{{ route('contents.index', array_merge(request()->query(), ['tab' => $key])) }}"
                            class="{{ $filters['tab'] === $key ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-white' }} inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('contents.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-[180px_180px_auto]">
                    <input type="hidden" name="tab" value="{{ $filters['tab'] }}">

                    <select name="status" class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100">
                        <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="published" {{ $filters['status'] === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ $filters['status'] === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>

                    <select name="sort" class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100">
                        <option value="latest" {{ $filters['sort'] === 'latest' ? 'selected' : '' }}>Sort: Latest</option>
                        <option value="title" {{ $filters['sort'] === 'title' ? 'selected' : '' }}>Sort: Title</option>
                        <option value="oldest" {{ $filters['sort'] === 'oldest' ? 'selected' : '' }}>Sort: Oldest</option>
                    </select>

                    <button type="submit" class="inline-flex h-12 items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:bg-slate-800">
                        Apply
                    </button>
                </form>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @forelse ($contents as $content)
                @php
                    $statusClass = $content['status'] === 'published'
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-amber-100 text-amber-700';
                @endphp

                <article class="group overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                        <div class="relative h-44 overflow-hidden {{ empty($content['image']) ? 'bg-gradient-to-br ' . $content['accent'] : 'bg-slate-200' }}">
                            @if(!empty($content['image']))
                                <img
                                    src="{{ $content['image'] }}"
                                    alt="{{ $content['title'] }}"
                                    class="absolute inset-0 h-full w-full object-cover"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/45 via-slate-900/10 to-transparent"></div>
                            @else
                                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,.28),transparent_28%),radial-gradient(circle_at_bottom_left,rgba(255,255,255,.14),transparent_24%)]"></div>
                            @endif

                        <div class="absolute left-5 top-5 inline-flex rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-white backdrop-blur-sm">
                            {{ strtoupper($content['category']) }}
                        </div>

                        <div class="absolute right-5 top-5 inline-flex rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] {{ $statusClass }}">
                            {{ $content['status'] }}
                        </div>

                        <div class="absolute inset-x-0 bottom-0 flex items-end justify-between px-5 pb-5">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/20 bg-white/10 backdrop-blur-sm">
                                {!! contentIcon($content['icon']) !!}
                            </div>

                            <div class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                {{ $content['location'] }}
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <h3 class="line-clamp-2 text-lg font-bold tracking-tight text-slate-900 transition group-hover:text-indigo-700">
                            {{ $content['title'] }}
                        </h3>

                        <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-500">
                            {{ $content['summary'] }}
                        </p>

                        <div class="mt-4 flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700">
                                {{ strtoupper(substr($content['author'], 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-slate-800">{{ $content['author'] }}</div>
                                <div class="truncate text-xs text-slate-500">{{ $content['reading_time'] }}</div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-xs font-medium text-slate-500">
                            <div class="inline-flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75V4.5m7.5 2.25V4.5M3.75 9.75h16.5M6 21h12a2.25 2.25 0 0 0 2.25-2.25V8.25A2.25 2.25 0 0 0 18 6H6a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 6 21Z"/>
                                </svg>
                                <span>{{ $content['date'] }}</span>
                            </div>

                            <button
                                type="button"
                                class="content-detail-btn inline-flex items-center rounded-full border border-slate-200 bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-800"
                                data-title="{{ $content['title'] }}"
                                data-category="{{ $content['category'] }}"
                                data-status="{{ $content['status'] }}"
                                data-author="{{ $content['author'] }}"
                                data-date="{{ $content['date'] }}"
                                data-summary="{{ $content['summary'] }}"
                                data-content="{{ $content['content'] }}"
                                data-points='@json($content["points"])'
                            >
                                Detail
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-[26px] border border-dashed border-slate-300 bg-slate-50 p-12 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6l4.5 4.5v12A1.5 1.5 0 0 1 16.5 21h-9A1.5 1.5 0 0 1 6 19.5v-14A1.5 1.5 0 0 1 7.5 3.75Z"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-900">Belum ada konten</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Konten dummy akan muncul di sini sesuai kategori dan filter yang dipilih.
                    </p>
                </div>
            @endforelse
        </section>
    </div>

    <div id="content-detail-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/55 px-4 py-6">
        <div class="w-full max-w-4xl overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                <div class="pr-4">
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                        Detail Content
                    </div>
                    <h2 id="modal-title" class="mt-3 text-xl font-extrabold tracking-tight text-slate-900"></h2>
                </div>

                <button
                    type="button"
                    id="close-content-modal"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="max-h-[75vh] overflow-y-auto px-5 py-5 sm:px-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Kategori</div>
                        <div id="modal-category" class="mt-2 text-sm font-bold text-slate-900"></div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Status</div>
                        <div id="modal-status" class="mt-2 text-sm font-bold text-slate-900"></div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Author</div>
                        <div id="modal-author" class="mt-2 text-sm font-bold text-slate-900"></div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Tanggal</div>
                        <div id="modal-date" class="mt-2 text-sm font-bold text-slate-900"></div>
                    </div>
                </div>

                <div class="mt-5 rounded-[24px] border border-slate-200 bg-slate-50 p-5">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Ringkasan</div>
                    <p id="modal-summary" class="mt-3 text-sm leading-7 text-slate-600"></p>
                </div>

                <div class="mt-5 rounded-[24px] border border-slate-200 bg-white p-5">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Isi Konten</div>
                    <p id="modal-content" class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600"></p>
                </div>

                <div class="mt-5 rounded-[24px] border border-slate-200 bg-white p-5">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Poin Penting</div>
                    <div id="modal-points" class="mt-4 space-y-3"></div>
                </div>
            </div>

            <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                <div class="flex justify-end">
                    <button
                        type="button"
                        id="close-content-modal-footer"
                        class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('content-detail-modal');
            const closeBtn = document.getElementById('close-content-modal');
            const closeFooterBtn = document.getElementById('close-content-modal-footer');
            const detailButtons = document.querySelectorAll('.content-detail-btn');

            const modalTitle = document.getElementById('modal-title');
            const modalCategory = document.getElementById('modal-category');
            const modalStatus = document.getElementById('modal-status');
            const modalAuthor = document.getElementById('modal-author');
            const modalDate = document.getElementById('modal-date');
            const modalSummary = document.getElementById('modal-summary');
            const modalContent = document.getElementById('modal-content');
            const modalPoints = document.getElementById('modal-points');

            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }

            detailButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    modalTitle.textContent = this.dataset.title || '-';
                    modalCategory.textContent = this.dataset.category || '-';
                    modalStatus.textContent = this.dataset.status || '-';
                    modalAuthor.textContent = this.dataset.author || '-';
                    modalDate.textContent = this.dataset.date || '-';
                    modalSummary.textContent = this.dataset.summary || '-';
                    modalContent.textContent = this.dataset.content || '-';

                    modalPoints.innerHTML = '';

                    let points = [];
                    try {
                        points = JSON.parse(this.dataset.points || '[]');
                    } catch (e) {
                        points = [];
                    }

                    if (points.length) {
                        points.forEach(function (point, index) {
                            const item = document.createElement('div');
                            item.className = 'flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3';
                            item.innerHTML = `
                                <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">${index + 1}</div>
                                <p class="text-sm leading-6 text-slate-700">${point}</p>
                            `;
                            modalPoints.appendChild(item);
                        });
                    } else {
                        modalPoints.innerHTML = `
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                Belum ada poin tambahan.
                            </div>
                        `;
                    }

                    openModal();
                });
            });

            closeBtn.addEventListener('click', closeModal);
            closeFooterBtn.addEventListener('click', closeModal);

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        });
    </script>
@endsection