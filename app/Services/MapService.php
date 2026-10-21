<?php

namespace App\Services;

use App\Repositories\Contracts\PropertyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MapService
{
    public function __construct(
        private PropertyRepositoryInterface $properties,
        private GeocodingService $geocoding,
    ) {
    }

    public function markers(array $filters): Collection
    {
        return $this->properties->markers($filters, (int) ($filters['limit'] ?? 200));
    }

    public function geocode(string $address): array
    {
        return $this->geocoding->geocode($address);
    }

    public function reverseGeocode(float $lat, float $lng): array
    {
        return $this->geocoding->reverse($lat, $lng);
    }
}
