<?php

namespace Tests\Feature\Api\V1;

use App\Enums\PartnerType;
use App\Models\PartnerProfile;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_buyer_can_register_login_and_is_protected_from_guests(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
        ])->assertCreated()
            ->assertJsonPath('data.user.roles.0', 'user')
            ->assertJsonStructure(['data' => ['token']]);

        $this->postJson('/api/v1/auth/login', ['email' => 'budi@example.com', 'password' => 'Rahasia123'])
            ->assertOk()
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'email']]]);

        $this->postJson('/api/v1/auth/login', ['email' => 'budi@example.com', 'password' => 'salah'])
            ->assertUnauthorized();
    }

    public function test_partner_listing_moderation_workflow(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $seller = User::factory()->partner(PartnerType::Seller, false)->create();
        $profileId = PartnerProfile::where('user_id', $seller->id)->value('id');

        $payload = [
            'title' => 'Rumah Minimalis Depok',
            'description' => 'Rumah nyaman dekat stasiun.',
            'property_type' => 'house',
            'listing_type' => 'sale',
            'price' => 850000000,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'floors' => 2,
            'land_area' => 90,
            'building_area' => 72,
            'certificate' => 'SHM',
            'address' => 'Jl. Melati No. 5',
            'city' => 'Depok',
            'province' => 'Jawa Barat',
            'latitude' => -6.4025,
            'longitude' => 106.7942,
        ];

        // Partner belum diverifikasi -> ditolak
        Sanctum::actingAs($seller);
        $this->postJson('/api/v1/partner/properties', $payload)->assertForbidden();

        // Admin memverifikasi partner
        Sanctum::actingAs($admin);
        $this->postJson("/api/v1/admin/partners/{$profileId}/approve")->assertOk();

        // Seller membuat draft, unggah foto, lalu mengajukan
        Sanctum::actingAs($seller->fresh());
        $id = $this->postJson('/api/v1/partner/properties', $payload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->json('data.id');

        $this->postJson("/api/v1/partner/properties/{$id}/submit")->assertStatus(422); // belum ada foto

        $this->post(
            "/api/v1/partner/properties/{$id}/images",
            ['images' => [UploadedFile::fake()->image('depan.jpg')]],
            ['Accept' => 'application/json'],
        )->assertCreated();

        $this->postJson("/api/v1/partner/properties/{$id}/submit")
            ->assertOk()
            ->assertJsonPath('data.status', 'pending_review');

        // Belum tayang di listing publik
        $this->getJson('/api/v1/properties')->assertOk()->assertJsonPath('meta.total', 0);

        // Admin menyetujui -> tayang
        Sanctum::actingAs($admin);
        $this->postJson("/api/v1/admin/properties/{$id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'published');

        $this->getJson('/api/v1/properties')->assertOk()->assertJsonPath('meta.total', 1);

        // Seller lain tidak boleh mengubah properti ini
        $other = User::factory()->partner(PartnerType::Seller)->create();
        Sanctum::actingAs($other);
        $this->putJson("/api/v1/partner/properties/{$id}", ['title' => 'Dibajak'])->assertForbidden();
    }

    public function test_public_listing_filters_by_price_and_city(): void
    {
        Property::factory()->published()->create(['price' => 500_000_000, 'city' => 'Depok']);
        Property::factory()->published()->create(['price' => 2_000_000_000, 'city' => 'Bekasi']);
        Property::factory()->create(['price' => 600_000_000, 'city' => 'Depok']); // draft, tidak tayang

        $this->getJson('/api/v1/properties')->assertJsonPath('meta.total', 2);
        $this->getJson('/api/v1/properties?max_price=1000000000')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/properties?city=Bekasi')->assertJsonPath('meta.total', 1);
    }
}
