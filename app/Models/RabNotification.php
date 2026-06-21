<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RabNotification extends Model
{
    protected $fillable = [
        'rab_id',
        'user_id',
        'title',
        'message',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke model Rab (RAB terkait notifikasi ini).
     */
    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    /**
     * Relasi ke model User (Penerima notifikasi).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope query untuk memfilter notifikasi yang belum dibaca.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Menandai notifikasi telah dibaca.
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }
}
