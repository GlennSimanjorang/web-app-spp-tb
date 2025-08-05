<?php

namespace Database\Seeders;

use App\Models\Bill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class BillSeeder extends Seeder
{
    public function run()
    {
        $academicYear = \App\Models\AcademicYear::where('school_year', '2024/2025')->first();
        $spp = \App\Models\PaymentCategory::where('name', 'SPP Bulanan')->first();
        $gedung = \App\Models\PaymentCategory::where('name', 'Uang Gedung')->first();
        $buku = \App\Models\PaymentCategory::where('name', 'Uang Buku Tahunan')->first();
        $students = \App\Models\Student::all();

        if (!$academicYear || !$spp || !$gedung || !$buku || $students->isEmpty()) return;

        // Tagihan SPP Bulanan (contoh: Juli 2024)
        foreach ($students as $student) {
            Bill::create([
                'id' => Str::uuid(),
                'bill_number' => 'SPP-' . $student->nisn . '-072024',
                'month_year' => '2024-07',
                'due_date' => '2024-07-10',
                'amount' => $spp->amount,
                'status' => 'unpaid',
                'payment_categories_id' => $spp->id,
                'student_id' => $student->id,
                'academic_years_id' => $academicYear->id,
            ]);
        }

        // Uang Gedung (hanya untuk siswa baru)
        Bill::create([
            'id' => Str::uuid(),
            'bill_number' => 'UG-' . $students[0]->nisn,
            'due_date' => '2024-07-15',
            'amount' => $gedung->amount,
            'status' => 'unpaid',
            'payment_categories_id' => $gedung->id,
            'student_id' => $students[0]->id,
            'academic_years_id' => $academicYear->id,
        ]);

        // Uang Buku Tahunan
        Bill::create([
            'id' => Str::uuid(),
            'bill_number' => 'UB-' . $students[0]->nisn . '-2024',
            'due_date' => '2024-07-20',
            'amount' => $buku->amount,
            'status' => 'unpaid',
            'payment_categories_id' => $buku->id,
            'student_id' => $students[0]->id,
            'academic_years_id' => $academicYear->id,
        ]);
    }
}
