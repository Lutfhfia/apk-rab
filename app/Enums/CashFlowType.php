<?php

namespace App\Enums;

enum CashFlowType: string
{
    case SALDO_AWAL = 'saldo_awal';
    case DANA_MASUK = 'dana_masuk';
    case DANA_KELUAR = 'dana_keluar';

    public function label(): string
    {
        return match ($this) {
            self::SALDO_AWAL => 'Saldo Awal',
            self::DANA_MASUK => 'Dana Masuk',
            self::DANA_KELUAR => 'Dana Keluar',
        };
    }
}
