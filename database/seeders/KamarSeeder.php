<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class KamarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user for created_by
        $admin = User::where('role', 'admin')->first();
        
        if (!$admin) {
            throw new \Exception('Please run UserSeeder first to create admin users.');
        }

        // Sample data for kamar
        $kamarData = [];
        
        // Gedung Asrama Itera (5 gedung)
        $gedungNames = ['tb1', 'tb2', 'tb3', 'tb4', 'tb5'];
        $gedungGender = [
            'tb1' => 'Perempuan', // tb1 is for Perempuan
            'tb2' => 'Laki-laki', // tb2 is for Laki-laki
            'tb3' => 'Laki-laki', // tb3 is for Laki-laki
            'tb4' => 'Perempuan', // tb4 is for Perempuan
            'tb5' => 'Perempuan'  // tb5 is for Perempuan
        ];

        // Loop through each gedung (building)
        foreach ($gedungNames as $gedung) {
            for ($lantai = 1; $lantai <= 4; $lantai++) { // Each building has 4 floors
                for ($i = 1; $i <= 25; $i++) { // Each floor has 25 rooms
                    $noKamar = sprintf("%02d", $i);  // Format room number with two digits
                    
                    // Generate the full room number (floor + room number)
                    $noKamarFull = $lantai . $noKamar;

                    // Room properties
                    $status = 'tersedia'; // All rooms are available
                    $kapasitas = 4; // Each room can hold up to 4 people
                    $terisi = 0; // All rooms are initially empty

                    // Add room data to the array
                    $kamarData[] = [
                        'no_kamar' => $noKamarFull,
                        'gedung' => $gedung,
                        'lantai' => $lantai,
                        'status' => $status,
                        'kapasitas' => $kapasitas,
                        'terisi' => $terisi,
                        'keterangan' => null,
                        'created_by' => $admin->id,
                        'updated_by' => $admin->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            }
        }

        // Insert all the room data into the database
        DB::table('kamar')->insert($kamarData);
    }
}
