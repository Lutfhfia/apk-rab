<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ExpenseType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        ExpenseType::updateOrCreate(
            ['code' => 'listrik'],
            [
                'name' => 'Biaya Listrik',
                'description' => 'Pengeluaran untuk pembayaran tagihan listrik bulanan.',
                'is_active' => true,
            ]
        );
        ExpenseType::updateOrCreate(
            ['code' => 'air_pam'],
            [
                'name' => 'Biaya Air PAM',
                'description' => 'Pengeluaran untuk pembayaran tagihan air PAM bulanan.',
                'is_active' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        ExpenseType::whereIn('code', ['listrik', 'air_pam'])->delete();
    }
};
