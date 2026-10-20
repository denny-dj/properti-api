<?php

namespace Database\Factories;

use App\Enums\ListingType;
use App\Enums\PartnerType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Facility;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(PropertyType::cases());
        $isHouse = $type === PropertyType::House;
        $land = fake()->numberBetween($isHouse ? 60 : 40, $isHouse ? 400 : 150);

        // Area Jabodetabek agar koordinat realistis untuk uji Maps.
        $cities = [
            ['Jakarta Selatan', 'DKI Jakarta', -6.2615, 106.8106],
            ['Tangerang Selatan', 'Banten', -6.2886, 106.7179],
            ['Bekasi', 'Jawa Barat', -6.2383, 106.9756],
            ['Depok', 'Jawa Barat', -6.4025, 106.7942],
        ];
        [$city, $province, $lat, $lng] = fake()->randomElement($cities);

        return [
            'partner_id' => User::factory()->partner(PartnerType::Seller),
            'title' => ($isHouse ? 'Rumah ' : 'Ruko ').fake()->randomElement(['Minimalis', 'Modern', 'Strategis', 'Asri', 'Premium']).' '.fake()->streetName(),
            'description' => fake()->paragraphs(3, true),
            'property_type' => $type,
            'listing_type' => ListingType::Sale,
            'price' => fake()->numberBetween(300, 5000) * 1_000_000,
            'bedrooms' => $isHouse ? fake()->numberBetween(2, 5) : 0,
            'bathrooms' => fake()->numberBetween(1, 4),
            'floors' => $isHouse ? fake()->numberBetween(1, 3) : fake()->numberBetween(2, 4),
            'land_area' => $land,
            'building_area' => (int) ($land * fake()->randomFloat(2, 0.5, 0.9)),
            'certificate' => fake()->randomElement(['SHM', 'HGB']),
            'address' => fake()->streetAddress(),
            'district' => fake()->lastName(),
            'city' => $city,
            'province' => $province,
            'postal_code' => fake()->numerify('1####'),
            'latitude' => $lat + fake()->randomFloat(6, -0.04, 0.04),
            'longitude' => $lng + fake()->randomFloat(6, -0.04, 0.04),
            'status' => PropertyStatus::Draft,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Property $property) {
            $ids = Facility::inRandomOrder()->limit(fake()->numberBetween(3, 6))->pluck('id');
            if ($ids->isNotEmpty()) {
                $property->facilities()->syncWithoutDetaching($ids);
            }
        });
    }

    public function pendingReview(): static
    {
        return $this->state(fn () => ['status' => PropertyStatus::PendingReview]);
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => PropertyStatus::Published,
            'published_at' => now(),
            'verified_at' => now(),
        ]);
    }
}
