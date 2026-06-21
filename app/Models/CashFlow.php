<?php

namespace App\Models;

use App\Enums\CashFlowType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CashFlow extends Model
{
    protected $fillable = [
        'rab_id',
        'payment_id',
        'transaction_date',
        'type',
        'description',
        'debit',
        'credit',
        'balance',
        'proof_file_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'type' => CashFlowType::class,
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    /**
     * Relasi ke model Rab (RAB terkait dengan arus kas ini).
     */
    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    /**
     * Relasi ke model RabPayment (Pembayaran terkait dengan arus kas ini).
     */
    public function payment()
    {
        return $this->belongsTo(RabPayment::class, 'payment_id');
    }

    /**
     * Relasi ke model User (Pengguna yang mencatat arus kas).
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Mendapatkan path file bukti transaksi arus kas.
     */
    public function proofFilePath(): ?string
    {
        return $this->proof_file_path ?: $this->payment?->proof_file_path;
    }

    /**
     * Memeriksa apakah file bukti transaksi tersedia di storage public.
     */
    public function proofFileExists(): bool
    {
        $proofPath = $this->proofFilePath();

        return $proofPath && Storage::disk('public')->exists($proofPath);
    }
}
