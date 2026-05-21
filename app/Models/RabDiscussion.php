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

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
