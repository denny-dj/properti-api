<?php

namespace Tests\Feature\Api\V1;

use App\Enums\PartnerType;
use App\Livewire\Orders\TransactionDetail;
use App\Models\AdBanner;
use App\Models\ArchitectPortfolio;
use App\Models\ConciergeLead;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\EventNotification;
use App\Services\DeliverableService;
use App\Services\PaymentService;
use App\Support\ConciergeFees;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class ConciergeTest extends TestCase
{
    use RefreshDatabase;

    private const SERVER_KEY = 'SB-Mid-server-test';

    private const SNAP_URL = 'https://app.sandbox.midtrans.com/snap/v4/redirection/tok-adm';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        config([
            'concierge.admin_wa_number' => '6281234567890',
            'payments.midtrans.server_key' => self::SERVER_KEY,
        ]);

        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response(['token' => 'tok-adm', 'redirect_url' => self::SNAP_URL], 201),
        ]);
    }

    private function architect(): User
    {
        return User::factory()->partner(PartnerType::Architect)->create();
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /** Menandai transaksi lunas lewat jalur webhook yang sebenarnya (tanpa mengubahnya). */
    private function pay(Transaction $transaction): void
    {
        $gross = number_format((float) $transaction->amount, 2, '.', '');

        app(PaymentService::class)->handleNotification([
            'order_id' => $transaction->code,
            'status_code' => '200',
            'gross_amount' => $gross,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'va_numbers' => [['bank' => 'bca', 'va_number' => '1']],
            'transaction_id' => 'mid-'.$transaction->id,
            'signature_key' => hash('sha512', $transaction->code.'200'.$gross.self::SERVER_KEY),
        ]);
    }

    private function pendingBlueprint(User $buyer, User $partner, ArchitectPortfolio $portfolio): Transaction
    {
        return Transaction::create([
            'code' => 'TRX-CNC-'.strtoupper(Str::random(6)),
            'user_id' => $buyer->id,
            'payee_id' => $partner->id,
            'type' => 'architect_blueprint',
            'portfolio_id' => $portfolio->id,
            'description' => 'Paket desain siap bangun: '.$portfolio->title,
            'amount' => 5_000_000,
            'platform_fee' => 625_000,
            'net_partner_amount' => 4_375_000,
            'status' => 'pending',
            'gateway' => 'midtrans',
            'expires_at' => now()->addDay(),
        ]);
    }

    // ------------------------------------------------------------------ lead & WhatsApp

    public function test_wa_link_records_a_lead_and_builds_the_deep_link(): void
    {
        $property = Property::factory()->published()->create(['title' => 'Rumah Uji Concierge', 'price' => 850_000_000]);

        $payload = [
            'source' => 'property_detail',
            'reference_id' => $property->id,
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'estimated_budget' => 900_000_000,
            'payment_preference' => 'kpr',
        ];

        $response = $this->postJson('/api/v1/concierge/generate-wa-link', $payload)->assertCreated();

        $code = $response->json('data.lead_code');
        $this->assertMatchesRegularExpression('/^LEAD-\d{4}-[A-Z0-9]{4}$/', $code);

        $prefix = 'https://wa.me/6281234567890?text=';
        $url = $response->json('data.wa_url');
        $this->assertStringStartsWith($prefix, $url);

        $text = urldecode(substr($url, strlen($prefix)));
        $this->assertStringContainsString('Rumah Uji Concierge', $text);
        $this->assertStringContainsString('Rp 850.000.000', $text);
        $this->assertStringContainsString($code, $text);

        $this->assertDatabaseHas('concierge_leads', [
            'lead_code' => $code,
            'source' => 'property_detail',
            'reference_id' => $property->id,
            'customer_phone' => '6281234567890',
            'payment_preference' => 'kpr',
            'status' => 'new',
        ]);

        // Permintaan berulang dari orang yang sama tidak menggandakan prospek
        $this->postJson('/api/v1/concierge/generate-wa-link', $payload)
            ->assertOk()->assertJsonPath('data.lead_code', $code);
        $this->assertDatabaseCount('concierge_leads', 1);
    }

    public function test_wa_link_validates_input_and_references(): void
    {
        $draft = Property::factory()->create();

        $this->postJson('/api/v1/concierge/generate-wa-link', ['source' => 'ngawur'])->assertStatus(422);
        $this->postJson('/api/v1/concierge/generate-wa-link', ['source' => 'property_detail'])->assertStatus(422);
        $this->postJson('/api/v1/concierge/generate-wa-link', ['source' => 'property_detail', 'reference_id' => $draft->id])
            ->assertNotFound();

        config(['concierge.admin_wa_number' => null]);
        $published = Property::factory()->published()->create();
        $this->postJson('/api/v1/concierge/generate-wa-link', ['source' => 'property_detail', 'reference_id' => $published->id])
            ->assertStatus(503);
    }

    public function test_kpr_interest_requires_consent_and_is_recorded_as_a_kpr_lead(): void
    {
        $payload = [
            'customer_name' => 'Siti Rahma',
            'customer_phone' => '+6281298765432',
            'loan_amount' => 600_000_000,
            'monthly_income' => 25_000_000,
            'tenor_years' => 20,
        ];

        $this->postJson('/api/v1/leads/kpr-interest', $payload)->assertStatus(422);
        $this->postJson('/api/v1/leads/kpr-interest', $payload + ['consent' => false])->assertStatus(422);

        $this->postJson('/api/v1/leads/kpr-interest', $payload + ['consent' => true])
            ->assertCreated()
            ->assertJsonStructure(['data' => ['lead_code', 'wa_url', 'message']]);

        $lead = ConciergeLead::firstOrFail();
        $this->assertSame('kpr_calculator', $lead->source->value);
        $this->assertSame('kpr', $lead->payment_preference->value);
        $this->assertSame('6281298765432', $lead->customer_phone);
        $this->assertSame(600_000_000, $lead->estimated_budget);
        $this->assertStringContainsString('Penghasilan/bulan: Rp 25.000.000', $lead->notes);
    }

    // ------------------------------------------------------------------ portofolio

    public function test_architect_manages_portfolios_and_public_can_filter_by_rab(): void
    {
        Storage::fake('public');
        $architect = $this->architect();
        Sanctum::actingAs($architect);

        $base = [
            'title' => 'Rumah Japandi 2 Lantai',
            'category' => 'full_house',
            'architectural_style' => 'japandi',
            'estimated_rab' => 600_000_000,
            'area_sqm' => 120,
            'description' => 'Hunian hangat dengan material kayu.',
        ];

        $id = $this->postJson('/api/v1/partner/architect-portfolios', $base)
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.cost_per_sqm', 5_000_000) // dihitung server: RAB / luas
            ->json('data.id');

        // Paket siap bangun wajib berharga & berdaftar berkas; tayang wajib berfoto
        $this->postJson('/api/v1/partner/architect-portfolios', $base + ['is_ready_to_build' => true])->assertStatus(422);
        $this->postJson('/api/v1/partner/architect-portfolios', $base + ['status' => 'published'])->assertStatus(422);

        $this->post('/api/v1/partner/architect-portfolios', $base + [
            'status' => 'published',
            'is_ready_to_build' => 1,
            'blueprint_price' => 5_000_000,
            'blueprint_deliverables' => ['DED (AutoCAD)', '3D (SketchUp)', 'RAB (Excel)'],
            'images' => [UploadedFile::fake()->image('depan.jpg')],
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.blueprint.price', 5_000_000)
            ->assertJsonCount(3, 'data.blueprint.deliverables');
        $this->assertDatabaseCount('portfolio_images', 1);

        // Hanya pemilik yang dapat mengubah/menghapus
        Sanctum::actingAs($this->architect());
        $this->putJson("/api/v1/partner/architect-portfolios/{$id}", ['title' => 'Dibajak'])->assertNotFound();
        $this->deleteJson("/api/v1/partner/architect-portfolios/{$id}")->assertNotFound();

        // Mitra seller tidak punya akses jalur arsitek
        Sanctum::actingAs(User::factory()->partner(PartnerType::Seller)->create());
        $this->postJson('/api/v1/partner/architect-portfolios', $base)->assertForbidden();

        // Pemilik memperbarui lalu menghapus
        Sanctum::actingAs($architect);
        $this->putJson("/api/v1/partner/architect-portfolios/{$id}", ['estimated_rab' => 720_000_000])
            ->assertOk()->assertJsonPath('data.cost_per_sqm', 6_000_000);
        $this->deleteJson("/api/v1/partner/architect-portfolios/{$id}")->assertOk();
        $this->assertSoftDeleted('architect_portfolios', ['id' => $id]);

        // Filter publik
        ArchitectPortfolio::query()->forceDelete();
        $match = ArchitectPortfolio::factory()->published()->readyToBuild()->create([
            'title' => 'Cocok', 'estimated_rab' => 300_000_000, 'category' => 'interior', 'architectural_style' => 'modern',
        ]);
        ArchitectPortfolio::factory()->published()->create([
            'title' => 'Bukan siap bangun', 'estimated_rab' => 300_000_000, 'category' => 'interior', 'architectural_style' => 'modern',
        ]);
        ArchitectPortfolio::factory()->published()->readyToBuild()->create([
            'title' => 'Terlalu mahal', 'estimated_rab' => 800_000_000, 'category' => 'interior', 'architectural_style' => 'modern',
        ]);
        ArchitectPortfolio::factory()->readyToBuild()->create([
            'title' => 'Masih draft', 'estimated_rab' => 300_000_000, 'category' => 'interior', 'architectural_style' => 'modern',
        ]);

        $this->getJson('/api/v1/architect-portfolios?min_rab=200000000&max_rab=500000000&category=interior&style=modern&is_ready_to_build=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Cocok');

        $this->getJson('/api/v1/architect-portfolios')->assertJsonPath('meta.total', 3); // draft tidak tampil
        $this->getJson('/api/v1/architect-portfolios/'.$match->slug)->assertOk()->assertJsonPath('data.title', 'Cocok');
        $this->getJson('/api/v1/architect-portfolios?category=ngawur')->assertStatus(422);
    }

    public function test_portfolio_with_open_transactions_cannot_be_deleted(): void
    {
        $architect = $this->architect();
        $portfolio = ArchitectPortfolio::factory()->published()->readyToBuild()->create(['user_id' => $architect->id]);
        $this->pendingBlueprint(User::factory()->buyer()->create(), $architect, $portfolio);

        Sanctum::actingAs($architect);
        $this->deleteJson("/api/v1/partner/architect-portfolios/{$portfolio->id}")->assertStatus(422);
    }

    // ------------------------------------------------------------------ cockpit admin

    public function test_admin_cockpit_lists_and_updates_leads(): void
    {
        $admin = $this->admin();
        ConciergeLead::factory()->count(2)->create();
        $contacted = ConciergeLead::factory()->create(['status' => 'contacted']);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/concierge/leads?status=contacted')
            ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.lead_code', $contacted->lead_code);

        $this->putJson("/api/v1/admin/concierge/leads/{$contacted->id}/status", ['status' => 'surveying', 'note' => 'Survei Sabtu 10.00'])
            ->assertOk()->assertJsonPath('data.status', 'surveying');

        $fresh = $contacted->fresh();
        $this->assertSame($admin->id, $fresh->handled_by);
        $this->assertStringContainsString('Survei Sabtu 10.00', $fresh->notes);

        $this->putJson("/api/v1/admin/concierge/leads/{$contacted->id}/status", ['status' => 'new'])->assertStatus(422);
        $this->putJson('/api/v1/admin/concierge/leads/99999/status', ['status' => 'lost'])->assertNotFound();

        Sanctum::actingAs(User::factory()->buyer()->create());
        $this->getJson('/api/v1/admin/concierge/leads')->assertForbidden();
    }

    public function test_quick_booking_issues_property_invoice_with_two_percent_management_fee(): void
    {
        $admin = $this->admin();
        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $property = Property::factory()->published()->create([
            'partner_id' => $seller->id, 'price' => 1_000_000_000, 'title' => 'Rumah Uji Invoice',
        ]);
        $buyer = User::factory()->buyer()->create(['email' => 'pembeli@example.com']);
        $lead = ConciergeLead::factory()->create();

        Sanctum::actingAs($admin);

        $payload = [
            'type' => 'property_booking_fee',
            'property_id' => $property->id,
            'buyer_email' => 'pembeli@example.com',
            'lead_code' => $lead->lead_code,
        ];

        $response = $this->postJson('/api/v1/admin/invoices/quick-booking', $payload)
            ->assertCreated()
            ->assertJsonPath('data.type', 'property_booking_fee')
            ->assertJsonPath('data.amount', 10_000_000)            // 1% harga (aturan booking fee bawaan)
            ->assertJsonPath('data.platform_fee', 20_000_000)      // 2% dari HARGA properti
            ->assertJsonPath('data.net_partner_amount', 0)
            ->assertJsonPath('data.payment_url', self::SNAP_URL)
            ->assertJsonPath('data.lead_code', $lead->lead_code)
            ->assertJsonPath('data.fee_basis', 'property_price');

        $this->assertStringContainsString(self::SNAP_URL, $response->json('data.wa_message'));

        $transaction = Transaction::where('code', $response->json('data.code'))->firstOrFail();
        $this->assertSame($lead->id, $transaction->lead_id);
        $this->assertSame($admin->id, $transaction->issued_by);
        $this->assertSame('committed', $lead->fresh()->status->value);

        // Invoice yang sama dipakai ulang; pembeli lain ditolak (properti ditahan)
        $this->postJson('/api/v1/admin/invoices/quick-booking', $payload)->assertOk();
        $this->assertDatabaseCount('transactions', 1);

        User::factory()->buyer()->create(['email' => 'lain@example.com']);
        $this->postJson('/api/v1/admin/invoices/quick-booking', ['buyer_email' => 'lain@example.com'] + $payload)->assertStatus(409);

        // Pembeli belum punya akun
        $this->postJson('/api/v1/admin/invoices/quick-booking', ['buyer_email' => 'belum@daftar.test'] + $payload)->assertStatus(422);

        // Webhook yang sudah ada tetap memesan properti (tanpa perubahan alur)
        $this->pay($transaction);
        $this->assertSame('reserved', $property->fresh()->status->value);
    }

    public function test_quick_booking_computes_architect_commission_within_the_allowed_range(): void
    {
        Sanctum::actingAs($this->admin());
        $buyer = User::factory()->buyer()->create();
        $ready = ArchitectPortfolio::factory()->published()->readyToBuild(5_000_000)->create();

        // Jalur A: harga paket tetap; komisi bawaan 12,5%
        $this->postJson('/api/v1/admin/invoices/quick-booking', [
            'type' => 'architect_blueprint', 'portfolio_id' => $ready->id, 'buyer_id' => $buyer->id,
        ])->assertCreated()
            ->assertJsonPath('data.amount', 5_000_000)
            ->assertJsonPath('data.platform_fee', 625_000)
            ->assertJsonPath('data.net_partner_amount', 4_375_000)
            ->assertJsonPath('data.commission_percent', 12.5);

        // Komisi di luar 10-15% ditolak
        $other = User::factory()->buyer()->create();
        $this->postJson('/api/v1/admin/invoices/quick-booking', [
            'type' => 'architect_blueprint', 'portfolio_id' => $ready->id, 'buyer_id' => $other->id, 'commission_percent' => 20,
        ])->assertStatus(422);

        // Jalur B: tanda jadi custom, nominal wajib
        $custom = ArchitectPortfolio::factory()->published()->create();
        $this->postJson('/api/v1/admin/invoices/quick-booking', [
            'type' => 'architect_custom', 'portfolio_id' => $custom->id, 'buyer_id' => $buyer->id,
        ])->assertStatus(422);

        $this->postJson('/api/v1/admin/invoices/quick-booking', [
            'type' => 'architect_custom', 'portfolio_id' => $custom->id, 'buyer_id' => $buyer->id,
            'amount' => 3_000_000, 'commission_percent' => 10,
        ])->assertCreated()
            ->assertJsonPath('data.platform_fee', 300_000)
            ->assertJsonPath('data.net_partner_amount', 2_700_000);

        // Paket siap bangun hanya untuk portofolio yang menyediakannya
        $this->postJson('/api/v1/admin/invoices/quick-booking', [
            'type' => 'architect_blueprint', 'portfolio_id' => $custom->id, 'buyer_id' => $other->id,
        ])->assertStatus(422);

        Sanctum::actingAs($buyer);
        $this->postJson('/api/v1/admin/invoices/quick-booking', [
            'type' => 'architect_blueprint', 'portfolio_id' => $ready->id, 'buyer_id' => $buyer->id,
        ])->assertForbidden();
    }

    // ------------------------------------------------------------------ serah terima berkas

    public function test_deliverable_handover_releases_partner_funds_only_after_buyer_confirmation(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $partner = $this->architect();
        $buyer = User::factory()->buyer()->create();
        $portfolio = ArchitectPortfolio::factory()->published()->readyToBuild(5_000_000)->create(['user_id' => $partner->id]);

        Sanctum::actingAs($admin);
        $code = $this->postJson('/api/v1/admin/invoices/quick-booking', [
            'type' => 'architect_blueprint', 'portfolio_id' => $portfolio->id, 'buyer_id' => $buyer->id,
        ])->assertCreated()->json('data.code');
        $transaction = Transaction::where('code', $code)->firstOrFail();

        $link = 'https://drive.google.com/drive/folders/1AbCdEfGhIjKlMnOp?usp=sharing';

        // Belum dibayar: berkas belum boleh dikirim
        Sanctum::actingAs($partner);
        $this->postJson("/api/v1/partner/transactions/{$code}/submit-deliverable", ['deliverable_link' => $link])->assertStatus(422);

        $this->pay($transaction);

        Notification::assertSentTo($partner, EventNotification::class, fn ($n) => str_contains($n->subject, 'kirim tautan berkas'));

        // Pembeli belum bisa mengonfirmasi sebelum ada tautan
        Sanctum::actingAs($buyer);
        $this->postJson("/api/v1/transactions/{$code}/confirm-deliverable")->assertStatus(422);

        // Mitra: host tidak diizinkan, mitra lain, lalu sukses
        Sanctum::actingAs($partner);
        $this->postJson("/api/v1/partner/transactions/{$code}/submit-deliverable", ['deliverable_link' => 'https://evil.example.com/x'])->assertStatus(422);
        $this->postJson("/api/v1/partner/transactions/{$code}/submit-deliverable", ['deliverable_link' => 'http://drive.google.com/x'])->assertStatus(422);

        Sanctum::actingAs($this->architect());
        $this->postJson("/api/v1/partner/transactions/{$code}/submit-deliverable", ['deliverable_link' => $link])->assertForbidden();

        Sanctum::actingAs($partner);
        $this->postJson("/api/v1/partner/transactions/{$code}/submit-deliverable", ['deliverable_link' => $link])
            ->assertOk()
            ->assertJsonPath('data.deliverable.link', $link)
            ->assertJsonPath('data.deliverable.confirmed_at', null);

        Notification::assertSentTo($buyer, EventNotification::class, fn ($n) => str_contains($n->subject, 'Berkas desain Anda siap diperiksa'));

        // Saldo: dana masih tertahan
        $this->getJson('/api/v1/partner/balance')
            ->assertOk()->assertJsonPath('data.released', 0)->assertJsonPath('data.pending_release', 4375000);

        // Pihak lain tidak boleh mengonfirmasi; pembeli melihat tautan tetapi bukan komisi
        Sanctum::actingAs(User::factory()->buyer()->create());
        $this->postJson("/api/v1/transactions/{$code}/confirm-deliverable")->assertForbidden();

        Sanctum::actingAs($buyer);
        $this->getJson("/api/v1/transactions/{$code}")
            ->assertOk()
            ->assertJsonPath('data.deliverable.link', $link)
            ->assertJsonMissingPath('data.platform_fee')
            ->assertJsonMissingPath('data.net_partner_amount');

        $this->postJson("/api/v1/transactions/{$code}/confirm-deliverable")
            ->assertOk()
            ->assertJsonPath('data.status', 'paid');
        $this->assertNotNull($transaction->fresh()->client_confirmed_at);

        // Konfirmasi hanya sekali; mitra tidak bisa mengganti tautan lagi
        $this->postJson("/api/v1/transactions/{$code}/confirm-deliverable")->assertStatus(422);
        Sanctum::actingAs($partner);
        $this->postJson("/api/v1/partner/transactions/{$code}/submit-deliverable", ['deliverable_link' => $link])->assertStatus(422);

        // Dana dilepas ke saldo mitra
        $balance = $this->getJson('/api/v1/partner/balance')->assertOk();
        $this->assertEquals(4_375_000, $balance->json('data.released'));
        $this->assertEquals(0, $balance->json('data.pending_release'));

        Notification::assertSentTo($partner, EventNotification::class, fn ($n) => str_contains($n->subject, 'dana dilepas'));

        // Admin melihat komisi
        Sanctum::actingAs($admin);
        $this->assertEquals(625_000, $this->getJson("/api/v1/transactions/{$code}")->json('data.platform_fee'));
    }

    public function test_buyer_confirms_the_handover_from_the_web_ui(): void
    {
        $partner = $this->architect();
        $buyer = User::factory()->buyer()->create();
        $portfolio = ArchitectPortfolio::factory()->published()->readyToBuild()->create(['user_id' => $partner->id]);
        $transaction = $this->pendingBlueprint($buyer, $partner, $portfolio);
        $this->pay($transaction);

        $component = Livewire::actingAs($buyer)->test(TransactionDetail::class, ['code' => $transaction->code])
            ->assertSee('Menunggu arsitek mengirim tautan berkas');

        app(DeliverableService::class)->submit($transaction->fresh(), $partner, 'https://drive.google.com/drive/folders/xyz');

        $component->call('$refresh')
            ->assertSee('Konfirmasi Penerimaan Berkas')
            ->call('confirmDeliverable')
            ->assertHasNoErrors();

        $this->assertNotNull($transaction->fresh()->client_confirmed_at);
    }

    // ------------------------------------------------------------------ rekap, konten, preflight

    public function test_revenue_summary_aggregates_platform_fees(): void
    {
        $buyer = User::factory()->buyer()->create();
        $partner = $this->architect();
        $base = ['user_id' => $buyer->id, 'payee_id' => $partner->id, 'gateway' => 'midtrans'];

        Transaction::create($base + [
            'code' => 'TRX-REV-0001', 'type' => 'architect_blueprint', 'description' => 'Blueprint',
            'amount' => 5_000_000, 'platform_fee' => 625_000, 'net_partner_amount' => 4_375_000,
            'status' => 'paid', 'paid_at' => now(), 'client_confirmed_at' => now(),
        ]);
        Transaction::create($base + [
            'code' => 'TRX-REV-0002', 'type' => 'property_booking_fee', 'description' => 'Booking fee',
            'amount' => 10_000_000, 'platform_fee' => 20_000_000, 'net_partner_amount' => 0,
            'status' => 'paid', 'paid_at' => now(),
        ]);
        Transaction::create($base + [
            'code' => 'TRX-REV-0003', 'type' => 'architect_blueprint', 'description' => 'Belum bayar',
            'amount' => 9_000_000, 'platform_fee' => 900_000, 'net_partner_amount' => 8_100_000, 'status' => 'pending',
        ]);

        Sanctum::actingAs($this->admin());
        $data = $this->getJson('/api/v1/admin/revenue/summary')->assertOk()->json('data');

        $this->assertSame(2, $data['totals']['transactions']);
        $this->assertEquals(15_000_000, $data['totals']['gross']);
        $this->assertEquals(20_625_000, $data['totals']['platform_fee']);
        $this->assertEquals(4_375_000, $data['totals']['net_partner_amount']);
        $this->assertEquals(4_375_000, $data['architect_escrow']['released']);
        $this->assertEquals(0, $data['architect_escrow']['pending_release']);
        $this->assertCount(2, $data['by_type']);

        $empty = $this->getJson('/api/v1/admin/revenue/summary?from=2999-01-01')->assertOk()->json('data');
        $this->assertSame(0, $empty['totals']['transactions']);

        Sanctum::actingAs($buyer);
        $this->getJson('/api/v1/admin/revenue/summary')->assertForbidden();
    }

    public function test_public_content_how_it_works_and_banner_tracking(): void
    {
        $this->getJson('/api/v1/content/how-it-works')
            ->assertOk()
            ->assertJsonCount(6, 'data.steps')
            ->assertJsonPath('data.steps.0.key', 'registration')
            ->assertJsonPath('data.steps.1.options.0.code', 'A')
            ->assertJsonPath('data.steps.5.key', 'handover');

        $live = AdBanner::factory()->create(['title' => 'Aktif']);
        AdBanner::factory()->create(['title' => 'Nonaktif', 'is_active' => false]);
        AdBanner::factory()->create(['title' => 'Kedaluwarsa', 'end_date' => now()->subDay()->toDateString()]);
        AdBanner::factory()->create(['title' => 'Belum mulai', 'start_date' => now()->addDay()->toDateString()]);
        AdBanner::factory()->create(['title' => 'Sorotan', 'placement' => 'architect_spotlight']);

        $this->getJson('/api/v1/content/promotions')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/v1/content/promotions?placement=architect_spotlight')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/content/promotions?placement=ngawur')->assertStatus(422);

        // Hanya panggilan pertama (tanpa filter) memuat banner top_hero; panggilan ber-filter spotlight tidak.
        $this->assertSame(1, $live->fresh()->impression_count);

        $this->postJson("/api/v1/content/promotions/{$live->id}/click")
            ->assertOk()->assertJsonPath('data.target_url', 'https://example.com/promo');
        $this->assertSame(1, $live->fresh()->click_count);

        $inactive = AdBanner::where('title', 'Nonaktif')->firstOrFail();
        $this->postJson("/api/v1/content/promotions/{$inactive->id}/click")->assertNotFound();
        $this->assertSame(0, $inactive->fresh()->click_count);
    }

    public function test_fee_calculator_enforces_the_architect_range(): void
    {
        $this->assertSame(12.5, ConciergeFees::resolveArchitectPercent(null));
        $this->assertSame(10.0, ConciergeFees::resolveArchitectPercent(10.0));
        $this->assertSame(15.0, ConciergeFees::resolveArchitectPercent(15.0));
        $this->assertEquals(17_000_000, ConciergeFees::propertyFee(850_000_000));

        $this->expectException(InvalidArgumentException::class);
        ConciergeFees::resolveArchitectPercent(9.9);
    }

    public function test_preflight_flags_a_missing_admin_whatsapp_number(): void
    {
        config(['concierge.admin_wa_number' => '']);
        Artisan::call('app:preflight');
        $output = Artisan::output();

        $this->assertStringContainsString('WhatsApp admin (concierge)', $output);
        $this->assertStringContainsString('ADMIN_WA_NUMBER', $output);
    }
}
