<?php

namespace Tests\Feature\Web;

use App\Livewire\ConciergeCta;
use App\Livewire\KprCalculator;
use App\Livewire\Pages\PortfolioIndex;
use App\Models\AdBanner;
use App\Models\ArchitectPortfolio;
use App\Models\ConciergeLead;
use App\Models\Property;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConciergeWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        config(['concierge.admin_wa_number' => '6281234567890']);
    }

    public function test_portfolio_list_shows_only_published_and_filters(): void
    {
        ArchitectPortfolio::factory()->published()->create(['title' => 'Interior Cocok Uji', 'category' => 'interior', 'estimated_rab' => 300_000_000]);
        ArchitectPortfolio::factory()->published()->create(['title' => 'Fasad Mahal Uji', 'category' => 'facade', 'estimated_rab' => 2_000_000_000]);
        ArchitectPortfolio::factory()->create(['title' => 'Masih Draft Uji']);

        $this->get('/portofolio')
            ->assertOk()
            ->assertSee('Interior Cocok Uji')
            ->assertSee('Fasad Mahal Uji')
            ->assertDontSee('Masih Draft Uji');

        Livewire::test(PortfolioIndex::class)
            ->set('category', 'interior')
            ->assertSee('Interior Cocok Uji')
            ->assertDontSee('Fasad Mahal Uji')
            ->set('category', '')
            ->set('rab', 'gt1000')
            ->assertSee('Fasad Mahal Uji')
            ->assertDontSee('Interior Cocok Uji');

        Livewire::test(PortfolioIndex::class)
            ->set('ready', '1')
            ->assertDontSee('Interior Cocok Uji'); // tidak ada yang siap bangun
    }

    public function test_portfolio_detail_shows_packages_and_whatsapp_cta(): void
    {
        $portfolio = ArchitectPortfolio::factory()->published()->readyToBuild(5_000_000)->create(['title' => 'Rumah Siap Bangun Uji']);

        $this->get('/portofolio/'.$portfolio->slug)
            ->assertOk()
            ->assertSee('Rumah Siap Bangun Uji')
            ->assertSee('Rp 5.000.000')
            ->assertSee('DED (AutoCAD)')
            ->assertSee('Jalur B')
            ->assertSee('Konsultasi via WhatsApp Admin');

        $draft = ArchitectPortfolio::factory()->create();
        $this->get('/portofolio/'.$draft->slug)->assertNotFound();
    }

    public function test_whatsapp_cta_records_a_lead_and_redirects(): void
    {
        $portfolio = ArchitectPortfolio::factory()->published()->create();

        Livewire::test(ConciergeCta::class, ['source' => 'architect_portfolio', 'referenceId' => $portfolio->id])
            ->set('name', 'Budi Santoso')
            ->set('phone', '0812 3456 7890')
            ->set('budget', '650.000.000')
            ->set('preference', 'milestone')
            ->call('openWhatsApp')
            ->assertHasNoErrors()
            ->assertRedirect();

        $lead = ConciergeLead::firstOrFail();
        $this->assertSame('architect_portfolio', $lead->source->value);
        $this->assertSame($portfolio->id, $lead->reference_id);
        $this->assertSame('6281234567890', $lead->customer_phone);
        $this->assertSame(650_000_000, $lead->estimated_budget);
    }

    public function test_whatsapp_cta_works_for_guests_without_any_input(): void
    {
        $property = Property::factory()->published()->create();

        Livewire::test(ConciergeCta::class, ['source' => 'property_detail', 'referenceId' => $property->id])
            ->call('openWhatsApp')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseCount('concierge_leads', 1);
    }

    public function test_whatsapp_cta_validates_phone_and_reports_missing_admin_number(): void
    {
        $property = Property::factory()->published()->create();

        Livewire::test(ConciergeCta::class, ['source' => 'property_detail', 'referenceId' => $property->id])
            ->set('phone', '12345')
            ->call('openWhatsApp')
            ->assertHasErrors(['phone']);

        config(['concierge.admin_wa_number' => null]);

        Livewire::test(ConciergeCta::class, ['source' => 'property_detail', 'referenceId' => $property->id])
            ->call('openWhatsApp')
            ->assertHasErrors(['cta']);

        $this->assertDatabaseCount('concierge_leads', 0);
    }

    public function test_property_detail_has_cta_and_legacy_buttons_follow_the_switch(): void
    {
        $property = Property::factory()->published()->create();

        config(['concierge.legacy_ui' => true]);
        $this->get('/properti/'.$property->slug)
            ->assertOk()
            ->assertSee('Konsultasi via WhatsApp Admin')
            ->assertSee('Masuk untuk chat');

        config(['concierge.legacy_ui' => false]);
        $this->get('/properti/'.$property->slug)
            ->assertOk()
            ->assertSee('Konsultasi via WhatsApp Admin')
            ->assertDontSee('Masuk untuk chat');
    }

    public function test_kpr_interest_form_records_a_kpr_lead(): void
    {
        $property = Property::factory()->published()->create(['price' => 850_000_000]);

        $component = Livewire::test(KprCalculator::class, ['price' => 850_000_000.0, 'propertyId' => $property->id])
            ->assertSee('Ajukan KPR via bank rekanan')
            ->set('showInterest', true)
            ->set('leadName', 'Siti Rahma')
            ->set('leadPhone', '081298765432')
            ->set('monthlyIncome', '25.000.000');

        // tanpa persetujuan data ke bank -> ditolak
        $component->call('submitInterest')->assertHasErrors(['consent']);
        $this->assertDatabaseCount('concierge_leads', 0);

        $component->set('consent', true)
            ->call('submitInterest')
            ->assertHasNoErrors()
            ->assertSee('Pengajuan tercatat');

        $lead = ConciergeLead::firstOrFail();
        $this->assertSame('kpr_calculator', $lead->source->value);
        $this->assertSame('kpr', $lead->payment_preference->value);
        $this->assertSame($property->id, $lead->reference_id);
        $this->assertSame(680_000_000, $lead->estimated_budget); // 850 jt dikurangi DP 20%
        $this->assertStringContainsString('Penghasilan/bulan: Rp 25.000.000', $lead->notes);
    }

    public function test_how_it_works_page_lists_the_six_steps(): void
    {
        $this->get('/cara-kerja')
            ->assertOk()
            ->assertSee('Registrasi')
            ->assertSee('Kunjungan Pertama')
            ->assertSee('Konsultasi Online')
            ->assertSee('Beli Desain Jadi Siap Bangun')
            ->assertSee('Produksi')
            ->assertSee('Instalasi')
            ->assertSee('Serah Terima & Siap Huni');
    }

    public function test_home_shows_portfolios_banners_and_new_menu(): void
    {
        ArchitectPortfolio::factory()->published()->create(['title' => 'Desain Beranda Uji']);
        $banner = AdBanner::factory()->create(['title' => 'Promo Hero Uji', 'placement' => 'top_hero']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Desain Beranda Uji')
            ->assertSee('Promo Hero Uji')
            ->assertSee('Cara Kerja')
            ->assertSee('Portofolio')
            ->assertSee('Arsitek (konsultasi)');

        $this->assertSame(1, $banner->fresh()->impression_count);

        config(['concierge.legacy_ui' => false]);
        $this->get('/')->assertOk()->assertDontSee('Arsitek (konsultasi)');
    }

    public function test_banner_click_is_counted_and_redirects(): void
    {
        $banner = AdBanner::factory()->create(['target_url' => 'https://example.com/promo']);
        $inactive = AdBanner::factory()->create(['is_active' => false]);

        $this->get('/promo/'.$banner->id)->assertRedirect('https://example.com/promo');
        $this->assertSame(1, $banner->fresh()->click_count);

        $this->get('/promo/'.$inactive->id)->assertNotFound();
        $this->assertSame(0, $inactive->fresh()->click_count);
    }
}
