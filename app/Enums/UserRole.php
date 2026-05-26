<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN_KEUANGAN = 'admin_keuangan';
    case MANAJER_OPERASIONAL = 'manajer_operasional';
    case DIREKTUR = 'direktur';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN_KEUANGAN => 'Admin Keuangan',
            self::MANAJER_OPERASIONAL => 'Manajer Operasional',
            self::DIREKTUR => 'Direktur',
        };
    }
}
