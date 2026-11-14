@echo off
rem ============================================================
rem  Vesper - Serveur de developpement local
rem  - Definit PHPRC (extensions PHP : mbstring, openssl, sodium)
rem  - --no-reload requis (bug php artisan serve sur Windows/PHP 8.4)
rem ============================================================
set "PHPRC=C:\composer"
cd /d "%~dp0"
php artisan serve --host=127.0.0.1 --port=8000 --no-reload
