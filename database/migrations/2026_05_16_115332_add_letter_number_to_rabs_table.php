<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menjalankan migrasi database
        Schema::table('rabs', function (Blueprint $table) {
            $table->string('letter_number')->nullable()->after('rab_number');
        });
    }

    public function down(): void
    {
        // Membatalkan migrasi database (mengembalikan perubahan)
        Schema::table('rabs', function (Blueprint $table) {
            $table->dropColumn('letter_number');
        });
    }
};
