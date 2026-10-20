<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Carport', 'Taman', 'Kolam Renang', 'Keamanan 24 Jam', 'CCTV',
            'AC', 'Water Heater', 'Dapur', 'Balkon', 'Akses Tol Dekat',
            'Dekat Stasiun/Halte', 'Smart Home',
        ];

        foreach ($items as $name) {
            Facility::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
    }
}
