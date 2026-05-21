<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PettyCashItem extends Model
{
    protected $fillable = [
        'rab_id',
        'expense_name',
        'description',
        'volume',
        'unit',
        'unit_price',
        'transaction_date',
        'nominal',
        'admin_fee',
        'total',
        'receipt_path',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'volume' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'nominal' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }
}
