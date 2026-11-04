<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading - {{ config('app.name', 'NOTAGO') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes pulseSoft {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: .92;
            }
        }

        @keyframes floatY {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes spinGlow {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes slideBar {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }

        .logo-float {
            animation: floatY 2.8s ease-in-out infinite;
        }

        .logo-pulse {
            animation: pulseSoft 2.2s ease-in-out infinite;
        }

        .ring-spin {
            animation: spinGlow 8s linear infinite;
        }

        .bar-animated {
            background-size: 200% 100%;
            animation: slideBar 2s linear infinite;
        }
    </style>
</head>
<body class="min-h-screen overflow-hidden bg-amber-500">
    <div class="relative flex min-h-screen items-center justify-center px-6">
        {{-- Background glow --}}
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute top-1/3 -right-24 h-80 w-80 rounded-full bg-amber-200/20 blur-3xl"></div>
            <div class="absolute -bottom-20 left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-rose-400/10 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-xl text-center">
            {{-- Logo wrapper --}}
            <div class="mx-auto mb-8 flex justify-center">
                <div class="relative logo-float">
                    <div class="ring-spin absolute inset-[-14px] rounded-[36px] border border-white/20"></div>
                    <div class="ring-spin absolute inset-[-28px] rounded-[44px] border border-white/10"></div>

                    <div class="logo-pulse flex h-32 w-32 items-center justify-center rounded-[32px] bg-white shadow-[0_25px_80px_rgba(0,0,0,0.20)] sm:h-36 sm:w-36">
                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="Logo"
                            class="h-20 w-20 object-contain sm:h-24 sm:w-24"
                        >
                    </div>
                </div>
            </div>

            {{-- Brand --}}
            <div class="mb-8">
                <h1 class="text-4xl font-black tracking-[0.12em] text-white sm:text-5xl">
                    NOTAGO
                </h1>
                <p class="mt-3 text-sm font-medium tracking-[0.22em] text-amber-100 uppercase">
                    Smart Material & Asset Operations
                </p>
            </div>

            {{-- Loading text --}}
            <div class="mb-5">
                <p id="loadingText" class="text-base font-semibold text-white sm:text-lg">
                    Menyiapkan sistem...
                </p>
                <p class="mt-2 text-sm text-amber-100/90">
                    Mohon tunggu, aplikasi sedang dimuat
                </p>
            </div>

            {{-- Progress card --}}
            <div class="mx-auto w-full max-w-md rounded-[28px] border border-white/20 bg-white/10 p-5 shadow-2xl backdrop-blur-md">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-sm font-semibold text-white/90">Loading</span>
                    <span id="progressText" class="text-sm font-bold text-white">0%</span>
                </div>

                <div class="h-4 w-full overflow-hidden rounded-full bg-white/20">
                    <div
                        id="progressBar"
                        class="bar-animated h-full rounded-full bg-[linear-gradient(90deg,#ffffff_0%,#ffedd5_25%,#ffffff_50%,#fdba74_75%,#ffffff_100%)] transition-all duration-200 ease-out"
                        style="width: 0%;"
                    ></div>
                </div>

                <div class="mt-4 flex items-center justify-center gap-2 text-xs text-amber-50/90">
                    <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-white"></span>
                    <span id="statusText">Memuat halaman awal...</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const statusText = document.getElementById('statusText');
            const loadingText = document.getElementById('loadingText');

            const isAuthenticated = @json(auth()->check());
            const targetUrl = isAuthenticated
                ? "{{ route('dashboard') }}"
                : "{{ route('login') }}";

            const steps = [
                { limit: 20, text: 'Memuat identitas aplikasi...' },
                { limit: 45, text: 'Menyiapkan antarmuka...' },
                { limit: 70, text: 'Menyusun komponen sistem...' },
                { limit: 90, text: 'Hampir selesai...' },
                { limit: 100, text: 'Selesai, mengalihkan...' },
            ];

            let progress = 0;

            const interval = setInterval(() => {
                let increment = Math.floor(Math.random() * 8) + 3;
                progress += increment;

                if (progress > 100) progress = 100;

                progressBar.style.width = progress + '%';
                progressText.textContent = progress + '%';

                const currentStep = steps.find(step => progress <= step.limit) || steps[steps.length - 1];
                statusText.textContent = currentStep.text;

                if (progress < 100) {
                    loadingText.textContent = 'Menyiapkan sistem...';
                } else {
                    loadingText.textContent = 'Selamat datang';
                    clearInterval(interval);

                    setTimeout(() => {
                        window.location.href = targetUrl;
                    }, 500);
                }
            }, 120);
        })();
    </script>
</body>
</html>