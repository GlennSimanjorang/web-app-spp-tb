<?php

namespace Database\Seeders;

use App\Models\PaymentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentCategorySeeder extends Seeder
{
    public function run()
    {
        PaymentCategory::create([
            'id' => Str::uuid(),
            'name' => 'SPP Bulanan',
            'amount' => 300000,
            'frequency' => 'monthly',
            'description' => 'Biaya SPP untuk siswa kelas 1-12',
            'is_active' => true,
        ]);

        PaymentCategory::create([
            'id' => Str::uuid(),
            'name' => 'Uang Gedung',
            'amount' => 2500000,
            'frequency' => 'once',
            'description' => 'Biaya satu kali saat pendaftaran',
            'is_active' => true,
        ]);

        PaymentCategory::create([
            'id' => Str::uuid(),
            'name' => 'Uang Buku Tahunan',
            'amount' => 750000,
            'frequency' => 'yearly',
            'description' => 'Biaya buku pelajaran per tahun',
            'is_active' => true,
        ]);
    }
}
