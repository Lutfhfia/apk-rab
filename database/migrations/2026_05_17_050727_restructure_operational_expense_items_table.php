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
        // Menjalankan migrasi database
        Schema::table('operational_expense_items', function (Blueprint $table) {
            $table->string('group_name')->after('rab_id')->nullable();
            $table->renameColumn('need_name', 'item_name');
            $table->text('note')->nullable()->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Membatalkan migrasi database (mengembalikan perubahan)
        Schema::table('operational_expense_items', function (Blueprint $table) {
            $table->dropColumn('group_name');
            $table->renameColumn('item_name', 'need_name');
            $table->dropColumn('note');
        });
    }
};
