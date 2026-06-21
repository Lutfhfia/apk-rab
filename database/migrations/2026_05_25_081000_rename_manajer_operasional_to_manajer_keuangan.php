<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Menjalankan migrasi database
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin_keuangan', 'manajer_keuangan', 'manajer_operasional', 'direktur') NOT NULL DEFAULT 'admin_keuangan'");
        }

        \Illuminate\Support\Facades\DB::table('users')->where('role', 'manajer_operasional')->update(['role' => 'manajer_keuangan']);

        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin_keuangan', 'manajer_keuangan', 'direktur') NOT NULL DEFAULT 'admin_keuangan'");
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('rab_approvals')) {
            if (config('database.default') === 'mysql') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE rab_approvals MODIFY COLUMN role ENUM('manajer_keuangan', 'manajer_operasional', 'direktur') NOT NULL");
            }

            \Illuminate\Support\Facades\DB::table('rab_approvals')->where('role', 'manajer_operasional')->update(['role' => 'manajer_keuangan']);

            if (config('database.default') === 'mysql') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE rab_approvals MODIFY COLUMN role ENUM('manajer_keuangan', 'direktur') NOT NULL");
            }
        }
    }

    public function down(): void
    {
        // Membatalkan migrasi database (mengembalikan perubahan)
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin_keuangan', 'manajer_keuangan', 'manajer_operasional', 'direktur') NOT NULL DEFAULT 'admin_keuangan'");
        }

        \Illuminate\Support\Facades\DB::table('users')->where('role', 'manajer_keuangan')->update(['role' => 'manajer_operasional']);

        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin_keuangan', 'manajer_operasional', 'direktur') NOT NULL DEFAULT 'admin_keuangan'");
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('rab_approvals')) {
            if (config('database.default') === 'mysql') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE rab_approvals MODIFY COLUMN role ENUM('manajer_keuangan', 'manajer_operasional', 'direktur') NOT NULL");
            }

            \Illuminate\Support\Facades\DB::table('rab_approvals')->where('role', 'manajer_keuangan')->update(['role' => 'manajer_operasional']);

            if (config('database.default') === 'mysql') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE rab_approvals MODIFY COLUMN role ENUM('manajer_operasional', 'direktur') NOT NULL");
            }
        }
    }
};
