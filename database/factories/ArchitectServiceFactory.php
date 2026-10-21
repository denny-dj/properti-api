<?php

namespace Database\Factories;

use App\Enums\PartnerType;
use App\Enums\ServiceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArchitectServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'architect_id' => User::factory()->partner(PartnerType::Architect),
            'title' => fake()->randomElement(['Konsultasi Desain Rumah', 'Desain Rumah Minimalis', 'Renovasi & Interior', 'Pengawasan Pembangunan']).' '.fake()->lastName(),
            'description' => fake()->paragraphs(2, true),
            'service_type' => fake()->randomElement(ServiceType::cases()),
            'price' => fake()->numberBetween(5, 100) * 100_000,
            'duration_minutes' => fake()->randomElement([30, 60, 90]),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
