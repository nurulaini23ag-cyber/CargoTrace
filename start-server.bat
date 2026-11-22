@echo off
title CargoTrace - Local Server Launcher
cd /d "%~dp0"
echo ===================================================
echo   CargoTrace - Global Shipment Visibility Platform
echo ===================================================
echo [1/2] Membuka browser ke http://localhost:8000 ...
start http://localhost:8000
echo [2/2] Menjalankan PHP Artisan Serve...
php artisan serve --host=127.0.0.1 --port=8000
pause
