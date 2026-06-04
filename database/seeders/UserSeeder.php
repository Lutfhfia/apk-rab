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
                'name' => 'Manajer Operasional',
                'email' => 'manajer@rab-sbk.com',
                'password' => 'password',
                'role' => 'manajer_operasional',
                'is_active' => true,
            ],
            [
                'name' => 'Direktur',
                'email' => 'direktur@rab-sbk.com',
                'password' => 'password',
                'role' => 'direktur',
                'is_active' => true,
            ], //php artisan db:seed //php artisan db:seed --class=NamaSeeder
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
//php artisan tinker

// $user = App\Models\User::where('email', 'luthfiandini1909@gmail.com')->first();
// $user->password = Hash::make('passwordbaru');
// $user->save();

// exit
