<?php

namespace Database\Factories;

use App\Enums\PartnerType;
use App\Enums\VerificationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'partner_type' => PartnerType::Seller,
            'business_name' => fake()->company(),
            'description' => fake()->paragraph(),
            'business_phone' => fake()->numerify('08##########'),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'license_number' => null,
            'portfolio_url' => null,
            'verification_status' => VerificationStatus::Pending,
        ];
    }

    public function seller(): static
    {
        return $this->state(fn () => ['partner_type' => PartnerType::Seller]);
    }

    public function architect(): static
    {
        return $this->state(fn () => [
            'partner_type' => PartnerType::Architect,
            'license_number' => 'IAI-'.fake()->numerify('######'),
            'portfolio_url' => fake()->url(),
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'verification_status' => VerificationStatus::Approved,
            'verified_at' => now(),
        ]);
    }
}
