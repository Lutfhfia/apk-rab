<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN_KEUANGAN = 'admin_keuangan';
    case MANAJER_KEUANGAN = 'manajer_keuangan';
    case LEGACY_MANAJER_KEUANGAN = 'manajer_operasional';
    case DIREKTUR = 'direktur';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN_KEUANGAN => 'Admin Keuangan',
            self::MANAJER_KEUANGAN, self::LEGACY_MANAJER_KEUANGAN => 'Manajer Keuangan',
            self::DIREKTUR => 'Direktur',
        };
    }

    public function normalizedValue(): string
    {
        return match ($this) {
            self::LEGACY_MANAJER_KEUANGAN => self::MANAJER_KEUANGAN->value,
            default => $this->value,
        };
    }
}
