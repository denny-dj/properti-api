<?php

namespace Tests\Feature\Web;

use App\Enums\PartnerType;
use App\Http\Middleware\EnsureLegacyUiEnabled;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LegacyModeTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoles(): void
    {
        $this->seed(RoleSeeder::class);
    }

    public function test_legacy_pages_and_api_stay_available_by_default(): void
    {
        $this->seedRoles();
        config(['concierge.legacy_ui' => true]);

        $this->get('/arsitek')->assertOk();
        $this->getJson('/api/v1/architect-services')->assertOk();

        $buyer = User::factory()->buyer()->create();
        $this->actingAs($buyer)->get('/chat')->assertOk();
        $this->actingAs($buyer)->get('/pesanan')->assertOk();
        $this->actingAs($buyer)->get('/')->assertSee('Pesanan konsultasi');

        $architect = User::factory()->partner(PartnerType::Architect)->create();
        $this->actingAs($architect)->get('/mitra')->assertSee(route('chat.index'));
    }

    public function test_web_routes_redirect_to_concierge_pages_when_legacy_is_off(): void
    {
        $this->seedRoles();
        config(['concierge.legacy_ui' => false]);

        $this->get('/arsitek')->assertRedirect(route('portfolios.index'))
            ->assertSessionHas('status', EnsureLegacyUiEnabled::MESSAGE);
        $this->get('/arsitek/apa-saja')->assertRedirect(route('portfolios.index'));

        $buyer = User::factory()->buyer()->create();
        $this->actingAs($buyer)->get('/chat')->assertRedirect(route('how-it-works'));
        $this->actingAs($buyer)->get('/pesanan')->assertRedirect(route('transactions.index'));
        $this->actingAs($buyer)->get('/transaksi')->assertOk();            // tetap hidup
        $this->actingAs($buyer)->get('/')->assertDontSee('Pesanan konsultasi');

        $architect = User::factory()->partner(PartnerType::Architect)->create();
        foreach (['/mitra/layanan', '/mitra/jadwal', '/mitra/pesanan'] as $url) {
            $this->actingAs($architect)->get($url)->assertRedirect(route('partner.portfolios.index'));
        }
        $this->actingAs($architect)->get('/mitra/portofolio')->assertOk()->assertDontSee(route('chat.index'));
        $this->actingAs($architect)->get('/mitra')->assertOk()->assertDontSee(route('chat.index'));
        $this->actingAs($architect)->get('/mitra/pesanan-desain')->assertOk();

        $this->actingAs(User::factory()->admin()->create())->get('/admin')->assertDontSee('Pesanan konsultasi');
    }

    public function test_api_returns_410_for_legacy_endpoints_but_keeps_the_rest(): void
    {
        $this->seedRoles();
        config(['concierge.legacy_ui' => false]);

        $this->getJson('/api/v1/architect-services')->assertStatus(410)->assertJsonPath('success', false);

        Sanctum::actingAs(User::factory()->buyer()->create());
        $this->postJson('/api/v1/conversations', [])->assertStatus(410);
        $this->getJson('/api/v1/bookings')->assertStatus(410);
        $this->postJson('/api/v1/payments/property-booking-fee', [])->assertStatus(410);
        $this->postJson('/api/v1/payments/architect-bookings', [])->assertStatus(410);
        $this->getJson('/api/v1/transactions')->assertOk();

        Sanctum::actingAs(User::factory()->partner(PartnerType::Architect)->create());
        $this->getJson('/api/v1/partner/bookings')->assertStatus(410);
        $this->getJson('/api/v1/partner/architect-services')->assertStatus(410);
        $this->getJson('/api/v1/partner/availabilities')->assertStatus(410);
        $this->getJson('/api/v1/partner/balance')->assertOk();

        Sanctum::actingAs(User::factory()->admin()->create());
        $this->getJson('/api/v1/admin/bookings')->assertOk();               // arsip tetap terbaca admin
    }

    public function test_preflight_reports_concierge_readiness(): void
    {
        $this->seedRoles();

        config(['concierge.legacy_ui' => true]);
        Artisan::call('app:preflight');
        $out = Artisan::output();
        $this->assertMatchesRegularExpression('/\|\s*OK\s*\|\s*Tabel concierge/', $out);
        $this->assertMatchesRegularExpression('/\|\s*OK\s*\|\s*Izin admin concierge/', $out);
        $this->assertMatchesRegularExpression('/\|\s*PERINGATAN\s*\|\s*Mode concierge/', $out);

        config(['concierge.legacy_ui' => false]);
        Artisan::call('app:preflight');
        $this->assertMatchesRegularExpression('/\|\s*OK\s*\|\s*Mode concierge/', Artisan::output());
    }

    public function test_preflight_fails_when_concierge_permissions_are_not_seeded(): void
    {
        // Database kosong (tanpa RoleSeeder): preflight harus melapor GAGAL, bukan melempar exception.
        Artisan::call('app:preflight');
        $out = Artisan::output();

        $this->assertMatchesRegularExpression('/\|\s*GAGAL\s*\|\s*Peran & izin/', $out);
        $this->assertMatchesRegularExpression('/\|\s*GAGAL\s*\|\s*Izin admin concierge/', $out);
        $this->assertMatchesRegularExpression('/\|\s*GAGAL\s*\|\s*Administrator/', $out);
    }
}
