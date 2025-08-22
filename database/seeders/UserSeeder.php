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
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Kevin Villacreses',
                'password' => Hash::make('dalcroze77aA@'),
                'role' => 'admin',
                'phone' => '+593963368896',
                'address' => 'Oficina Principal EcoPlagas, Quito, Ecuador',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}