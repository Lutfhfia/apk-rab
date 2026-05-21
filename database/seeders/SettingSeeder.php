<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'company_name' => 'PT Sertifikasi Bermutu Ketenagalistrikan',
            'company_address' => 'Jl. Contoh Alamat No. 123, Jambi',
            'company_phone' => '(0741) 123-4567',
            'company_email' => 'info@sbk-sertifikasi.co.id',
            'report_signer_name' => 'Direktur Utama',
            'report_signer_position' => 'Direktur',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
