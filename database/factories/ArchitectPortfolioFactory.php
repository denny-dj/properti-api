<?php

namespace Database\Factories;

use App\Enums\ArchitecturalStyle;
use App\Enums\PartnerType;
use App\Enums\PortfolioCategory;
use App\Enums\PortfolioStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArchitectPortfolioFactory extends Factory
{
    public function definition(): array
    {
        $rab = fake()->numberBetween(150, 900) * 1_000_000;
        $area = fake()->numberBetween(36, 250);

        return [
            'user_id' => User::factory()->partner(PartnerType::Architect),
            'title' => 'Desain '.fake()->randomElement(['Rumah', 'Fasad', 'Interior', 'Kitchen Set']).' '.fake()->lastName(),
            'category' => fake()->randomElement(PortfolioCategory::cases()),
            'architectural_style' => fake()->randomElement(ArchitecturalStyle::cases()),
            'estimated_rab' => $rab,
            'area_sqm' => $area,
            'cost_per_sqm' => (int) round($rab / $area),
            'description' => fake()->paragraph(),
            'is_ready_to_build' => false,
            'status' => PortfolioStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => PortfolioStatus::Published]);
    }

    public function readyToBuild(int $price = 5_000_000): static
    {
        return $this->state(fn () => [
            'is_ready_to_build' => true,
            'blueprint_price' => $price,
            'blueprint_deliverables' => ['DED (AutoCAD)', 'Model 3D (SketchUp)', 'RAB (Excel)'],
        ]);
    }
}
