<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Petty Cash: tambah volume, satuan, harga satuan, admin, total ──
        Schema::table('petty_cash_items', function (Blueprint $table) {
            $table->decimal('volume', 15, 2)->default(1)->after('description');
            $table->string('unit')->default('pcs')->after('volume');
            $table->decimal('unit_price', 15, 2)->default(0)->after('unit');
            $table->decimal('admin_fee', 15, 2)->default(0)->after('nominal');
            $table->decimal('total', 15, 2)->default(0)->after('admin_fee');
        });

        // ── Salary: tambah hadir, gaji pokok, uang makan, transport, lembur, total gaji, catatan ──
        Schema::table('salary_expense_items', function (Blueprint $table) {
            $table->integer('attendance_days')->default(0)->after('bank_name');
            $table->decimal('base_salary', 15, 2)->default(0)->after('attendance_days');
            $table->decimal('meal_allowance_daily', 15, 2)->default(0)->after('base_salary');
            $table->decimal('transport_daily', 15, 2)->default(20000)->after('meal_allowance_daily');
            $table->decimal('overtime', 15, 2)->default(0)->after('transport_daily');
            $table->decimal('total_salary', 15, 2)->default(0)->after('overtime');
            $table->text('notes')->nullable()->after('total_salary');
        });

        // ── Monthly: tambah noregist, account_name, total_expense, transaction_date ──
        Schema::table('monthly_expense_items', function (Blueprint $table) {
            $table->string('registration_number')->nullable()->after('payment_name');
            $table->string('account_name')->nullable()->after('registration_number');
            $table->decimal('total_expense', 15, 2)->default(0)->after('description');
            $table->date('transaction_date')->nullable()->after('total_payment');
        });
    }

    public function down(): void
    {
        Schema::table('petty_cash_items', function (Blueprint $table) {
            $table->dropColumn(['volume', 'unit', 'unit_price', 'admin_fee', 'total']);
        });

        Schema::table('salary_expense_items', function (Blueprint $table) {
            $table->dropColumn(['attendance_days', 'base_salary', 'meal_allowance_daily', 'transport_daily', 'overtime', 'total_salary', 'notes']);
        });

        Schema::table('monthly_expense_items', function (Blueprint $table) {
            $table->dropColumn(['registration_number', 'account_name', 'total_expense', 'transaction_date']);
        });
    }
};
