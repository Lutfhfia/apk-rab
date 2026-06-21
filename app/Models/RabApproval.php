<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Model;

class RabApproval extends Model
{
    protected $fillable = [
        'rab_id',
        'user_id',
        'role',
        'approval_level',
        'status',
        'notes',
        'approved_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApprovalStatus::class,
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke model Rab (RAB yang disetujui / ditolak).
     */
    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    /**
     * Relasi ke model User (Pihak manajer/direktur yang memberikan persetujuan).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
