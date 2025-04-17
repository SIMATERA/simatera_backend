<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Kamar;
use Carbon\Carbon;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure we have users and kamar data available
        $users = User::all();
        $kamars = Kamar::all();
        
        // Check if necessary data exists
        if ($users->isEmpty()) {
            throw new \Exception('Please run UserSeeder first.');
        }
        
        if ($kamars->isEmpty()) {
            throw new \Exception('Please run KamarSeeder first.');
        }
        
        // Get a specific admin user for created_by if available
        $adminUser = User::where('role', 'admin')->first() ?? $users->first();
        
        // Sample data for mahasiswa
        $mahasiswaData = [
            [
                'nim' => '190411100001',
                'nama' => 'Ahmad Zaki',
                'email' => 'ahmadzaki@student.example.ac.id',
                'prodi' => 'Teknik Informatika',
                'kamar_id' => $kamars->random()->id,
                'tanggal_lahir' => Carbon::parse('2000-05-15'),
                'tempat_lahir' => 'Jakarta',
                'asal' => 'Jakarta Selatan',
                'jenis_kelamin' => 'Laki-laki',
                'golongan_ukt' => '3',
                'status' => 'Aktif Tinggal',
                'password' => Hash::make('password123'),
                'user_id' => $users->random()->id,
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nim' => '190411100002',
                'nama' => 'Siti Nurhayati',
                'email' => 'sitinur@student.example.ac.id',
                'prodi' => 'Ekonomi',
                'kamar_id' => $kamars->random()->id,
                'tanggal_lahir' => Carbon::parse('2001-02-28'),
                'tempat_lahir' => 'Surabaya',
                'asal' => 'Surabaya',
                'jenis_kelamin' => 'Perempuan',
                'golongan_ukt' => '2',
                'status' => 'Aktif Tinggal',
                'password' => Hash::make('password123'),
                'user_id' => $users->random()->id,
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nim' => '190411100003',
                'nama' => 'Budi Santoso',
                'email' => 'budisantoso@student.example.ac.id',
                'prodi' => 'Ilmu Komunikasi',
                'kamar_id' => $kamars->random()->id,
                'tanggal_lahir' => Carbon::parse('1999-11-10'),
                'tempat_lahir' => 'Bandung',
                'asal' => 'Bandung',
                'jenis_kelamin' => 'Laki-laki',
                'golongan_ukt' => '4',
                'status' => 'Aktif Tinggal',
                'password' => Hash::make('password123'),
                'user_id' => $users->random()->id,
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nim' => '190411100004',
                'nama' => 'Dewi Lestari',
                'email' => 'dewilestari@student.example.ac.id',
                'prodi' => 'Manajemen',
                'kamar_id' => $kamars->random()->id,
                'tanggal_lahir' => Carbon::parse('2000-07-22'),
                'tempat_lahir' => 'Medan',
                'asal' => 'Medan',
                'jenis_kelamin' => 'Perempuan',
                'golongan_ukt' => '5',
                'status' => 'Aktif Tinggal',
                'password' => Hash::make('password123'),
                'user_id' => $users->random()->id,
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nim' => '190411100005',
                'nama' => 'Rudi Hermawan',
                'email' => 'rudihermawan@student.example.ac.id',
                'prodi' => 'Teknik Sipil',
                'kamar_id' => $kamars->random()->id,
                'tanggal_lahir' => Carbon::parse('2001-03-14'),
                'tempat_lahir' => 'Makassar',
                'asal' => 'Makassar',
                'jenis_kelamin' => 'Laki-laki',
                'golongan_ukt' => '3',
                'status' => 'Tidak Aktif',
                'password' => Hash::make('password123'),
                'user_id' => $users->random()->id,
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('mahasiswa')->insert($mahasiswaData);
    }
}