<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RabDiscussion extends Model
{
    protected $fillable = [
        'rab_id',
        'user_id',
        'message',
    ];

    /**
     * Relasi ke model Rab (RAB tempat diskusi ini dilakukan).
     */
    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    /**
     * Relasi ke model User (Pengguna yang mengirim pesan diskusi).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
