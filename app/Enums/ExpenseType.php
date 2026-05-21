<?php

namespace App\Enums;

enum ExpenseType: string
{
    case OPERASIONAL = 'operasional';
    case PETTY_CASH = 'petty_cash';
    case GAJI = 'gaji';
    case BULANAN = 'bulanan';

    public function label(): string
    {
        return match ($this) {
            self::OPERASIONAL => 'Biaya Operasional',
            self::PETTY_CASH => 'Petty Cash',
            self::GAJI => 'Biaya Gaji',
            self::BULANAN => 'Biaya Bulanan',
        };
    }
}
