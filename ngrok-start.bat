@echo off
title NOTAGO — Ngrok Launcher
color 0A

echo.
echo  =========================================
echo    NOTAGO ^| Ngrok Tunnel Launcher
echo  =========================================
echo.

REM ── 0. Hapus Vite hot file supaya assets load dari build/ bukan dev server ──
if exist "%~dp0public\hot" (
    del "%~dp0public\hot"
    echo  [0/3] Vite hot file dihapus.
)

REM ── 1. Jalankan php artisan serve di jendela baru ──
echo  [1/3] Menjalankan PHP server (port 8000)...
start "NOTAGO - PHP Server" cmd /k "cd /d %~dp0 && php artisan serve --host=127.0.0.1 --port=8000"
timeout /t 2 /nobreak >nul

REM ── 1b. Queue worker: wajib untuk proses Scan Nota (OCR) & notifikasi ──
echo  [1b/3] Menjalankan queue worker (Scan Nota / OCR)...
start "NOTAGO - Queue Worker" cmd /k "cd /d %~dp0 && php artisan queue:work --tries=1 --timeout=0"

REM ── 2. Jalankan ngrok di background (static domain) ──
echo  [2/3] Menjalankan ngrok tunnel (static domain)...
start "NOTAGO - Ngrok" cmd /k "ngrok http 8000 --domain=uncapacious-micrometrically-deja.ngrok-free.dev"
timeout /t 3 /nobreak >nul

REM ── 3. Ambil URL ngrok dari API lokal & update .env ──
echo  [3/3] Mengambil URL publik dari ngrok...
timeout /t 2 /nobreak >nul

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$url = ''; $tries = 0; do { try { $r = Invoke-RestMethod -Uri 'http://localhost:4040/api/tunnels' -ErrorAction Stop; $url = ($r.tunnels | Where-Object { $_.proto -eq 'https' } | Select-Object -First 1).public_url } catch {} ; if (-not $url) { Start-Sleep -Seconds 2; $tries++ } } while (-not $url -and $tries -lt 10); if ($url) { Write-Host ''; Write-Host '  URL Publik: ' -NoNewline -ForegroundColor Cyan; Write-Host $url -ForegroundColor Yellow; $env_file = '%~dp0.env'; $content = Get-Content $env_file -Raw; $content = $content -replace 'APP_URL=.*', \"APP_URL=$url\"; Set-Content $env_file $content -NoNewline; Write-Host '  APP_URL di .env telah diperbarui!' -ForegroundColor Green; Write-Host '' } else { Write-Host '  Gagal mendapatkan URL ngrok. Cek jendela ngrok secara manual.' -ForegroundColor Red }"

REM ── 4. Clear config cache ──
echo.
echo  Membersihkan config cache...
cd /d %~dp0
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1

echo.
echo  =========================================
echo    Selesai! Buka URL di atas di browser.
echo    Tekan tombol apa saja untuk keluar...
echo  =========================================
echo.
pause >nul
