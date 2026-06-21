<?php

namespace App\Models;

use App\Enums\PaymentValidationStatus;
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
        'validation_status',
        'validation_notes',
        'validated_by',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'paid_amount' => 'decimal:2',
            'validation_status' => PaymentValidationStatus::class,
            'validated_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke model Rab (RAB yang dibayarkan).
     */
    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    /**
     * Relasi ke model User (Pengguna yang memproses pembayaran).
     */
    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Relasi ke User yang memvalidasi bukti pembayaran / LPJ.
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Relasi ke model CashFlow (Detail arus kas keluar dari pembayaran ini).
     */
    public function cashFlow()
    {
        return $this->hasOne(CashFlow::class, 'payment_id');
    }
}
