<?php

namespace App\Models;

use App\Enums\RabStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rab extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rab_number',
        'request_date',
        'period_month',
        'period_year',
        'user_id',
        'expense_type_id',
        'description',
        'total_amount',
        'status',
        'submitted_at',
        'approved_by_manager_at',
        'approved_by_director_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'total_amount' => 'decimal:2',
            'status' => RabStatus::class,
            'submitted_at' => 'datetime',
            'approved_by_manager_at' => 'datetime',
            'approved_by_director_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // ── Relationships ──

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function operationalExpenseItems()
    {
        return $this->hasMany(OperationalExpenseItem::class);
    }

    public function pettyCashItems()
    {
        return $this->hasMany(PettyCashItem::class);
    }

    public function salaryExpenseItems()
    {
        return $this->hasMany(SalaryExpenseItem::class);
    }

    public function monthlyExpenseItems()
    {
        return $this->hasMany(MonthlyExpenseItem::class);
    }

    public function approvals()
    {
        return $this->hasMany(RabApproval::class);
    }

    public function payment()
    {
        return $this->hasOne(RabPayment::class);
    }

    public function cashFlows()
    {
        return $this->hasMany(CashFlow::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function discussions()
    {
        return $this->hasMany(RabDiscussion::class);
    }

    public function notifications()
    {
        return $this->hasMany(RabNotification::class);
    }

    // ── Helpers ──

    /**
     * Get the expense items based on the expense type.
     */
    public function getExpenseItems()
    {
        return match ($this->expenseType?->code) {
            'operasional' => $this->operationalExpenseItems,
            'petty_cash' => $this->pettyCashItems,
            'gaji' => $this->salaryExpenseItems,
            'bulanan' => $this->monthlyExpenseItems,
            default => collect(),
        };
    }

    /**
     * Get period month name.
     */
    public function getPeriodMonthNameAttribute()
    {
        if (!$this->period_month) return '-';
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        // Handle if it's already a string or invalid integer
        return $bulanList[(int) $this->period_month] ?? $this->period_month;
    }

    public function getPeriodLabelAttribute(): string
    {
        $year = $this->period_year ?: $this->request_date?->format('Y');

        return trim($this->period_month_name . ' ' . $year);
    }

    public function getExpenseItemsCountAttribute(): int
    {
        return $this->getExpenseItems()->count();
    }

    public function getExpenseItemsCountLabelAttribute(): string
    {
        $label = match ($this->expenseType?->code) {
            'gaji' => 'penerima',
            'operasional', 'petty_cash', 'bulanan' => 'item',
            default => 'data',
        };

        return $this->expense_items_count . ' ' . $label;
    }

    public function getTotalAmountInWordsAttribute(): string
    {
        return trim($this->numberToIndonesianWords((int) round((float) $this->total_amount))) . ' rupiah';
    }

    public function buildWhatsAppSubmissionMessage(?string $approvalUrl = null): string
    {
        $paymentType = $this->expenseType->name ?? 'RAB';
        $period = $this->period_label ?: '-';
        $adminName = $this->user->name ?? '-';
        $importantNote = $this->description
            ? $this->description
            : 'Pengajuan pembayaran ini dibutuhkan untuk kelancaran operasional periode ' . $period . '.';
        $approvalUrl = $approvalUrl ?: url('/manajer/rab/' . $this->id);

        return "Pengajuan Pembayaran {$paymentType} - {$period}\n\n"
            . "Yth. Pak Alpian\n\n"
            . "Saya bermaksud mengajukan pembayaran {$paymentType} untuk periode {$period} dengan total kebutuhan dana sebesar Rp"
            . number_format((float) $this->total_amount, 0, ',', '.')
            . " ({$this->total_amount_in_words}).\n\n"
            . "Berikut ringkasan rinciannya:\n\n"
            . "Total: {$this->expense_items_count_label}.\n\n"
            . "Poin Penting: {$importantNote}.\n\n"
            . "Mohon pemeriksaan dan persetujuan Bapak agar pembayaran dapat segera diproses. Rincian lengkap tersedia pada sistem melalui link berikut:\n\n"
            . "{$approvalUrl}\n\n"
            . "Terima kasih atas arahan Bapak.\n\n"
            . "Hormat saya,\n\n"
            . "{$adminName}";
    }

    public function addDiscussionNote(int $userId, string $message): RabDiscussion
    {
        return $this->discussions()->create([
            'user_id' => $userId,
            'message' => $message,
        ]);
    }

    public function notifyUser(int $userId, string $title, string $message, ?string $waMessage = null): RabNotification
    {
        $notification = $this->notifications()->create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
        ]);

        $user = User::find($userId);
        if ($user && !empty($user->phone_number)) {
            $textToSend = $waMessage ?: "*{$title}*\n\n{$message}";
            \App\Services\WhatsAppService::send($user->phone_number, $textToSend);
        }

        return $notification;
    }

    public function notifyRole(string $role, string $title, string $message, ?int $exceptUserId = null, ?string $waMessage = null): void
    {
        User::where('role', $role)
            ->where('is_active', true)
            ->when($exceptUserId, fn ($query) => $query->where('id', '!=', $exceptUserId))
            ->get()
            ->each(function (User $user) use ($title, $message, $waMessage) {
                $this->notifyUser($user->id, $title, $message, $waMessage);
            });
    }

    private function numberToIndonesianWords(int $number): string
    {
        $number = abs($number);
        if ($number === 0) {
            return 'nol';
        }

        $words = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima',
            'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas',
        ];

        if ($number < 12) {
            return $words[$number];
        }

        if ($number < 20) {
            return $this->numberToIndonesianWords($number - 10) . ' belas';
        }

        if ($number < 100) {
            return trim($this->numberToIndonesianWords(intdiv($number, 10)) . ' puluh ' . $this->numberToIndonesianWords($number % 10));
        }

        if ($number < 200) {
            return trim('seratus ' . $this->numberToIndonesianWords($number - 100));
        }

        if ($number < 1000) {
            return trim($this->numberToIndonesianWords(intdiv($number, 100)) . ' ratus ' . $this->numberToIndonesianWords($number % 100));
        }

        if ($number < 2000) {
            return trim('seribu ' . $this->numberToIndonesianWords($number - 1000));
        }

        if ($number < 1000000) {
            return trim($this->numberToIndonesianWords(intdiv($number, 1000)) . ' ribu ' . $this->numberToIndonesianWords($number % 1000));
        }

        if ($number < 1000000000) {
            return trim($this->numberToIndonesianWords(intdiv($number, 1000000)) . ' juta ' . $this->numberToIndonesianWords($number % 1000000));
        }

        if ($number < 1000000000000) {
            return trim($this->numberToIndonesianWords(intdiv($number, 1000000000)) . ' miliar ' . $this->numberToIndonesianWords($number % 1000000000));
        }

        return trim($this->numberToIndonesianWords(intdiv($number, 1000000000000)) . ' triliun ' . $this->numberToIndonesianWords($number % 1000000000000));
    }

    /**
     * Recalculate total amount from items.
     */
    public function recalculateTotal(): void
    {
        $total = match ($this->expenseType?->code) {
            'operasional' => $this->operationalExpenseItems()->sum('total'),
            'petty_cash' => $this->pettyCashItems()->sum('total'),
            'gaji' => $this->salaryExpenseItems()->sum('total_salary'),
            'bulanan' => $this->monthlyExpenseItems()->sum('total_payment'),
            default => 0,
        };

        $this->update(['total_amount' => $total]);
    }

    /**
     * Generate next RAB number.
     */
    public static function generateNumber(?string $month = null, ?string $year = null): string
    {
        return static::buildNumber(
            static::nextSequence(),
            $month ?: now()->format('m'),
            $year ?: now()->format('Y')
        );
    }

    public static function nextSequence(): int
    {
        $rabs = static::withTrashed()->get(['rab_number']);
        $maxNumber = 0;

        foreach ($rabs as $rab) {
            $parts = explode('/', $rab->rab_number);
            if (count($parts) > 1 && is_numeric($parts[0])) {
                $num = (int) $parts[0];
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        return $maxNumber + 1;
    }

    public static function buildNumber(int $sequence, ?string $month = null, ?string $year = null): string
    {
        return str_pad($sequence, 3, '0', STR_PAD_LEFT)
            . '/RAB/SBK/'
            . static::normalizeMonthToRoman($month ?: now()->format('m'))
            . '/'
            . static::normalizeYear($year ?: now()->format('Y'));
    }

    public static function parseNumberParts(?string $rabNumber): array
    {
        $parts = explode('/', (string) $rabNumber);

        return [
            'sequence' => isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : static::nextSequence(),
            'month' => $parts[3] ?? static::normalizeMonthToRoman(now()->format('m')),
            'year' => $parts[4] ?? now()->format('Y'),
        ];
    }

    public static function normalizeMonthToRoman(string|int|null $month): string
    {
        $month = strtoupper(trim((string) $month));
        $romanMonths = [
            '1' => 'I', '01' => 'I', 'JANUARI' => 'I', 'JANUARY' => 'I', 'I' => 'I',
            '2' => 'II', '02' => 'II', 'FEBRUARI' => 'II', 'FEBRUARY' => 'II', 'II' => 'II',
            '3' => 'III', '03' => 'III', 'MARET' => 'III', 'MARCH' => 'III', 'III' => 'III',
            '4' => 'IV', '04' => 'IV', 'APRIL' => 'IV', 'IV' => 'IV',
            '5' => 'V', '05' => 'V', 'MEI' => 'V', 'MAY' => 'V', 'V' => 'V',
            '6' => 'VI', '06' => 'VI', 'JUNI' => 'VI', 'JUNE' => 'VI', 'VI' => 'VI',
            '7' => 'VII', '07' => 'VII', 'JULI' => 'VII', 'JULY' => 'VII', 'VII' => 'VII',
            '8' => 'VIII', '08' => 'VIII', 'AGUSTUS' => 'VIII', 'AUGUST' => 'VIII', 'VIII' => 'VIII',
            '9' => 'IX', '09' => 'IX', 'SEPTEMBER' => 'IX', 'IX' => 'IX',
            '10' => 'X', 'OKTOBER' => 'X', 'OCTOBER' => 'X', 'X' => 'X',
            '11' => 'XI', 'NOVEMBER' => 'XI', 'XI' => 'XI',
            '12' => 'XII', 'DESEMBER' => 'XII', 'DECEMBER' => 'XII', 'XII' => 'XII',
        ];

        return $romanMonths[$month] ?? static::normalizeMonthToRoman(now()->format('m'));
    }

    public static function normalizeYear(string|int|null $year): string
    {
        $year = preg_replace('/[^0-9]/', '', (string) $year);

        return strlen($year) === 4 ? $year : now()->format('Y');
    }
}
