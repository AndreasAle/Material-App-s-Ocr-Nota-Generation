@echo off
echo ========================================
echo   NOTAGO - Build for ngrok sharing
echo ========================================
echo.

echo [1/3] Menghapus hot file...
if exist public\hot (
    del public\hot
    echo       OK - hot file dihapus
) else (
    echo       OK - hot file tidak ada
)

echo.
echo [2/3] Build assets...
call npm run build
if %errorlevel% neq 0 (
    echo ERROR: Build gagal!
    pause
    exit /b 1
)

echo.
echo [3/3] Clear cache Laravel...
call php artisan config:clear
call php artisan view:clear

echo.
echo ========================================
echo   SELESAI! Refresh ngrok di HP sekarang
echo ========================================
echo.
pause
