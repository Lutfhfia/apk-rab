<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RabPayment extends Model
{
    protected $fillable = [
        'rab_id',
        'paid_by',
        'payment_date',
        'paid_amount',
        'payment_method',
        'recipient_account',
        'recipient_name',
        'proof_file_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function cashFlow()
    {
        return $this->hasOne(CashFlow::class, 'payment_id');
    }
}
