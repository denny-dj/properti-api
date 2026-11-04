<?php

namespace Tests\Feature\Web;

use App\Enums\PartnerType;
use App\Livewire\Partner\DesignOrders;
use App\Livewire\Partner\PortfolioForm;
use App\Livewire\Partner\PortfolioList;
use App\Models\ArchitectPortfolio;
use App\Models\PortfolioImage;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class PartnerConciergeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function architect(): User
    {
        return User::factory()->partner(PartnerType::Architect)->create();
    }

    private function order(User $partner, User $buyer, string $status, array $extra = []): Transaction
    {
        $portfolio = ArchitectPortfolio::factory()->published()->readyToBuild()->create(['user_id' => $partner->id]);

        return Transaction::create($extra + [
            'code' => 'TRX-8B-'.strtoupper(Str::random(6)),
            'user_id' => $buyer->id,
            'payee_id' => $partner->id,
            'type' => 'architect_blueprint',
            'portfolio_id' => $portfolio->id,
            'description' => 'Paket desain siap bangun: '.$portfolio->title,
            'amount' => 5_000_000,
            'platform_fee' => 625_000,
            'net_partner_amount' => 4_375_000,
            'status' => $status,
            'gateway' => 'midtrans',
            'paid_at' => $status === 'paid' ? now() : null,
            'expires_at' => now()->addDay(),
        ]);
    }

    // ------------------------------------------------------------------ akses & menu

    public function test_menu_and_access_for_architect_partners(): void
    {
        $architect = $this->architect();

        $this->actingAs($architect)->get('/mitra')
            ->assertOk()
            ->assertSee('Portofolio saya')
            ->assertSee('Pesanan desain')
            ->assertSee('Layanan saya');

        config(['concierge.legacy_ui' => false]);
        $this->actingAs($architect)->get('/mitra')->assertOk()->assertDontSee('Layanan saya');

        foreach (['/mitra/portofolio', '/mitra/portofolio/baru', '/mitra/pesanan-desain'] as $url) {
            $this->actingAs($architect)->get($url)->assertOk();
        }

        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $this->actingAs($seller)->get('/mitra/portofolio')->assertRedirect(route('partner.dashboard'));
        $this->actingAs($seller)->get('/mitra/pesanan-desain')->assertRedirect(route('partner.dashboard'));

        $this->actingAs(User::factory()->buyer()->create())->get('/mitra/portofolio')->assertForbidden();
    }

    // ------------------------------------------------------------------ formulir portofolio

    public function test_architect_creates_a_published_ready_to_build_portfolio_with_photo(): void
    {
        Storage::fake('public');
        $architect = $this->architect();
        $this->actingAs($architect);

        Livewire::test(PortfolioForm::class)
            ->set('title', 'Rumah Japandi Uji 8b')
            ->set('category', 'full_house')
            ->set('architectural_style', 'japandi')
            ->set('estimated_rab', '600.000.000')
            ->set('area_sqm', '120')
            ->set('description', 'Hunian hangat.')
            ->set('is_ready_to_build', true)
            ->set('blueprint_price', '5.000.000')
            ->set('deliverables', "DED (AutoCAD)\n3D (SketchUp)\n\nRAB (Excel)")
            ->set('status', 'published')
            ->set('photos', [UploadedFile::fake()->image('depan.jpg')])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('partner.portfolios.index'));

        $portfolio = ArchitectPortfolio::firstOrFail();
        $this->assertSame($architect->id, $portfolio->user_id);
        $this->assertSame(5_000_000, $portfolio->cost_per_sqm);
        $this->assertSame(5_000_000, $portfolio->blueprint_price);
        $this->assertSame(['DED (AutoCAD)', '3D (SketchUp)', 'RAB (Excel)'], $portfolio->blueprint_deliverables);
        $this->assertSame('published', $portfolio->status->value);
        $this->assertSame(1, PortfolioImage::where('portfolio_id', $portfolio->id)->where('is_cover', true)->count());

        // tampil di etalase publik
        $this->get('/portofolio/'.$portfolio->slug)->assertOk()->assertSee('Rumah Japandi Uji 8b');
    }

    public function test_form_rules_publishing_needs_a_photo_and_ready_to_build_needs_price(): void
    {
        $this->actingAs($this->architect());

        $base = fn () => Livewire::test(PortfolioForm::class)
            ->set('title', 'Uji Aturan')
            ->set('estimated_rab', '300000000')
            ->set('area_sqm', '100');

        $base()->set('status', 'published')->call('save')->assertHasErrors(['form']);
        $base()->set('is_ready_to_build', true)->call('save')->assertHasErrors(['blueprint_price']);
        $base()->set('is_ready_to_build', true)->set('blueprint_price', '4000000')->call('save')->assertHasErrors(['deliverables']);
        $base()->set('estimated_rab', '500')->call('save')->assertHasErrors(['estimated_rab']);

        $this->assertDatabaseCount('architect_portfolios', 0);

        $base()->call('save')->assertHasNoErrors(); // draft tanpa foto diperbolehkan
        $this->assertDatabaseCount('architect_portfolios', 1);
    }

    public function test_architect_edits_manages_photos_and_cannot_open_others(): void
    {
        Storage::fake('public');
        $architect = $this->architect();
        $portfolio = ArchitectPortfolio::factory()->published()->create([
            'user_id' => $architect->id, 'estimated_rab' => 600_000_000, 'area_sqm' => 120,
        ]);
        $first = PortfolioImage::create(['portfolio_id' => $portfolio->id, 'image_path' => 'portfolios/x/a.jpg', 'is_cover' => true]);
        $second = PortfolioImage::create(['portfolio_id' => $portfolio->id, 'image_path' => 'portfolios/x/b.jpg', 'is_cover' => false]);

        $this->actingAs($architect)->get("/mitra/portofolio/{$portfolio->id}/ubah")->assertOk()->assertSee('Ubah portofolio');
        $this->actingAs($this->architect())->get("/mitra/portofolio/{$portfolio->id}/ubah")->assertNotFound();

        $component = Livewire::actingAs($architect)->test(PortfolioForm::class, ['portfolio' => $portfolio->id])
            ->set('estimated_rab', '720.000.000')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertSame(6_000_000, $portfolio->fresh()->cost_per_sqm);

        $component->call('makeCover', $second->id);
        $this->assertTrue($second->fresh()->is_cover);
        $this->assertFalse($first->fresh()->is_cover);

        $component->call('deleteImage', $first->id);
        $this->assertDatabaseMissing('portfolio_images', ['id' => $first->id]);

        // satu-satunya foto pada portofolio tayang tidak boleh dihapus
        $component->call('deleteImage', $second->id)->assertHasErrors(['photos']);
        $this->assertDatabaseHas('portfolio_images', ['id' => $second->id]);
    }

    // ------------------------------------------------------------------ daftar portofolio

    public function test_list_changes_status_and_protects_portfolios_with_open_orders(): void
    {
        $architect = $this->architect();
        $this->actingAs($architect);

        $withoutPhoto = ArchitectPortfolio::factory()->create(['user_id' => $architect->id]);
        $published = ArchitectPortfolio::factory()->published()->create(['user_id' => $architect->id]);
        $busy = ArchitectPortfolio::factory()->published()->readyToBuild()->create(['user_id' => $architect->id]);

        $buyer = User::factory()->buyer()->create();
        Transaction::create([
            'code' => 'TRX-8B-BUSY', 'user_id' => $buyer->id, 'payee_id' => $architect->id, 'type' => 'architect_blueprint',
            'portfolio_id' => $busy->id, 'description' => 'Sibuk', 'amount' => 5_000_000, 'status' => 'pending',
            'gateway' => 'midtrans', 'expires_at' => now()->addDay(),
        ]);

        Livewire::test(PortfolioList::class)
            ->call('setStatus', $withoutPhoto->id, 'published')->assertHasErrors(['list']) // butuh foto
            ->call('setStatus', $published->id, 'archived')->assertHasNoErrors();

        $this->assertSame('archived', $published->fresh()->status->value);

        Livewire::test(PortfolioList::class)
            ->call('delete', $busy->id)->assertHasErrors(['list'])
            ->call('delete', $published->id)->assertHasNoErrors();

        $this->assertSoftDeleted('architect_portfolios', ['id' => $published->id]);
        $this->assertNotSoftDeleted('architect_portfolios', ['id' => $busy->id]);
    }

    // ------------------------------------------------------------------ pesanan desain

    public function test_design_orders_show_stages_and_submit_the_drive_link(): void
    {
        $partner = $this->architect();
        $buyer = User::factory()->buyer()->create();
        $awaiting = $this->order($partner, $buyer, 'paid');
        $released = $this->order($partner, $buyer, 'paid', [
            'deliverable_link' => 'https://drive.google.com/drive/folders/done',
            'deliverable_submitted_at' => now(), 'client_confirmed_at' => now(),
        ]);
        $unpaid = $this->order($partner, $buyer, 'pending');

        $this->actingAs($partner)->get('/mitra/pesanan-desain')
            ->assertOk()
            ->assertSee('Perlu kirim berkas')
            ->assertSee('Dana dilepas')
            ->assertSee('Menunggu pembayaran');

        $component = Livewire::actingAs($partner)->test(DesignOrders::class)
            ->assertSee($awaiting->code)
            ->set('stage', 'awaiting_link')
            ->assertSee($awaiting->code)
            ->assertDontSee($released->code)
            ->assertDontSee($unpaid->code)
            ->set('stage', '');

        // host tidak diizinkan, tautan kosong, lalu sukses
        $component->set("links.{$awaiting->id}", 'https://evil.example.com/x')->call('submitLink', $awaiting->id)
            ->assertHasErrors(["links.{$awaiting->id}"]);
        $component->set("links.{$awaiting->id}", '')->call('submitLink', $awaiting->id)
            ->assertHasErrors(["links.{$awaiting->id}"]);
        $this->assertNull($awaiting->fresh()->deliverable_link);

        $link = 'https://drive.google.com/drive/folders/1AbCdEf';
        $component->set("links.{$awaiting->id}", $link)->call('submitLink', $awaiting->id)->assertHasNoErrors();

        $fresh = $awaiting->fresh();
        $this->assertSame($link, $fresh->deliverable_link);
        $this->assertNotNull($fresh->deliverable_submitted_at);
        $this->assertNull($fresh->client_confirmed_at);

        // sudah dikonfirmasi pembeli: tautan tidak bisa diganti
        $component->set("links.{$released->id}", $link)->call('submitLink', $released->id)
            ->assertHasErrors(["links.{$released->id}"]);
        $this->assertSame('https://drive.google.com/drive/folders/done', $released->fresh()->deliverable_link);
    }

    public function test_design_orders_are_private_to_the_receiving_partner(): void
    {
        $owner = $this->architect();
        $other = $this->architect();
        $buyer = User::factory()->buyer()->create();
        $order = $this->order($owner, $buyer, 'paid');

        Livewire::actingAs($other)->test(DesignOrders::class)
            ->assertDontSee($order->code)
            ->set("links.{$order->id}", 'https://drive.google.com/drive/folders/curi')
            ->call('submitLink', $order->id)
            ->assertHasErrors(['action']);

        $this->assertNull($order->fresh()->deliverable_link);
    }

    public function test_design_orders_and_dashboard_show_balance_numbers(): void
    {
        $partner = $this->architect();
        $buyer = User::factory()->buyer()->create();
        $this->order($partner, $buyer, 'paid'); // 4.375.000 menunggu dilepas, perlu kirim berkas
        $this->order($partner, $buyer, 'paid', [
            'deliverable_link' => 'https://drive.google.com/drive/folders/ok',
            'deliverable_submitted_at' => now(), 'client_confirmed_at' => now(),
            'net_partner_amount' => 2_700_000,
        ]);

        $this->actingAs($partner)->get('/mitra/pesanan-desain')
            ->assertOk()
            ->assertSee('Rp 4.375.000')
            ->assertSee('Rp 2.700.000');

        $this->actingAs($partner)->get('/mitra')
            ->assertOk()
            ->assertSee('serah terima')
            ->assertSee('Perlu kirim berkas')
            ->assertSee('Siap dicairkan');
    }
}
