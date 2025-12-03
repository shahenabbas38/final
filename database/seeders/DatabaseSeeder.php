<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// ✅ أضف هذا السطر
use Database\Seeders\ClinicAndSpecialtySeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ClinicAndSpecialtySeeder::class);
    }
}
