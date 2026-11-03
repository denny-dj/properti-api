<?php

namespace Database\Factories;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ConciergeLeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lead_code' => 'LEAD-'.now()->format('Y').'-'.strtoupper(Str::random(4)),
            'source' => LeadSource::PropertyDetail,
            'reference_id' => null,
            'customer_name' => fake()->name(),
            'customer_phone' => '628'.fake()->numerify('#########'),
            'estimated_budget' => fake()->numberBetween(300, 2000) * 1_000_000,
            'status' => LeadStatus::New,
        ];
    }
}
