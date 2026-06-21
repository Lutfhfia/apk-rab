<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Membuat data awal untuk pengguna (Admin Keuangan, Manajer Keuangan, Direktur).
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Keuangan',
                'email' => 'luthfiandini1909@gmail.com',
                'password' => 'andin2005',
                'role' => 'admin_keuangan',
                'phone_number' => '08123456789',
                'is_active' => true,
            ],
            [
                'name' => 'Manajer Keuangan',
                'email' => 'manajer@rab-sbk.com',
                'password' => 'password',
                'role' => 'manajer_keuangan',
                'phone_number' => '08987654321',
                'is_active' => true,
            ],
            [
                'name' => 'Direktur',
                'email' => '062330801581@student.polsri.ac.id',
                'password' => 'password',
                'role' => 'direktur',
                'phone_number' => '08112233445',
                'is_active' => true,
            ], //jika setelah melakukkan perubahan pada database //php artisan db:seed //php artisan db:seed --class=NamaSeeder //php artisan migrate:fresh --seed
        ];

        foreach ($users as $userData) {
            $lookup = $userData['role'] === 'direktur'
                ? ['role' => 'direktur']
                : ['email' => $userData['email']];

            User::updateOrCreate(
                $lookup,
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
