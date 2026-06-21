<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'avatar',
        'phone_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ──

    public function rabs()
    {
        return $this->hasMany(Rab::class);
    }

    public function approvals()
    {
        return $this->hasMany(RabApproval::class);
    }

    public function payments()
    {
        return $this->hasMany(RabPayment::class, 'paid_by');
    }

    public function cashFlows()
    {
        return $this->hasMany(CashFlow::class, 'created_by');
    }

    public function reportExports()
    {
        return $this->hasMany(ReportExport::class, 'exported_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function rabDiscussions()
    {
        return $this->hasMany(RabDiscussion::class);
    }

    public function rabNotifications()
    {
        return $this->hasMany(RabNotification::class);
    }

    // ── Helpers ──

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN_KEUANGAN;
    }

    public function isManajer(): bool
    {
        return $this->role === UserRole::MANAJER_OPERASIONAL;
    }

    public function isDirektur(): bool
    {
        return $this->role === UserRole::DIREKTUR;
    }
}
