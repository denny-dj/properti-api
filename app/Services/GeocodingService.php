<?php

namespace App\Services;

use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Pembungkus Google Geocoding API (server-side key). Hasil sukses di-cache 30 hari. */
class GeocodingService
{
    private const ENDPOINT = 'https://maps.googleapis.com/maps/api/geocode/json';

    public function geocode(string $address): array
    {
        $key = 'geocode:'.md5(mb_strtolower(trim($address)));

        return Cache::remember($key, now()->addDays(30), fn () => $this->request(['address' => $address]));
    }

    public function reverse(float $lat, float $lng): array
    {
        $key = 'revgeocode:'.md5(round($lat, 5).','.round($lng, 5));

        return Cache::remember($key, now()->addDays(30), fn () => $this->request(['latlng' => "{$lat},{$lng}"]));
    }

    private function request(array $params): array
    {
        $apiKey = config('services.google_maps.server_key');

        if (! $apiKey) {
            throw new ApiException('Layanan peta belum dikonfigurasi.', 503);
        }

        $response = Http::timeout(8)
            ->retry(2, 200, throw: false)
            ->get(self::ENDPOINT, array_merge($params, [
                'key' => $apiKey,
                'region' => 'id',
                'language' => 'id',
            ]));

        if ($response->failed()) {
            throw new ApiException('Layanan peta sedang tidak tersedia.', 502);
        }

        $status = $response->json('status');

        if ($status === 'ZERO_RESULTS') {
            throw new ApiException('Alamat atau koordinat tidak ditemukan.', 404);
        }

        if ($status !== 'OK') {
            Log::warning('Google Geocoding error', ['status' => $status, 'message' => $response->json('error_message')]);
            throw new ApiException('Layanan peta sedang tidak tersedia.', 502);
        }

        return $this->normalize($response->json('results.0'));
    }

    private function normalize(array $result): array
    {
        $components = collect($result['address_components'] ?? []);
        $find = fn (string $type) => $components
            ->first(fn (array $c) => in_array($type, $c['types'] ?? [], true))['long_name'] ?? null;

        return [
            'formatted_address' => $result['formatted_address'] ?? null,
            'latitude' => (float) ($result['geometry']['location']['lat'] ?? 0),
            'longitude' => (float) ($result['geometry']['location']['lng'] ?? 0),
            'district' => $find('administrative_area_level_3'),
            'city' => $find('administrative_area_level_2'),
            'province' => $find('administrative_area_level_1'),
            'postal_code' => $find('postal_code'),
            'place_id' => $result['place_id'] ?? null,
        ];
    }
}
