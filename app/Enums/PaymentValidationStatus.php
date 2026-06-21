<?php

namespace App\Enums;

enum PaymentValidationStatus: string
{
    case MENUNGGU_VALIDASI = 'menunggu_validasi';
    case VALID = 'valid';
    case DITOLAK = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU_VALIDASI => 'Menunggu Validasi',
            self::VALID => 'Valid',
            self::DITOLAK => 'Ditolak',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::MENUNGGU_VALIDASI => 'bg-amber-100 text-amber-700',
            self::VALID => 'bg-emerald-100 text-emerald-700',
            self::DITOLAK => 'bg-red-100 text-red-700',
        };
    }
}
