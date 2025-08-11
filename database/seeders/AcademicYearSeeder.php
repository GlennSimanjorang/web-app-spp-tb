<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class AcademicYearSeeder extends Seeder
{
    public function run()
    {
        AcademicYear::create([
            'id' => Str::uuid(),
            'school_year' => '2024/2025',
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ]);

        AcademicYear::create([
            'id' => Str::uuid(),
            'school_year' => '2023/2024',
            'start_date' => '2023-07-01',
            'end_date' => '2024-06-30',
            'is_active' => false,
        ]);
    }
}
