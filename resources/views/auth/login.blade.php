<x-guest-layout>
    <div class="min-h-[720px] overflow-hidden rounded-[34px] border border-slate-200 bg-white shadow-[0_25px_70px_rgba(15,23,42,0.10)]">
        <div class="grid min-h-[720px] lg:grid-cols-[1.02fr_1.18fr]">
            {{-- LEFT: FORM --}}
            <div class="relative flex items-center bg-white px-6 py-10 sm:px-10 lg:px-14 xl:px-16">
                <div class="absolute inset-y-0 right-0 hidden w-[120px] bg-[linear-gradient(135deg,transparent_0%,transparent_18%,#f8fafc_18%,#f8fafc_100%)] lg:block"></div>

                <div class="relative z-10 w-full max-w-[430px]">
                    <div class="mb-10">
                        <div class="inline-flex items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <img
                                    src="{{ asset('images/logo.png') }}"
                                    alt="Logo Notago"
                                    class="h-full w-full object-contain p-1.5"
                                >
                            </div>

                            <div>
                                <h1 class="text-[1.95rem] font-extrabold tracking-tight text-slate-900">
                                    NOTAGO
                                </h1>
                                <p class="mt-1 text-sm text-slate-500">
                                    Panel administrasi
                                </p>
                            </div>
                        </div>

                        <div class="mt-10">
                            <h2 class="text-[2.15rem] font-extrabold leading-tight tracking-tight text-slate-900">
                                Asset Spec Management
                            </h2>
                            <p class="mt-3 max-w-md text-sm leading-7 text-slate-500">
                                Welcome back. Please login to your account and continue managing
                                procurement, material purchases, suppliers, and project operations
                                in one integrated dashboard.
                            </p>
                        </div>
                    </div>

                    <x-auth-session-status
                        class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                        :status="session('status')"
                    />

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <x-input-label
                                for="email"
                                :value="__('Email')"
                                class="mb-2.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500"
                            />

                            <x-text-input
                                id="email"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Masukkan email anda"
                                class="block h-[58px] w-full rounded-[20px] border-slate-200 bg-white px-5 text-[15px] text-slate-800 shadow-sm transition focus:border-orange-300 focus:ring-orange-200"
                            />

                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
                        </div>

                        <div>
                            <div class="mb-2.5 flex items-center justify-between gap-3">
                                <x-input-label
                                    for="password"
                                    :value="__('Password')"
                                    class="block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500"
                                />

                                @if (Route::has('password.request'))
                                    <a
                                        class="text-xs font-semibold text-slate-500 transition hover:text-slate-900"
                                        href="{{ route('password.request') }}"
                                    >
                                        Forgot password?
                                    </a>
                                @endif
                            </div>

                            <div class="relative">
                                <x-text-input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Masukkan password"
                                    class="block h-[58px] w-full rounded-[20px] border-slate-200 bg-white px-5 pr-12 text-[15px] text-slate-800 shadow-sm transition focus:border-orange-300 focus:ring-orange-200"
                                />

                                <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c4.757 0 8.775-3.162 10.066-7.5a10.474 10.474 0 0 0-1.932-3.777M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6.228-7.228L2.772 19.228"/>
                                    </svg>
                                </div>
                            </div>

                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600" />
                        </div>

                        <div class="flex flex-col gap-4 pt-1 sm:flex-row sm:items-center sm:justify-between">
                            <label for="remember_me" class="inline-flex items-center gap-3 text-sm font-medium text-slate-600">
                                <input
                                    id="remember_me"
                                    type="checkbox"
                                    name="remember"
                                    class="rounded border-slate-300 text-orange-500 shadow-sm focus:ring-orange-400"
                                >
                                <span>Remember me</span>
                            </label>
                        </div>

                        <div class="pt-2">
                            <button
                                type="submit"
                                class="inline-flex h-[58px] w-full items-center justify-center rounded-[18px] bg-gradient-to-r from-amber-400 via-orange-400 to-orange-500 px-6 text-sm font-bold uppercase tracking-[0.16em] text-white shadow-[0_18px_30px_rgba(249,115,22,0.24)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_34px_rgba(249,115,22,0.30)]"
                            >
                                Login
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 border-t border-slate-200 pt-5">
                        <p class="text-xs leading-6 text-slate-400">
                            Sistem internal perusahaan. Gunakan akun resmi yang telah terdaftar
                            untuk mengakses dashboard operasional.
                        </p>
                    </div>
                </div>
            </div>

            {{-- RIGHT: VISUAL --}}
            <div class="relative hidden overflow-hidden bg-slate-900 lg:block">
                <img
                    src="{{ asset('images/login-cover.png') }}"
                    alt="Login Cover"
                    class="absolute inset-0 h-full w-full object-cover"
                >

                <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.08)_0%,rgba(15,23,42,0.30)_100%)]"></div>

                <div class="absolute inset-y-0 left-[-1px] w-[180px] bg-white [clip-path:polygon(0_0,100%_0,30%_100%,0_100%)]"></div>

                <div class="absolute inset-x-0 bottom-0 p-10 xl:p-12">
                    <div class="max-w-md rounded-[28px] border border-white/15 bg-white/10 p-7 text-white shadow-2xl backdrop-blur-md">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/80">
                            <span class="h-2 w-2 rounded-full bg-orange-400"></span>
                            Smart Operations
                        </div>

                        <h3 class="mt-5 text-3xl font-extrabold tracking-tight">
                            Clean, modern, and focused workflow.
                        </h3>

                        <p class="mt-3 text-sm leading-7 text-white/80">
                            Satu sistem untuk membantu tim mengelola pembelian material, supplier,
                            dokumen nota, dan kebutuhan proyek dengan tampilan yang lebih profesional.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>