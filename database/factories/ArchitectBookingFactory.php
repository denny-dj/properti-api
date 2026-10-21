<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\MeetingType;
use App\Models\ArchitectService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArchitectBookingFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->addDays(fake()->numberBetween(2, 20))->setTime(10, 0);

        return [
            'code' => 'BK-'.now()->format('ymd').'-'.strtoupper(Str::random(6)),
            'client_id' => User::factory()->buyer(),
            'architect_service_id' => ArchitectService::factory(),
            'architect_id' => fn (array $attrs) => ArchitectService::find($attrs['architect_service_id'])->architect_id,
            'scheduled_start' => $start,
            'scheduled_end' => $start->copy()->addHour(),
            'meeting_type' => MeetingType::Online,
            'price' => 500000,
            'duration_minutes' => 60,
            'status' => BookingStatus::Pending,
        ];
    }
}
