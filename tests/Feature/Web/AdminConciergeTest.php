<?php

namespace Tests\Feature\Web;

use App\Enums\PartnerType;
use App\Livewire\Admin\LeadInbox;
use App\Livewire\Admin\PayoutManager;
use App\Livewire\Admin\QuickInvoice;
use App\Livewire\Admin\RevenueDashboard;
use App\Models\ArchitectPortfolio;
use App\Models\ConciergeLead;
use App\Models\PartnerPayout;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\EventNotification;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class AdminConciergeTest extends TestCase
{
    use RefreshDatabase;

    private const SNAP_URL = 'https://app.sandbox.midtrans.com/snap/v4/redirection/tok-8c';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response(['token' => 'tok-8c', 'redirect_url' => self::SNAP_URL], 201),
        ]);
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function architect(): User
    {
        return User::factory()->partner(PartnerType::Architect)->create();
    }

    /** Transaksi desain lunas; $confirmed menentukan apakah dana sudah dilepas. */
    private function design(User $partner, User $buyer, bool $confirmed, int $net = 4_375_000): Transaction
    {
        $portfolio = ArchitectPortfolio::factory()->published()->readyToBuild()->create(['user_id' => $partner->id]);

        return Transaction::create([
            'code' => 'TRX-8C-'.strtoupper(Str::random(6)),
            'user_id' => $buyer->id,
            'payee_id' => $partner->id,
            'type' => 'architect_blueprint',
            'portfolio_id' => $portfolio->id,
            'description' => 'Paket desain: '.$portfolio->title,
            'amount' => $net + 625_000,
            'platform_fee' => 625_000,
            'net_partner_amount' => $net,
            'status' => 'paid',
            'gateway' => 'midtrans',
            'paid_at' => now(),
            'deliverable_link' => $confirmed ? 'https://drive.google.com/drive/folders/ok' : null,
            'deliverable_submitted_at' => $confirmed ? now() : null,
            'client_confirmed_at' => $confirmed ? now() : null,
        ]);
    }

    // ------------------------------------------------------------------ akses & menu

    public function test_admin_pages_and_menu_are_reachable_only_by_admins(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('Prospek')
            ->assertSee('Invoice cepat')
            ->assertSee('Pendapatan')
            ->assertSee('Pencairan mitra');

        foreach (['/admin/prospek', '/admin/invoice-cepat', '/admin/pendapatan', '/admin/pencairan'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
            $this->actingAs(User::factory()->buyer()->create())->get($url)->assertForbidden();
            $this->actingAs($this->architect())->get($url)->assertForbidden();
        }
    }

    // ------------------------------------------------------------------ prospek

    public function test_lead_inbox_lists_filters_and_updates_leads(): void
    {
        $admin = $this->admin();
        $property = Property::factory()->published()->create(['title' => 'Rumah Rujukan Uji']);
        $portfolio = ArchitectPortfolio::factory()->published()->create(['title' => 'Desain Rujukan Uji']);

        $fresh = ConciergeLead::factory()->create([
            'lead_code' => 'LEAD-2026-AAAA', 'source' => 'property_detail', 'reference_id' => $property->id,
            'customer_name' => 'Budi Baru', 'customer_phone' => '6281234567890', 'status' => 'new',
        ]);
        ConciergeLead::factory()->create([
            'lead_code' => 'LEAD-2026-BBBB', 'source' => 'architect_portfolio', 'reference_id' => $portfolio->id,
            'customer_name' => 'Sari Lama', 'status' => 'contacted',
        ]);

        $component = Livewire::actingAs($admin)->test(LeadInbox::class)
            ->assertSee('LEAD-2026-AAAA')
            ->assertSee('Rumah Rujukan Uji')
            ->assertSee('Desain Rujukan Uji')
            ->assertSee('https://wa.me/6281234567890')
            ->set('status', 'new')
            ->assertSee('Budi Baru')
            ->assertDontSee('Sari Lama')
            ->set('status', '')
            ->set('q', 'Sari')
            ->assertSee('Sari Lama')
            ->assertDontSee('Budi Baru')
            ->set('q', '');

        $component->call('startEdit', $fresh->id, 'new')
            ->set('newStatus', 'new')
            ->call('saveStatus')
            ->assertHasErrors(['newStatus']);

        $component->set('newStatus', 'surveying')
            ->set('newNote', 'Survei Sabtu 10.00')
            ->call('saveStatus')
            ->assertHasNoErrors()
            ->assertSee('Prospek diperbarui');

        $lead = $fresh->fresh();
        $this->assertSame('surveying', $lead->status->value);
        $this->assertSame($admin->id, $lead->handled_by);
        $this->assertStringContainsString('Survei Sabtu 10.00', $lead->notes);
    }

    // ------------------------------------------------------------------ invoice cepat

    public function test_quick_invoice_for_property_returns_payment_link_and_whatsapp_message(): void
    {
        $admin = $this->admin();
        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $property = Property::factory()->published()->create(['partner_id' => $seller->id, 'price' => 1_000_000_000]);
        $buyer = User::factory()->buyer()->create(['email' => 'pembeli8c@example.com', 'phone' => '081234567890']);
        $lead = ConciergeLead::factory()->create();

        $component = Livewire::actingAs($admin)->test(QuickInvoice::class)
            ->set('type', 'property_booking_fee')
            ->set('buyerEmail', 'pembeli8c@example.com')
            ->set('itemId', (string) $property->id)
            ->set('leadCode', $lead->lead_code)
            ->assertSee('Management fee platform')   // pratinjau komisi 2% dari harga
            ->call('submit')
            ->assertHasNoErrors();

        $result = $component->get('result');
        $this->assertSame(self::SNAP_URL, $result['payment_url']);
        $this->assertEquals(10_000_000, $result['amount']);
        $this->assertEquals(20_000_000, $result['platform_fee']);
        $this->assertEquals(0, $result['net_partner_amount']);
        $this->assertStringContainsString(self::SNAP_URL, $result['wa_message']);
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $result['wa_url']);

        $transaction = Transaction::where('code', $result['code'])->firstOrFail();
        $this->assertSame($buyer->id, $transaction->user_id);
        $this->assertSame($admin->id, $transaction->issued_by);
        $this->assertSame('committed', $lead->fresh()->status->value);

        $component->assertSee('Salin pesan')->call('createAnother')->assertSet('result', null);
    }

    public function test_quick_invoice_prefills_from_a_lead_and_applies_the_commission_range(): void
    {
        $admin = $this->admin();
        $portfolio = ArchitectPortfolio::factory()->published()->readyToBuild(5_000_000)->create();
        $buyer = User::factory()->buyer()->create(['phone' => '081234567890']);
        $lead = ConciergeLead::factory()->create([
            'source' => 'architect_portfolio', 'reference_id' => $portfolio->id, 'customer_phone' => '6281234567890',
        ]);

        $component = Livewire::actingAs($admin)->test(QuickInvoice::class, ['lead' => $lead->lead_code])
            ->assertSet('type', 'architect_blueprint')
            ->assertSet('itemId', (string) $portfolio->id)
            ->assertSet('buyerEmail', $buyer->email)
            ->assertSet('leadCode', $lead->lead_code);

        $component->set('commission', '20')->call('submit')->assertHasErrors(['commission']);

        $component->set('commission', '12')->call('submit')->assertHasNoErrors();
        $result = $component->get('result');
        $this->assertEquals(5_000_000, $result['amount']);
        $this->assertEquals(600_000, $result['platform_fee']);
        $this->assertEquals(4_400_000, $result['net_partner_amount']);
    }

    public function test_quick_invoice_validation_for_custom_unknown_item_and_unregistered_buyer(): void
    {
        $admin = $this->admin();
        $portfolio = ArchitectPortfolio::factory()->published()->create();
        $buyer = User::factory()->buyer()->create();

        $base = fn () => Livewire::actingAs($admin)->test(QuickInvoice::class)
            ->set('type', 'architect_custom')
            ->set('buyerEmail', $buyer->email)
            ->set('itemId', (string) $portfolio->id);

        $base()->call('submit')->assertHasErrors(['amount']);                         // tanda jadi wajib
        $base()->set('amount', '3.000.000')->set('itemId', '999999')->call('submit')->assertHasErrors(['itemId']);
        $base()->set('amount', '3000000')->set('buyerEmail', 'belum@daftar.test')->call('submit')->assertHasErrors(['form']);

        $this->assertDatabaseCount('transactions', 0);

        $ok = $base()->set('amount', '3000000')->set('commission', '10')->call('submit')->assertHasNoErrors();
        $this->assertEquals(300_000, $ok->get('result')['platform_fee']);
    }

    // ------------------------------------------------------------------ pendapatan

    public function test_revenue_dashboard_shows_totals_and_respects_the_period(): void
    {
        $partner = $this->architect();
        $buyer = User::factory()->buyer()->create();
        $this->design($partner, $buyer, true);                                                 // 5.000.000 / komisi 625.000
        Transaction::create([
            'code' => 'TRX-8C-PROP', 'user_id' => $buyer->id, 'payee_id' => $partner->id, 'type' => 'property_booking_fee',
            'description' => 'Booking fee', 'amount' => 10_000_000, 'platform_fee' => 20_000_000, 'net_partner_amount' => 0,
            'status' => 'paid', 'gateway' => 'midtrans', 'paid_at' => now(),
        ]);

        Livewire::actingAs($this->admin())->test(RevenueDashboard::class)
            ->assertSee('Rp 15.000.000')      // total lunas
            ->assertSee('Rp 20.625.000')      // komisi platform
            ->assertSee('Booking fee properti')
            ->set('from', '2999-01-01')
            ->assertSee('Belum ada transaksi lunas')
            ->call('preset', 'all')
            ->assertSee('Rp 20.625.000')
            ->call('preset', 'month')
            ->assertSee('Rp 20.625.000');
    }

    // ------------------------------------------------------------------ pencairan

    public function test_payouts_reduce_the_available_balance_and_cannot_exceed_it(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $partner = $this->architect();
        $buyer = User::factory()->buyer()->create();
        $this->design($partner, $buyer, true, 4_375_000);   // dilepas
        $this->design($partner, $buyer, false, 8_100_000);  // masih menunggu konfirmasi

        $component = Livewire::actingAs($admin)->test(PayoutManager::class)
            ->assertSee('Rp 4.375.000')
            ->assertSee('Rp 8.100.000')
            ->call('startPay', $partner->id)
            ->assertSet('amount', '4375000');

        // melebihi saldo siap cair (dana yang belum dikonfirmasi tidak boleh dicairkan)
        $component->set('amount', '5.000.000')->call('record')->assertHasErrors(['amount']);
        $this->assertDatabaseCount('partner_payouts', 0);

        $component->set('amount', '1.000.000')->set('reference', 'BCA-123')->call('record')
            ->assertHasNoErrors()
            ->assertSee('dicatat')
            ->assertSee('Rp 3.375.000');

        $payout = PartnerPayout::firstOrFail();
        $this->assertSame($partner->id, $payout->partner_id);
        $this->assertSame($admin->id, $payout->recorded_by);
        $this->assertSame('BCA-123', $payout->reference);
        $this->assertEquals(1_000_000, $payout->amount);

        Notification::assertSentTo($partner, EventNotification::class, fn ($n) => str_contains($n->subject, 'Pencairan saldo dicatat'));

        // sisa dicairkan, lalu tidak ada saldo lagi
        $component->call('startPay', $partner->id)->assertSet('amount', '3375000')
            ->call('record')->assertHasNoErrors();
        $component->call('startPay', $partner->id)->assertHasErrors(['list']);
        $this->assertDatabaseCount('partner_payouts', 2);
    }

    public function test_partner_sees_the_reduced_balance_in_web_and_api(): void
    {
        $partner = $this->architect();
        $buyer = User::factory()->buyer()->create();
        $this->design($partner, $buyer, true, 4_375_000);

        Livewire::actingAs($this->admin())->test(PayoutManager::class)
            ->call('startPay', $partner->id)
            ->set('amount', '1000000')
            ->call('record')
            ->assertHasNoErrors();

        $this->actingAs($partner)->get('/mitra/pesanan-desain')
            ->assertOk()
            ->assertSee('Rp 3.375.000')
            ->assertSee('Pencairan terakhir');

        Sanctum::actingAs($partner);
        $balance = $this->getJson('/api/v1/partner/balance')->assertOk();
        $this->assertEquals(4_375_000, $balance->json('data.released'));
        $this->assertEquals(1_000_000, $balance->json('data.paid_out'));
        $this->assertEquals(3_375_000, $balance->json('data.available'));
    }

    public function test_admin_dashboard_shows_concierge_cards(): void
    {
        $partner = $this->architect();
        $buyer = User::factory()->buyer()->create();
        $this->design($partner, $buyer, true, 4_375_000);
        ConciergeLead::factory()->count(2)->create(['status' => 'new']);

        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee('Prospek baru')
            ->assertSee('Saldo mitra belum dicairkan')
            ->assertSee('Rp 4.375.000');
    }
}
