<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class StudentSeeder extends Seeder
{
    public function run()
    {
        $students = [
            ['name' => 'Rina Putri', 'nisn' => '1234567890', 'kelas' => 'XII IPA 1', 'user_id' => User::where('email', 'andi@example.com')->first()->id ?? null],
            ['name' => 'Agus Kurniawan', 'nisn' => '1234567891', 'kelas' => 'XI IPS 2', 'user_id' => User::where('email', 'siti@example.com')->first()->id ?? null],
            ['name' => 'Dewi Lestari', 'nisn' => '1234567892', 'kelas' => 'X IPA 3', 'user_id' => User::where('email', 'budi@example.com')->first()->id ?? null],
        ];

        foreach ($students as $data) {
            if ($data['user_id']) {
                Student::create([
                    'id' => Str::uuid(),
                    'name' => $data['name'],
                    'nisn' => $data['nisn'],
                    'kelas' => $data['kelas'],
                    'user_id' => $data['user_id'],
                ]);
            }
        }
    }
}
