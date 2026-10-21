<?php

namespace Tests\Feature\Api\V1;

use App\Models\Property;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KprAndMapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Cache::flush();
    }

    public function test_kpr_simulation_endpoint(): void
    {
        $this->postJson('/api/v1/kpr/simulate', [
            'property_price' => 625000000,
            'dp_amount' => 125000000,
            'interest_rate' => 10,
            'tenor_years' => 20,
        ])->assertOk()
            ->assertJsonPath('data.loan_amount', 500000000)
            ->assertJsonPath('data.monthly_payment', 4825108);

        // DP wajib diisi (nominal atau persen)
        $this->postJson('/api/v1/kpr/simulate', [
            'property_price' => 625000000, 'interest_rate' => 10, 'tenor_years' => 20,
        ])->assertStatus(422)->assertJsonValidationErrors(['dp_amount', 'dp_percent'], 'errors');
    }

    public function test_kpr_for_property_uses_listing_price_and_defaults(): void
    {
        $property = Property::factory()->published()->create(['price' => 1_000_000_000]);

        $this->getJson("/api/v1/properties/{$property->slug}/kpr")
            ->assertOk()
            ->assertJsonPath('data.property.slug', $property->slug)
            ->assertJsonPath('data.simulation.loan_amount', 800000000); // default DP 20%
    }

    public function test_markers_respect_bounding_box(): void
    {
        Property::factory()->published()->create(['latitude' => -6.4, 'longitude' => 106.8]);
        Property::factory()->create(['latitude' => -6.4, 'longitude' => 106.8]); // draft: tidak tampil

        $this->getJson('/api/v1/properties/markers?south=-6.5&west=106.7&north=-6.3&east=106.9')
            ->assertOk()->assertJsonCount(1, 'data');

        $this->getJson('/api/v1/properties/markers?south=0&west=0&north=1&east=1')
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_geocode_requires_login_and_normalizes_google_response(): void
    {
        config(['services.google_maps.server_key' => 'test-key']);
        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'status' => 'OK',
                'results' => [[
                    'formatted_address' => 'Jl. Margonda Raya, Depok',
                    'place_id' => 'abc123',
                    'geometry' => ['location' => ['lat' => -6.37, 'lng' => 106.83]],
                    'address_components' => [
                        ['long_name' => 'Kota Depok', 'types' => ['administrative_area_level_2', 'political']],
                        ['long_name' => 'Jawa Barat', 'types' => ['administrative_area_level_1', 'political']],
                    ],
                ]],
            ]),
        ]);

        $url = '/api/v1/maps/geocode?address='.urlencode('Margonda Depok');

        $this->getJson($url)->assertUnauthorized();

        Sanctum::actingAs(User::factory()->buyer()->create());
        $this->getJson($url)
            ->assertOk()
            ->assertJsonPath('data.city', 'Kota Depok')
            ->assertJsonPath('data.province', 'Jawa Barat')
            ->assertJsonPath('data.latitude', -6.37);
    }
}
