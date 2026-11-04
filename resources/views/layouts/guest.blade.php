<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f7fb] font-sans text-slate-900 antialiased">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top_right,rgba(249,115,22,0.08),transparent_25%),radial-gradient(circle_at_bottom_left,rgba(15,23,42,0.06),transparent_25%)]">
        <div class="mx-auto flex min-h-screen max-w-7xl items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </div>
</body>
</html>