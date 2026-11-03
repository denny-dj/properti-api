<?php

namespace Database\Factories;

use App\Enums\BannerPlacement;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdBannerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Promo '.fake()->company(),
            'placement' => BannerPlacement::TopHero,
            'banner_image' => 'banners/'.fake()->uuid().'.jpg',
            'target_url' => 'https://example.com/promo',
            'is_active' => true,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
        ];
    }
}
