<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationalExpenseItem extends Model
{
    protected $fillable = [
        'rab_id',
        'group_name',
        'item_name',
        'description',
        'volume',
        'unit',
        'unit_price',
        'total',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'volume' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * Relasi ke model Rab (RAB pemilik item pengeluaran operasional ini).
     */
    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }
}
