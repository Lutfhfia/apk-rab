<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyExpenseItem extends Model
{
    protected $fillable = [
        'rab_id',
        'payment_name',
        'registration_number',
        'account_name',
        'period',
        'description',
        'total_expense',
        'bill_nominal',
        'admin_fee',
        'total_payment',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'total_expense' => 'decimal:2',
            'bill_nominal' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'total_payment' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    /**
     * Relasi ke model Rab (RAB pemilik item pengeluaran bulanan ini).
     */
    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }
}
