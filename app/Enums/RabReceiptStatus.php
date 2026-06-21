<?php

namespace App\Enums;

enum RabReceiptStatus: string
{
    case BELUM_UPLOAD = 'belum_upload';
    case MENUNGGU_VALIDASI = 'menunggu_validasi';
    case VALID = 'valid';
    case DITOLAK = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::BELUM_UPLOAD => 'Belum Upload',
            self::MENUNGGU_VALIDASI => 'Menunggu Validasi',
            self::VALID => 'Valid',
            self::DITOLAK => 'Ditolak',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::BELUM_UPLOAD => 'bg-gray-100 text-gray-700',
            self::MENUNGGU_VALIDASI => 'bg-amber-100 text-amber-700',
            self::VALID => 'bg-emerald-100 text-emerald-700',
            self::DITOLAK => 'bg-red-100 text-red-700',
        };
    }
}
