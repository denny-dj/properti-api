<?php

namespace Database\Factories;

use App\Enums\PartnerType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArchitectAvailabilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'architect_id' => User::factory()->partner(PartnerType::Architect),
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ];
    }
}
