<?php

namespace App\Enums;

enum ExpenseType: string
{
    case OPERASIONAL = 'operasional';
    case PETTY_CASH = 'petty_cash';
    case GAJI = 'gaji';
    case BULANAN = 'bulanan';
    case LISTRIK = 'listrik';
    case AIR_PAM = 'air_pam';
    case PNBP = 'pnbp';

    public function label(): string
    {
        return match ($this) {
            self::OPERASIONAL => 'Biaya Operasional',
            self::PETTY_CASH => 'Petty Cash',
            self::GAJI => 'Biaya Gaji',
            self::BULANAN => 'Biaya Bulanan',
            self::LISTRIK => 'Biaya Listrik',
            self::AIR_PAM => 'Biaya Air PAM',
            self::PNBP => 'Pembayaran PNBP',
        };
    }
}
