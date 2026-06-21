<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menjalankan migrasi database
        Schema::create('salary_expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_id')->constrained('rabs')->cascadeOnDelete();
            $table->string('employee_name');
            $table->string('position')->nullable();
            $table->string('bank_account_number');
            $table->string('bank_name');
            $table->decimal('salary_nominal', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Membatalkan migrasi database (mengembalikan perubahan)
        Schema::dropIfExists('salary_expense_items');
    }
};
