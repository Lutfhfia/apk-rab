<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Mengisi database aplikasi dengan data awal (seed).
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ExpenseTypeSeeder::class,
            DummyDashboardSeeder::class,
        ]);
    }
}
