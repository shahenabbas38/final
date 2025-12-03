<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClinicAndSpecialtySeeder extends Seeder
{
    public function run(): void
    {
        // 🏥 إدخال عيادات افتراضية
        DB::table('clinics')->insert([
            [
                'name' => 'SmartCare Main Clinic',
                'address' => 'Damascus - Mazzeh Street',
                'latitude' => 33.5102,
                'longitude' => 36.2913,
                'timezone' => 'Asia/Damascus',
                'phone' => '+963-11-5555555',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SmartCare North Branch',
                'address' => 'Aleppo - New Town',
                'latitude' => 36.2021,
                'longitude' => 37.1343,
                'timezone' => 'Asia/Damascus',
                'phone' => '+963-21-2222222',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 🧠 إدخال تخصصات طبية افتراضية
        DB::table('specialties')->insert([
            ['name' => 'Cardiology'],
            ['name' => 'Neurology'],
            ['name' => 'Dermatology'],
            ['name' => 'Pediatrics'],
            ['name' => 'Orthopedics'],
        ]);

        echo "✅ Clinics and Specialties seeded successfully.\n";
    }
}
