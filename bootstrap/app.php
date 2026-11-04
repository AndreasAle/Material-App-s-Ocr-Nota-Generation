<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies — diperlukan agar ngrok HTTPS / header forwarded bekerja benar
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Total upload melebihi post_max_size PHP: kembalikan ke form dengan pesan jelas, bukan halaman 413 kosong
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, \Illuminate\Http\Request $request) {
            $limit = ini_get('post_max_size');
            $message = "Total ukuran file yang diupload terlalu besar (batas server {$limit}). Kurangi jumlah atau ukuran file lalu coba lagi.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return back()->with('error', $message);
        });
    })->create();
