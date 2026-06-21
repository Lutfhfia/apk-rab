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
     * Atribut yang dapat diisi secara massal (mass assignable).
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
     * Atribut yang harus disembunyikan untuk serialisasi JSON.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mendapatkan atribut yang harus dikoversi (cast) tipe datanya.
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

    // ── Hubungan / Relasi Tabel ──

    /**
     * Relasi ke model Rab (Satu User memiliki banyak RAB).
     */
    public function rabs()
    {
        return $this->hasMany(Rab::class);
    }

    /**
     * Relasi ke model RabApproval (Persetujuan RAB yang dibuat oleh User).
     */
    public function approvals()
    {
        return $this->hasMany(RabApproval::class);
    }

    /**
     * Relasi ke model RabPayment (Pembayaran yang diproses oleh User).
     */
    public function payments()
    {
        return $this->hasMany(RabPayment::class, 'paid_by');
    }

    /**
     * Relasi ke bukti pembayaran / LPJ yang divalidasi oleh user.
     */
    public function validatedPayments()
    {
        return $this->hasMany(RabPayment::class, 'validated_by');
    }

    /**
     * Relasi ke nota LPJ yang diunggah oleh user.
     */
    public function uploadedReceipts()
    {
        return $this->hasMany(RabReceipt::class, 'uploaded_by');
    }

    /**
     * Relasi ke nota LPJ yang divalidasi oleh user.
     */
    public function validatedReceipts()
    {
        return $this->hasMany(RabReceipt::class, 'validated_by');
    }

    /**
     * Relasi ke model CashFlow (Arus Kas yang dibuat oleh User).
     */
    public function cashFlows()
    {
        return $this->hasMany(CashFlow::class, 'created_by');
    }

    /**
     * Relasi ke model ReportExport (Ekspor Laporan yang dilakukan oleh User).
     */
    public function reportExports()
    {
        return $this->hasMany(ReportExport::class, 'exported_by');
    }

    /**
     * Relasi ke model AuditLog (Log audit/aktivitas yang dicatat untuk User).
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Relasi ke model RabDiscussion (Diskusi/komentar RAB yang dibuat oleh User).
     */
    public function rabDiscussions()
    {
        return $this->hasMany(RabDiscussion::class);
    }

    /**
     * Relasi ke model RabNotification (Notifikasi yang dikirimkan ke User).
     */
    public function rabNotifications()
    {
        return $this->hasMany(RabNotification::class);
    }

    // ── Fungsi Pembantu / Helper ──

    /**
     * Mengecek apakah user adalah Admin Keuangan.
     */
    public function isAdmin(): bool
    {
        return $this->normalizedRoleValue() === UserRole::ADMIN_KEUANGAN->value;
    }

    /**
     * Mengecek apakah user adalah Manajer Keuangan.
     */
    public function isManajer(): bool
    {
        return $this->normalizedRoleValue() === UserRole::MANAJER_KEUANGAN->value;
    }

    /**
     * Mengecek apakah user adalah Direktur.
     */
    public function isDirektur(): bool
    {
        return $this->normalizedRoleValue() === UserRole::DIREKTUR->value;
    }

    /**
     * Menormalisasi nilai role dari database agar kompatibel dengan Enum baru.
     */
    public function normalizedRoleValue(): string
    {
        if ($this->role instanceof UserRole) {
            return $this->role->normalizedValue();
        }

        return $this->getRawOriginal('role') === 'manajer_operasional'
            ? UserRole::MANAJER_KEUANGAN->value
            : (string) $this->getRawOriginal('role');
    }
}
