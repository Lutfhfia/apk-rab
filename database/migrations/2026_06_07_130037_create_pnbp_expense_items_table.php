<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menjalankan migrasi database
        Schema::create('pnbp_expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_id')->constrained('rabs')->cascadeOnDelete();
            $table->string('item_name');
            $table->string('agenda_number');
            $table->string('level');
            $table->decimal('tarif_pnbp', 15, 2);
            $table->string('company_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Membatalkan migrasi database (mengembalikan perubahan)
        Schema::dropIfExists('pnbp_expense_items');
    }
};
