<?php

namespace Database\Seeders;

use App\Enums\ServiceType;
use App\Models\ArchitectAvailability;
use App\Models\ArchitectService;
use App\Models\User;
use Illuminate\Database\Seeder;

/** php artisan db:seed --class=ArchitectDemoSeeder  (aman dijalankan berulang) */
class ArchitectDemoSeeder extends Seeder
{
    public function run(): void
    {
        $architect = User::where('email', 'architect@example.com')->first();

        if (! $architect) {
            $this->command?->warn('User architect@example.com belum ada. Jalankan DatabaseSeeder lebih dulu.');

            return;
        }

        if (! ArchitectService::where('architect_id', $architect->id)->exists()) {
            $items = [
                ['Konsultasi Desain Rumah (1 jam)', ServiceType::Consultation, 350000, 60],
                ['Desain Denah & Tampak Rumah Minimalis', ServiceType::Design, 4500000, 90],
                ['Pengawasan Pembangunan (kunjungan lokasi)', ServiceType::Supervision, 1200000, 120],
            ];

            foreach ($items as [$title, $type, $price, $minutes]) {
                ArchitectService::create([
                    'architect_id' => $architect->id,
                    'title' => $title,
                    'description' => 'Layanan demo untuk pengujian booking.',
                    'service_type' => $type,
                    'price' => $price,
                    'duration_minutes' => $minutes,
                    'is_active' => true,
                ]);
            }
        }

        if (! ArchitectAvailability::where('architect_id', $architect->id)->exists()) {
            foreach (range(1, 5) as $day) { // Senin - Jumat
                ArchitectAvailability::create([
                    'architect_id' => $architect->id,
                    'day_of_week' => $day,
                    'start_time' => '09:00',
                    'end_time' => '16:00',
                ]);
            }
        }
    }
}
