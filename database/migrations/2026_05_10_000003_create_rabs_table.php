<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menjalankan migrasi database
        Schema::create('rabs', function (Blueprint $table) {
            $table->id();
            $table->string('rab_number')->unique();
            $table->date('request_date');
            $table->string('period_month')->nullable();
            $table->string('period_year')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('expense_type_id')->constrained('expense_types')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', [
                'draft',
                'diajukan',
                'disetujui_manajer',
                'disetujui_direktur',
                'disetujui',
                'ditolak',
                'selesai'
            ])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_by_manager_at')->nullable();
            $table->timestamp('approved_by_director_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        // Membatalkan migrasi database (mengembalikan perubahan)
        Schema::dropIfExists('rabs');
    }
};
