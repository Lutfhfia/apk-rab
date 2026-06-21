<?php

namespace App\Models;

use App\Enums\RabReceiptStatus;
use Illuminate\Database\Eloquent\Model;

class RabReceipt extends Model
{
    protected $fillable = [
        'rab_id',
        'uploaded_by',
        'validated_by',
        'receipt_date',
        'store_name',
        'receipt_number',
        'total_amount',
        'receipt_file',
        'status',
        'notes',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'receipt_date' => 'date',
            'total_amount' => 'decimal:2',
            'status' => RabReceiptStatus::class,
            'validated_at' => 'datetime',
        ];
    }

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
