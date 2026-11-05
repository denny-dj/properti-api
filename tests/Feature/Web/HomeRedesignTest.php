<?php

namespace Tests\Feature\Web;

use App\Enums\PartnerType;
use App\Livewire\Admin\LeadInbox;
use App\Models\AdBanner;
use App\Models\ArchitectPortfolio;
use App\Models\ConciergeLead;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomeRedesignTest extends TestCase
{
    use RefreshDatabase;

    private const WA = 'https://wa.me/6281234567890?text=';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        config(['concierge.admin_wa_number' => '6281234567890']);
    }

    // ------------------------------------------------------------------ hero

    public function test_home_renders_default_hero_and_empty_states_without_any_data(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Concierge Resmi')
            ->assertSee('name="q"', false)
            ->assertSee('name="rab"', false)             // filter budget arsitek (tab Arsitek)
            ->assertSee('Belum ada properti yang tayang.')
            ->assertSee('Portofolio arsitek segera hadir.')
            ->assertDontSee('Sponsored');
    }

    public function test_hero_shows_the_active_ad_with_tracked_cta_and_counts_one_impression(): void
    {
        $banner = AdBanner::factory()->create([
            'title' => 'Cluster Hijau Residence',
            'subtitle' => 'Unit siap huni dengan DP ringan.',
            'price_label' => 'Mulai Rp 850 Jt · Unit terbatas',
            'badge_label' => 'Featured Developer',
            'placement' => 'top_hero',
        ]);
        AdBanner::factory()->create(['title' => 'Strip Developer', 'placement' => 'featured_developer']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Cluster Hijau Residence')
            ->assertSee('Unit siap huni dengan DP ringan.')
            ->assertSee('Mulai Rp 850 Jt · Unit terbatas')
            ->assertSee('Featured Developer')
            ->assertSee('Konsultasi Unit Ini via Admin')
            ->assertSee('Lihat Brosur / Detail Proyek')
            ->assertSee(route('promo.click', $banner->id), false)
            ->assertSee(route('concierge.redirect'), false)
            ->assertSee('value="promotion"', false)
            ->assertSee('/api/v1/content/promotions/'.$banner->id.'/click', false)
            ->assertDontSee('name="rab"', false);        // hero bawaan tidak tampil

        $this->assertSame(1, $banner->fresh()->impression_count);
    }

    public function test_hero_uses_a_default_sponsor_badge_and_works_without_creative_fields(): void
    {
        AdBanner::factory()->create(['title' => 'Promo Tanpa Teks Tambahan', 'placement' => 'top_hero']);

        $this->get('/')->assertOk()->assertSee('Promo Tanpa Teks Tambahan')->assertSee('Sponsored');
    }

    public function test_only_one_hero_banner_is_shown_and_counted_when_several_are_active(): void
    {
        $a = AdBanner::factory()->create(['title' => 'Hero A', 'placement' => 'top_hero']);
        $b = AdBanner::factory()->create(['title' => 'Hero B', 'placement' => 'top_hero']);

        $this->get('/')->assertOk();

        $this->assertSame(1, $a->fresh()->impression_count + $b->fresh()->impression_count);
    }

    // ------------------------------------------------------------------ kartu

    public function test_property_card_shows_specs_kpr_estimate_and_concierge_button(): void
    {
        $partner = User::factory()->partner(PartnerType::Seller)->create();
        $partner->partnerProfile->update(['business_name' => 'PT Griya Kartu Uji']);
        $property = Property::factory()->published()->create([
            'partner_id' => $partner->id, 'title' => 'Rumah Kartu Uji', 'price' => 850_000_000,
            'bedrooms' => 3, 'bathrooms' => 2, 'land_area' => 90, 'building_area' => 60, 'city' => 'Depok',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Rumah Kartu Uji')
            ->assertSee('Rp 850.000.000')
            ->assertSee('Cicilan ±Rp 5,5 Jt/bln*')
            ->assertSee('LT 90 m²')
            ->assertSee('LB 60 m²')
            ->assertSee('3 KT')
            ->assertSee('2 KM')
            ->assertSee('Depok')
            ->assertSee('Verified Partner')
            ->assertSee('PT Griya Kartu Uji')
            ->assertSee('Tanya Admin Unit Ini')
            ->assertSee('value="property_detail"', false)
            ->assertSee('name="reference_id" value="'.$property->id.'"', false);
    }

    public function test_portfolio_card_shows_rab_range_blueprint_flag_and_architect(): void
    {
        $architect = User::factory()->partner(PartnerType::Architect)->create();
        $architect->partnerProfile->update(['business_name' => 'Studio Arsitek Uji']);

        ArchitectPortfolio::factory()->published()->readyToBuild(5_000_000)->create([
            'user_id' => $architect->id, 'title' => 'Rumah Japandi Kartu', 'estimated_rab' => 300_000_000,
            'area_sqm' => 100, 'architectural_style' => 'japandi', 'category' => 'full_house',
        ]);
        ArchitectPortfolio::factory()->published()->create([
            'user_id' => $architect->id, 'title' => 'Interior Custom Kartu', 'estimated_rab' => 100_000_000, 'is_ready_to_build' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Rumah Japandi Kartu')
            ->assertSee('Japandi')
            ->assertSee('Estimasi RAB Fisik')
            ->assertSee('Rp 270 Jt – Rp 330 Jt')
            ->assertSee('Blueprint siap bangun')
            ->assertSee('Paket Gambar Jadi')
            ->assertSee('Rp 5.000.000')
            ->assertSee('Studio Arsitek Uji')
            ->assertSee('Konsultasi via Admin')
            ->assertSee('value="architect_portfolio"', false)
            ->assertSee('Interior Custom Kartu')
            ->assertSee('Custom desain');
    }

    public function test_how_it_works_section_lists_the_six_concierge_steps(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Registrasi Akun')
            ->assertSee('Konsultasi & Kunjungan')
            ->assertSee('Pembayaran Tanda Jadi & Desain')
            ->assertSee('Produksi Workshop Mitra')
            ->assertSee('Instalasi di Lokasi')
            ->assertSee('Serah Terima & Rilis Dana')
            ->assertSee('01')
            ->assertSee('06');
    }

    // ------------------------------------------------------------------ tombol WhatsApp (POST /konsultasi)

    public function test_concierge_button_records_the_lead_and_redirects_to_whatsapp(): void
    {
        $property = Property::factory()->published()->create(['title' => 'Rumah Tombol Uji']);

        $response = $this->post('/konsultasi', ['source' => 'property_detail', 'reference_id' => $property->id]);

        $response->assertRedirect();
        $this->assertStringStartsWith(self::WA, (string) $response->headers->get('Location'));
        $this->assertStringContainsString('Rumah%20Tombol%20Uji', (string) $response->headers->get('Location'));

        $lead = ConciergeLead::firstOrFail();
        $this->assertSame('property_detail', $lead->source->value);
        $this->assertSame($property->id, $lead->reference_id);
    }

    public function test_concierge_button_prefills_the_logged_in_user_and_supports_portfolio_and_promotion(): void
    {
        $user = User::factory()->buyer()->create(['name' => 'Budi Login', 'phone' => '081234567890']);
        $portfolio = ArchitectPortfolio::factory()->published()->create();
        $banner = AdBanner::factory()->create(['title' => 'Promo Konteks Uji']);

        $this->actingAs($user)->post('/konsultasi', ['source' => 'architect_portfolio', 'reference_id' => $portfolio->id])->assertRedirect();
        $this->actingAs($user)->post('/konsultasi', ['source' => 'promotion', 'reference_id' => $banner->id])->assertRedirect();

        $leads = ConciergeLead::orderBy('id')->get();
        $this->assertCount(2, $leads);
        $this->assertSame('Budi Login', $leads[0]->customer_name);
        $this->assertSame('6281234567890', $leads[0]->customer_phone);
        $this->assertSame('promotion', $leads[1]->source->value);
        $this->assertStringContainsString('Promo Konteks Uji', (string) $leads[1]->notes);
    }

    public function test_concierge_button_validates_and_fails_gracefully(): void
    {
        $this->post('/konsultasi', ['source' => 'ngawur'])->assertSessionHasErrors('source');
        $this->post('/konsultasi', ['source' => 'property_detail'])->assertSessionHasErrors('reference_id');

        $draft = Property::factory()->create();
        $this->post('/konsultasi', ['source' => 'property_detail', 'reference_id' => $draft->id])
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Properti tidak ditemukan.');

        $expired = AdBanner::factory()->create(['end_date' => now()->subDay()->toDateString()]);
        $this->post('/konsultasi', ['source' => 'promotion', 'reference_id' => $expired->id])
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Promosi tidak ditemukan atau sudah berakhir.');

        config(['concierge.admin_wa_number' => null]);
        $published = Property::factory()->published()->create();
        $this->post('/konsultasi', ['source' => 'property_detail', 'reference_id' => $published->id])
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Nomor WhatsApp admin belum dikonfigurasi.');

        $this->assertDatabaseCount('concierge_leads', 0);
    }

    public function test_promotion_leads_work_through_the_api_and_show_up_in_the_admin_inbox(): void
    {
        $banner = AdBanner::factory()->create(['title' => 'Promo Admin Uji']);

        $this->postJson('/api/v1/concierge/generate-wa-link', ['source' => 'promotion', 'reference_id' => $banner->id])
            ->assertCreated();
        $this->postJson('/api/v1/concierge/generate-wa-link', ['source' => 'promotion'])->assertStatus(422);

        Livewire::actingAs(User::factory()->admin()->create())->test(LeadInbox::class)
            ->assertSee('Banner promo')
            ->assertSee('Promo Admin Uji');
    }
}
