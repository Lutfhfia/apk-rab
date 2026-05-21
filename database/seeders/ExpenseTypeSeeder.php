<?php

namespace Database\Seeders;

use App\Models\ExpenseType;
use Illuminate\Database\Seeder;

class ExpenseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'operasional',
                'name' => 'Biaya Operasional',
                'description' => 'Biaya kebutuhan operasional perusahaan yang berkaitan dengan aktivitas kerja sehari-hari atau kegiatan teknis perusahaan.',
            ],
            [
                'code' => 'petty_cash',
                'name' => 'Petty Cash',
                'description' => 'Pengeluaran kecil atau pengeluaran harian yang nominalnya relatif lebih kecil dan bersifat cepat.',
            ],
            [
                'code' => 'gaji',
                'name' => 'Biaya Gaji',
                'description' => 'Kebutuhan pembayaran gaji, honorarium, atau pembayaran kepada pegawai/karyawan.',
            ],
            [
                'code' => 'bulanan',
                'name' => 'Biaya Bulanan',
                'description' => 'Pembayaran rutin perusahaan yang dilakukan setiap bulan seperti listrik, internet, sewa, dll.',
            ],
        ];

        foreach ($types as $type) {
            ExpenseType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
