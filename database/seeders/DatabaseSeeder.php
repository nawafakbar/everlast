<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Akun Admin
        User::create([
            'name' => 'Admin Everlast',
            'email' => 'admin@everlast.com',
            'password' => Hash::make('password123!'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // 2. Create Akun Freelancer (Dummy)
        User::create([
            'name' => 'Budi Freelancer',
            'email' => 'freelancer@everlast.com',
            'password' => Hash::make('password123!'),
            'role' => 'freelancer',
            'email_verified_at' => now(),
        ]);

        // 3. Create Akun Customer (Dummy)
        User::create([
            'name' => 'Siti Customer',
            'email' => 'customer@everlast.com',
            'password' => Hash::make('password123!'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);
    }
}