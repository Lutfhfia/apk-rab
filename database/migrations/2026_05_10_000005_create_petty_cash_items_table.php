<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menjalankan migrasi database
        Schema::create('petty_cash_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_id')->constrained('rabs')->cascadeOnDelete();
            $table->string('expense_name');
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->decimal('nominal', 15, 2);
            $table->string('receipt_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Membatalkan migrasi database (mengembalikan perubahan)
        Schema::dropIfExists('petty_cash_items');
    }
};
