<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Keuangan',
                'email' => 'luthfiandini1909@gmail.com',
                'password' => 'andin2005',
                'role' => 'admin_keuangan',
                'is_active' => true,
            ],
            [
                'name' => 'Manajer Keuangan',
                'email' => 'manajer@rab-sbk.com',
                'password' => 'password',
                'role' => 'manajer_keuangan',
                'is_active' => true,
            ],
            [
                'name' => 'Direktur',
                'email' => 'direktur@rab-sbk.com',
                'password' => 'password',
                'role' => 'direktur',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
