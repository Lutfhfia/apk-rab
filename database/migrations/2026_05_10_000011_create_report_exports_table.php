<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menjalankan migrasi database
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exported_by')->constrained('users')->cascadeOnDelete();
            $table->string('report_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('file_path')->nullable();
            $table->enum('format', ['pdf', 'excel']);
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->decimal('ending_balance', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Membatalkan migrasi database (mengembalikan perubahan)
        Schema::dropIfExists('report_exports');
    }
};
