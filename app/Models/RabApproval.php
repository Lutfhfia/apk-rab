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

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
