<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@simak.local'],
            [
                'name' => 'Administrator',
                'email' => 'admin@simak.local',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create assessor user
        User::firstOrCreate(
            ['email' => 'assessor@simak.local'],
            [
                'name' => 'Assessor',
                'email' => 'assessor@simak.local',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
