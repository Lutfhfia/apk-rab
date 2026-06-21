<?php

namespace Database\Seeders;

use App\Enums\CashFlowType;
use App\Enums\RabStatus;
use App\Models\CashFlow;
use App\Models\ExpenseType;
use App\Models\MonthlyExpenseItem;
use App\Models\OperationalExpenseItem;
use App\Models\PettyCashItem;
use App\Models\PnbpExpenseItem;
use App\Models\Rab;
use App\Models\RabPayment;
use App\Models\SalaryExpenseItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDashboardSeeder extends Seeder
{
    /**
     * Mengisi database dengan data dummy lengkap (RAB, Item Pengeluaran, Pembayaran, Arus Kas)
     * untuk simulasi visual grafik pada dashboard.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin_keuangan')->first() ?? User::first();
        $types = ExpenseType::where('is_active', true)->get()->keyBy('code');

    if (!$admin || $types->isEmpty()) {
            return;
    }

        DB::transaction(function () use ($admin, $types) {
            $lastBalance = (float) (CashFlow::orderByDesc('id')->value('balance') ?? 0);
            $sequence = 1;
            $statuses = [
                RabStatus::SELESAI,
                RabStatus::DISETUJUI,
                RabStatus::DISETUJUI_MANAJER,
                RabStatus::DIAJUKAN,
                RabStatus::DITOLAK,
            ];

            for ($monthOffset = 11; $monthOffset >= 0; $monthOffset--) {
                $date = Carbon::now()->subMonths($monthOffset)->startOfMonth()->addDays(4);

                $monthlyIncome = 18000000 + (($monthOffset % 4) * 2500000);
                $lastBalance += $monthlyIncome;
                CashFlow::create([
                    'rab_id' => null,
                    'payment_id' => null,
                    'transaction_date' => $date->copy()->subDays(2),
                    'type' => CashFlowType::DANA_MASUK,
                    'description' => 'Dana masuk dummy bulan ' . $date->translatedFormat('F Y'),
                    'debit' => $monthlyIncome,
                    'credit' => 0,
                    'balance' => $lastBalance,
                    'proof_file_path' => null,
                    'created_by' => $admin->id,
                ]);

                foreach ($types as $code => $type) {
                    $status = $statuses[($sequence + $monthOffset) % count($statuses)];
                    $rabNumber = sprintf('DMY-%03d/RAB/SBK/%s/%s', $sequence, $this->romanMonth($date->month), $date->year);

                    if (Rab::where('rab_number', $rabNumber)->exists()) {
                        $sequence++;
                        continue;
                    }

                    $rab = Rab::create([
                        'rab_number' => $rabNumber,
                        'request_date' => $date->copy()->addDays(($sequence % 4) * 3),
                        'period_month' => (string) $date->month,
                        'period_year' => (string) $date->year,
                        'user_id' => $admin->id,
                        'expense_type_id' => $type->id,
                        'description' => 'Data dummy ' . $type->name . ' periode ' . $date->translatedFormat('F Y'),
                        'status' => $status,
                        'submitted_at' => $status === RabStatus::DRAFT ? null : $date->copy()->addDays(1),
                        'approved_by_manager_at' => in_array($status, [RabStatus::DISETUJUI_MANAJER, RabStatus::DISETUJUI_DIREKTUR, RabStatus::DISETUJUI, RabStatus::SELESAI], true) ? $date->copy()->addDays(2) : null,
                        'approved_by_director_at' => in_array($status, [RabStatus::DISETUJUI_DIREKTUR, RabStatus::DISETUJUI, RabStatus::SELESAI], true) ? $date->copy()->addDays(3) : null,
                        'completed_at' => $status === RabStatus::SELESAI ? $date->copy()->addDays(7) : null,
                    ]);

                    $this->createItems($rab, $code, $date, $sequence);
                    $rab->recalculateTotal();

                    if ($status === RabStatus::SELESAI) {
                        $paidAmount = (float) $rab->total_amount;
                        $payment = RabPayment::create([
                            'rab_id' => $rab->id,
                            'paid_by' => $admin->id,
                            'payment_date' => $date->copy()->addDays(7),
                            'paid_amount' => $paidAmount,
                            'payment_method' => 'Transfer Bank',
                            'recipient_account' => '1234567890',
                            'recipient_name' => 'Vendor Dummy',
                            'proof_file_path' => 'payment-proofs/dummy-proof.pdf',
                            'notes' => 'Pembayaran dummy untuk kebutuhan chart.',
                        ]);

                        $lastBalance -= $paidAmount;
                        CashFlow::create([
                            'rab_id' => $rab->id,
                            'payment_id' => $payment->id,
                            'transaction_date' => $payment->payment_date,
                            'type' => CashFlowType::DANA_KELUAR,
                            'description' => "Pembayaran dummy RAB {$rab->rab_number}",
                            'debit' => 0,
                            'credit' => $paidAmount,
                            'balance' => $lastBalance,
                            'proof_file_path' => 'payment-proofs/dummy-proof.pdf',
                            'created_by' => $admin->id,
                        ]);
                    }

                    $sequence++;
                }
            }
        });
    }

    private function createItems(Rab $rab, string $code, Carbon $date, int $sequence): void
    {
        match ($code) {
            'operasional' => $this->createOperationalItems($rab, $sequence),
            'petty_cash' => $this->createPettyCashItems($rab, $date, $sequence),
            'gaji' => $this->createSalaryItems($rab, $sequence),
            'bulanan' => $this->createMonthlyItems($rab, $date, $sequence),
            'pnbp' => $this->createPnbpItems($rab, $sequence),
            default => null,
        };
    }

    private function createOperationalItems(Rab $rab, int $sequence): void
    {
        $groups = [
            'Honor Pencari Peserta',
            'Uang Transport / Honor Peserta Uji Serkom',
            'Operasional Pembekalan',
        ];

        foreach (['ATK Kantor', 'Transport Operasional', 'Konsumsi Rapat'] as $index => $name) {
            $volume = 2 + $index;
            $price = 275000 + (($sequence + $index) % 5) * 65000;
            OperationalExpenseItem::create([
                'rab_id' => $rab->id,
                'group_name' => $groups[$index % count($groups)],
                'item_name' => $name,
                'description' => 'Dummy kebutuhan operasional',
                'volume' => $volume,
                'unit' => 'paket',
                'unit_price' => $price,
                'total' => $volume * $price,
            ]);
        }
    }

    private function createPettyCashItems(Rab $rab, Carbon $date, int $sequence): void
    {
        foreach (['Parkir dan Tol', 'Materai', 'Kebutuhan Kurir'] as $index => $name) {
            $volume = 1 + $index;
            $price = 125000 + (($sequence + $index) % 4) * 45000;
            $adminFee = 5000 * ($index + 1);
            PettyCashItem::create([
                'rab_id' => $rab->id,
                'expense_name' => $name,
                'description' => 'Dummy petty cash',
                'volume' => $volume,
                'unit' => 'kali',
                'unit_price' => $price,
                'transaction_date' => $date->copy()->addDays($index + 1),
                'nominal' => $volume * $price,
                'admin_fee' => $adminFee,
                'total' => ($volume * $price) + $adminFee,
            ]);
        }
    }

    private function createSalaryItems(Rab $rab, int $sequence): void
    {
        foreach (['Rahmad', 'Siti', 'Dimas'] as $index => $name) {
            $attendance = 20 + (($sequence + $index) % 4);
            $baseSalary = 2500000 + ($index * 350000);
            $meal = 25000;
            $transport = 20000;
            $overtime = (($sequence + $index) % 3) * 150000;
            $total = $baseSalary + ($attendance * $meal) + ($attendance * $transport) + $overtime;
            SalaryExpenseItem::create([
                'rab_id' => $rab->id,
                'employee_name' => $name,
                'position' => ['Admin', 'Staff', 'Teknisi'][$index],
                'bank_account_number' => '98765' . str_pad((string) ($sequence + $index), 5, '0', STR_PAD_LEFT),
                'bank_name' => 'Bank Dummy',
                'attendance_days' => $attendance,
                'base_salary' => $baseSalary,
                'meal_allowance_daily' => $meal,
                'transport_daily' => $transport,
                'overtime' => $overtime,
                'total_salary' => $total,
                'salary_nominal' => $total,
                'notes' => 'Dummy gaji',
                'description' => 'Data dummy gaji',
            ]);
        }
    }

    private function createMonthlyItems(Rab $rab, Carbon $date, int $sequence): void
    {
        foreach (['Internet Kantor', 'Listrik', 'Sewa Aplikasi'] as $index => $name) {
            $expense = 650000 + (($sequence + $index) % 5) * 175000;
            $adminFee = 7500 * ($index + 1);
            MonthlyExpenseItem::create([
                'rab_id' => $rab->id,
                'payment_name' => $name,
                'registration_number' => 'REG-' . $date->format('Ym') . '-' . ($index + 1),
                'account_name' => 'PT SBK',
                'period' => $date->translatedFormat('F Y'),
                'description' => 'Dummy pembayaran bulanan',
                'total_expense' => $expense,
                'bill_nominal' => $expense,
                'admin_fee' => $adminFee,
                'total_payment' => $expense + $adminFee,
                'transaction_date' => $date->copy()->addDays($index + 1),
            ]);
        }
    }

    private function createPnbpItems(Rab $rab, int $sequence): void
    {
        foreach (['Sertifikasi Uji Kompetensi', 'Verifikasi Dokumen Teknik'] as $index => $name) {
            $price = 1500000 + (($sequence + $index) % 3) * 500000;
            PnbpExpenseItem::create([
                'rab_id' => $rab->id,
                'item_name' => $name,
                'agenda_number' => 'AGD-SBK-' . str_pad((string) ($sequence + $index), 4, '0', STR_PAD_LEFT),
                'level' => 'Level ' . (1 + (($sequence + $index) % 3)),
                'tarif_pnbp' => $price,
                'company_name' => ['PT PLN', 'PT Indonesia Power', 'PT PJB'][$index % 3],
            ]);
        }
    }

    private function romanMonth(int $month): string
    {
        return [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ][$month];
    }
}
