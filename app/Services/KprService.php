<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use App\Support\KprCalculator;
use InvalidArgumentException;

class KprService
{
    // Asumsi bila pengguna tidak mengisi parameter pada simulasi per-properti.
    public const DEFAULT_DP_PERCENT = 20;
    public const DEFAULT_INTEREST_RATE = 7.5;
    public const DEFAULT_TENOR_YEARS = 20;

    public function __construct(
        private KprCalculator $calculator,
        private PropertyRepositoryInterface $properties,
    ) {
    }

    public function simulate(array $data): array
    {
        return $this->run((float) $data['property_price'], $data);
    }

    public function simulateForProperty(string $slug, array $data): array
    {
        $property = $this->properties->findPublishedBySlug($slug)
            ?? throw new ApiException('Properti tidak ditemukan.', 404);

        if (! isset($data['dp_amount']) && ! isset($data['dp_percent'])) {
            $data['dp_percent'] = self::DEFAULT_DP_PERCENT;
        }
        $data['interest_rate'] ??= self::DEFAULT_INTEREST_RATE;
        $data['tenor_years'] ??= self::DEFAULT_TENOR_YEARS;

        return [
            'property' => [
                'id' => $property->id,
                'slug' => $property->slug,
                'title' => $property->title,
                'price' => (float) $property->price,
            ],
            'simulation' => $this->run((float) $property->price, $data),
        ];
    }

    private function run(float $price, array $data): array
    {
        $downPayment = isset($data['dp_amount'])
            ? (float) $data['dp_amount']
            : $price * ((float) ($data['dp_percent'] ?? 0) / 100);

        $fixedMonths = isset($data['fixed_years']) ? (int) $data['fixed_years'] * 12 : null;
        $floatingRate = isset($data['floating_rate']) ? (float) $data['floating_rate'] : null;

        try {
            return $this->calculator->simulate(
                propertyPrice: $price,
                downPayment: $downPayment,
                annualRate: (float) $data['interest_rate'],
                tenorMonths: (int) $data['tenor_years'] * 12,
                fixedMonths: $fixedMonths,
                floatingRate: $floatingRate,
                withSchedule: (bool) ($data['include_schedule'] ?? false),
            );
        } catch (InvalidArgumentException $e) {
            throw new ApiException($e->getMessage(), 422);
        }
    }
}
