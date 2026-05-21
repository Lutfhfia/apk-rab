@echo off
set PHP_BIN="%TEMP%\php-8.2.31\php.exe"
if exist %PHP_BIN% (
    %PHP_BIN% artisan %*
) else (
    echo [ERROR] PHP 8.2 Portable tidak ditemukan. Silakan gunakan PHP Laragon.
    php artisan %*
)
