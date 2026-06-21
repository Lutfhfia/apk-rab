<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PnbpExpenseItem extends Model
{
    protected $fillable = [
        'rab_id',
        'item_name',
        'agenda_number',
        'level',
        'tarif_pnbp',
        'company_name',
    ];

    protected function casts(): array
    {
        return [
            'tarif_pnbp' => 'decimal:2',
        ];
    }

    /**
     * Relasi ke model Rab (RAB pemilik item pengeluaran PNBP ini).
     */
    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }
}
