<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            AcademicYearSeeder::class,
            PaymentCategorySeeder::class,
            UserSeeder::class,
            StudentSeeder::class,
            BillSeeder::class,
        ]);
    }
}
