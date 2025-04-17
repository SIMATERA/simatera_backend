<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create Admin
        User::create([
            'nama' => 'Administrator',
            'email' => 'admin@asrama.com',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true
        ]);

        // Create Kasra
        User::create([
            'nama' => 'Kasra TB1',
            'email' => 'kasra.tb1@asrama.com',
            'username' => 'kasratb1',
            'password' => Hash::make('kasra123'),
            'role' => 'kasra',
            'is_active' => true
        ]);

        // Create Test User
        User::create([
            'nama' => 'Jos Ray',
            'email' => 'josray10@asrama.com',
            'username' => 'josray10',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'is_active' => true
        ]);
    }
}