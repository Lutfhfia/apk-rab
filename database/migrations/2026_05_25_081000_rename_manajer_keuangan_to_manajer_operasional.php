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
        if (config('database.default') === 'mysql') {
            // 1. Temporarily expand users.role to include both old and new roles
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin_keuangan', 'manajer_keuangan', 'manajer_operasional', 'direktur') NOT NULL DEFAULT 'admin_keuangan'");
        }

        // 2. Change any existing user role 'manajer_keuangan' to 'manajer_operasional' if exists
        \Illuminate\Support\Facades\DB::table('users')->where('role', 'manajer_keuangan')->update(['role' => 'manajer_operasional']);

        if (config('database.default') === 'mysql') {
            // 3. Shrink users.role back, excluding the old role
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin_keuangan', 'manajer_operasional', 'direktur') NOT NULL DEFAULT 'admin_keuangan'");
        }
        
        // Also do the same for rab_approvals if it exists
        if (\Schema::hasTable('rab_approvals')) {
            if (config('database.default') === 'mysql') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE rab_approvals MODIFY COLUMN role ENUM('manajer_keuangan', 'manajer_operasional', 'direktur') NOT NULL");
            }

            \Illuminate\Support\Facades\DB::table('rab_approvals')->where('role', 'manajer_keuangan')->update(['role' => 'manajer_operasional']);

            if (config('database.default') === 'mysql') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE rab_approvals MODIFY COLUMN role ENUM('manajer_operasional', 'direktur') NOT NULL");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin_keuangan', 'manajer_keuangan', 'manajer_operasional', 'direktur') NOT NULL DEFAULT 'admin_keuangan'");
        }

        \Illuminate\Support\Facades\DB::table('users')->where('role', 'manajer_operasional')->update(['role' => 'manajer_keuangan']);

        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin_keuangan', 'manajer_keuangan', 'direktur') NOT NULL DEFAULT 'admin_keuangan'");
        }
        
        if (\Schema::hasTable('rab_approvals')) {
            if (config('database.default') === 'mysql') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE rab_approvals MODIFY COLUMN role ENUM('manajer_keuangan', 'manajer_operasional', 'direktur') NOT NULL");
            }

            \Illuminate\Support\Facades\DB::table('rab_approvals')->where('role', 'manajer_operasional')->update(['role' => 'manajer_keuangan']);

            if (config('database.default') === 'mysql') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE rab_approvals MODIFY COLUMN role ENUM('manajer_keuangan', 'direktur') NOT NULL");
            }
        }
    }
};
