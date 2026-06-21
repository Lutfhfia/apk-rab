<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('salary_expense_items', function (Blueprint $table) {
            $table->decimal('deduction', 15, 2)->default(0)->after('total_salary')->comment('Potongan nominal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_expense_items', function (Blueprint $table) {
            $table->dropColumn('deduction');
        });
    }
};
