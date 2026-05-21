@echo off
set PHP_BIN="%TEMP%\php-8.2.31\php.exe"
if exist %PHP_BIN% (
    echo Menggunakan PHP 8.2 Portable untuk menjalankan server...
    %PHP_BIN% artisan serve %*
) else (
    echo [ERROR] PHP 8.2 Portable tidak ditemukan di %TEMP%\php-8.2.31
    echo Silakan jalankan update PHP di Laragon Anda secara manual.
)
