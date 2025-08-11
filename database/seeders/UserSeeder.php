<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Andi Wijaya',
                'phone_number' => '081234567890',
                'email' => 'andi@example.com',
            ],
            [
                'name' => 'Siti Rahayu',
                'phone_number' => '081234567891',
                'email' => 'siti@example.com',
            ],
            [
                'name' => 'Budi Santoso',
                'phone_number' => '081234567892',
                'email' => 'budi@example.com',
            ],
        ];

        foreach ($users as $user) {
            User::create([
                'id' => Str::uuid(),
                'name' => $user['name'],
                'role' => 'parent',
                'phone_number' => $user['phone_number'],
                'email' => $user['email'],
                'password' => Hash::make('password'), 
            ]);
        }
    }
}
