<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@lehrzeit.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Create a test lecturer
        User::firstOrCreate(
            ['email' => 'lecturer@lehrzeit.com'],
            [
                'name' => 'Test Lecturer',
                'password' => Hash::make('password'),
                'role' => 'lecturer',
            ]
        );
    }
}
