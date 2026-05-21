<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'rab_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    /**
     * Create an audit log entry.
     */
    public static function log(
        string $action,
        string $description,
        ?int $userId = null,
        ?int $rabId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): static {
        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'rab_id' => $rabId,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
