<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menjalankan migrasi database
        Schema::create('cash_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_id')->nullable()->constrained('rabs')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('rab_payments')->nullOnDelete();
            $table->date('transaction_date');
            $table->enum('type', ['saldo_awal', 'dana_masuk', 'dana_keluar']);
            $table->text('description');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Membatalkan migrasi database (mengembalikan perubahan)
        Schema::dropIfExists('cash_flows');
    }
};
