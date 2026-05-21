<?php

namespace App\Enums;

enum RabStatus: string
{
    case DRAFT = 'draft';
    case DIAJUKAN = 'diajukan';
    case DISETUJUI_MANAJER = 'disetujui_manajer';
    case DISETUJUI_DIREKTUR = 'disetujui_direktur';
    case DISETUJUI = 'disetujui';
    case DITOLAK = 'ditolak';
    case SELESAI = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::DIAJUKAN => 'Diajukan',
            self::DISETUJUI_MANAJER => 'Disetujui Manajer',
            self::DISETUJUI_DIREKTUR => 'Disetujui Direktur',
            self::DISETUJUI => 'Disetujui Direktur',
            self::DITOLAK => 'Ditolak',
            self::SELESAI => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::DIAJUKAN => 'blue',
            self::DISETUJUI_MANAJER => 'indigo',
            self::DISETUJUI_DIREKTUR => 'purple',
            self::DISETUJUI => 'emerald',
            self::DITOLAK => 'red',
            self::SELESAI => 'green',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-700',
            self::DIAJUKAN => 'bg-blue-100 text-blue-700',
            self::DISETUJUI_MANAJER => 'bg-indigo-100 text-indigo-700',
            self::DISETUJUI_DIREKTUR => 'bg-purple-100 text-purple-700',
            self::DISETUJUI => 'bg-emerald-100 text-emerald-700',
            self::DITOLAK => 'bg-red-100 text-red-700',
            self::SELESAI => 'bg-green-100 text-green-700',
        };
    }
}
