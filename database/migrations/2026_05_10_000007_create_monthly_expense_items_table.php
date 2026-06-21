<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menjalankan migrasi database
        Schema::create('monthly_expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_id')->constrained('rabs')->cascadeOnDelete();
            $table->string('payment_name');
            $table->string('period');
            $table->text('description')->nullable();
            $table->decimal('bill_nominal', 15, 2);
            $table->decimal('admin_fee', 15, 2)->default(0);
            $table->decimal('total_payment', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Membatalkan migrasi database (mengembalikan perubahan)
        Schema::dropIfExists('monthly_expense_items');
    }
};
