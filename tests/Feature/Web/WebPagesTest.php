<?php

namespace Tests\Feature\Web;

use App\Livewire\ArchitectSlots;
use App\Livewire\KprCalculator;
use App\Livewire\Pages\ArchitectIndex;
use App\Livewire\Pages\PropertyIndex;
use App\Models\ArchitectAvailability;
use App\Models\ArchitectService;
use App\Models\Property;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WebPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_home_and_property_detail_render_and_hide_unpublished(): void
    {
        $property = Property::factory()->published()->create(['title' => 'Rumah Uji Beranda', 'city' => 'Depok']);

        $this->get('/')->assertOk()->assertSee('Rumah Uji Beranda');

        $this->get('/properti/'.$property->slug)
            ->assertOk()
            ->assertSee('Rumah Uji Beranda')
            ->assertSee('Simulasi KPR');

        $draft = Property::factory()->create();
        $this->get('/properti/'.$draft->slug)->assertNotFound();
    }

    public function test_property_search_filters_by_city(): void
    {
        Property::factory()->published()->create(['title' => 'Rumah Depok Uji', 'city' => 'Depok']);
        Property::factory()->published()->create(['title' => 'Ruko Bekasi Uji', 'city' => 'Bekasi']);

        Livewire::test(PropertyIndex::class)
            ->assertSee('Rumah Depok Uji')
            ->assertSee('Ruko Bekasi Uji')
            ->set('city', 'Bekasi')
            ->assertSee('Ruko Bekasi Uji')
            ->assertDontSee('Rumah Depok Uji');
    }

    public function test_map_and_kpr_pages_render(): void
    {
        $this->get('/peta')->assertOk()->assertSee('Peta properti');
        $this->get('/kpr')->assertOk()->assertSee('Simulasi KPR');
    }

    public function test_kpr_calculator_computes_monthly_payment(): void
    {
        // Pinjaman 500 jt (harga 625 jt, DP 20%), bunga 10%, 20 tahun -> Rp 4.825.108
        Livewire::test(KprCalculator::class, ['price' => 625000000])
            ->set('dpPercent', '20')
            ->set('interest', '10')
            ->set('tenorYears', '20')
            ->assertSee('4.825.108');

        Livewire::test(KprCalculator::class, ['price' => 625000000])
            ->set('dpPercent', '95')
            ->assertSee('Uang muka harus antara 0% dan 90%.');
    }

    public function test_architect_catalog_and_slot_picker(): void
    {
        $service = ArchitectService::factory()->create([
            'title' => 'Konsultasi Denah Uji', 'duration_minutes' => 60,
        ]);
        ArchitectAvailability::factory()->create([
            'architect_id' => $service->architect_id, 'day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '12:00',
        ]);

        $this->get('/arsitek')->assertOk()->assertSee('Konsultasi Denah Uji');
        Livewire::test(ArchitectIndex::class)->assertSee('Konsultasi Denah Uji');
        $this->get('/arsitek/'.$service->slug)->assertOk()->assertSee('Jadwal tersedia');

        $monday = CarbonImmutable::now(config('app.timezone'))->next(CarbonInterface::MONDAY)->toDateString();

        Livewire::test(ArchitectSlots::class, ['slug' => $service->slug])
            ->set('date', $monday)
            ->assertSee('09:00')
            ->assertSee('11:00')
            ->set('date', '2020-01-01')
            ->assertSee('Pilih tanggal antara');
    }
}
