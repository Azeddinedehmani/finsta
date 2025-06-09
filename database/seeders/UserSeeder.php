<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin Pharmacia',
            'email' => 'admin@pharmacia.com',
            'password' => Hash::make('password123'),
            'role' => 'responsable',
        ]);

        // Create pharmacist user
        User::create([
            'name' => 'Pharmacien Test',
            'email' => 'pharmacien@pharmacia.com',
            'password' => Hash::make('password123'),
            'role' => 'pharmacien',
        ]);
    }
}