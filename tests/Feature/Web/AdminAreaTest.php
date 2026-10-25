<?php

namespace Tests\Feature\Web;

use App\Enums\BookingStatus;
use App\Enums\PartnerType;
use App\Enums\PropertyStatus;
use App\Enums\TransactionStatus;
use App\Enums\VerificationStatus;
use App\Livewire\Admin\PartnerVerification;
use App\Livewire\Admin\PropertyModeration;
use App\Livewire\Admin\TransactionMonitor;
use App\Livewire\Admin\UserManager;
use App\Models\ArchitectBooking;
use App\Models\PartnerProfile;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAreaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_only_admins_can_open_the_admin_area(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->buyer()->create())->get('/admin')->assertForbidden();
        $this->actingAs(User::factory()->partner(PartnerType::Seller)->create())->get('/admin')->assertForbidden();

        $admin = User::factory()->admin()->create();

        foreach (['/admin', '/admin/mitra', '/admin/properti', '/admin/pengguna', '/admin/pesanan', '/admin/transaksi'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_dashboard_shows_pending_queues(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->partner(PartnerType::Seller, false)->create();
        Property::factory()->pendingReview()->create();

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('Mitra menunggu verifikasi')
            ->assertSee('Properti menunggu moderasi');
    }

    public function test_admin_approves_and_rejects_partners(): void
    {
        $admin = User::factory()->admin()->create();

        $a = User::factory()->partner(PartnerType::Architect, false)->create();
        $b = User::factory()->partner(PartnerType::Seller, false)->create();
        $profileA = PartnerProfile::where('user_id', $a->id)->value('id');
        $profileB = PartnerProfile::where('user_id', $b->id)->value('id');

        $component = Livewire::actingAs($admin)->test(PartnerVerification::class)
            ->assertSee($a->email)
            ->call('approve', $profileA)
            ->assertHasNoErrors();

        $this->assertSame(VerificationStatus::Approved, PartnerProfile::find($profileA)->verification_status);

        $component->call('startReject', $profileB)
            ->set('reason', 'x')
            ->call('reject')
            ->assertHasErrors('reason');

        $component->set('reason', 'Dokumen tidak lengkap')->call('reject')->assertHasNoErrors();

        $rejected = PartnerProfile::find($profileB);
        $this->assertSame(VerificationStatus::Rejected, $rejected->verification_status);
        $this->assertSame('Dokumen tidak lengkap', $rejected->rejection_reason);
    }

    public function test_admin_moderates_properties(): void
    {
        $admin = User::factory()->admin()->create();
        $toApprove = Property::factory()->pendingReview()->create();
        $toReject = Property::factory()->pendingReview()->create();

        $component = Livewire::actingAs($admin)->test(PropertyModeration::class)
            ->call('approve', $toApprove->id)
            ->assertHasNoErrors();

        $approved = $toApprove->fresh();
        $this->assertSame(PropertyStatus::Published, $approved->status);
        $this->assertNotNull($approved->published_at);

        $component->call('startReject', $toReject->id)
            ->set('reason', 'Foto tidak sesuai')
            ->call('reject')
            ->assertHasNoErrors();

        $rejected = $toReject->fresh();
        $this->assertSame(PropertyStatus::Rejected, $rejected->status);
        $this->assertSame('Foto tidak sesuai', $rejected->rejection_reason);

        // Properti draft tidak bisa disetujui
        $draft = Property::factory()->create();
        $component->call('approve', $draft->id)->assertHasErrors('list');
    }

    public function test_admin_suspends_and_activates_users_but_not_self(): void
    {
        $admin = User::factory()->admin()->create();
        $buyer = User::factory()->buyer()->create();

        $component = Livewire::actingAs($admin)->test(UserManager::class)
            ->call('suspend', $buyer->id)
            ->assertHasNoErrors();
        $this->assertSame('suspended', $buyer->fresh()->status);

        $component->call('activate', $buyer->id)->assertHasNoErrors();
        $this->assertSame('active', $buyer->fresh()->status);

        $component->call('suspend', $admin->id)->assertHasErrors('list');
        $this->assertSame('active', $admin->fresh()->status);
    }

    public function test_admin_syncs_reviews_and_records_refunds(): void
    {
        config(['payments.midtrans.server_key' => 'SB-Mid-server-test']);

        $admin = User::factory()->admin()->create();
        $booking = ArchitectBooking::factory()->create(['status' => BookingStatus::Confirmed]);

        $transaction = Transaction::create([
            'code' => 'TRX-ADM-0001',
            'user_id' => $booking->client_id,
            'payee_id' => $booking->architect_id,
            'type' => 'architect_booking',
            'architect_booking_id' => $booking->id,
            'description' => 'Konsultasi uji',
            'amount' => 500000,
            'status' => 'pending',
            'gateway' => 'midtrans',
            'needs_review' => true,
        ]);

        Http::fake([
            'api.sandbox.midtrans.com/*' => Http::response([
                'status_code' => '200',
                'order_id' => 'TRX-ADM-0001',
                'gross_amount' => '500000.00',
                'transaction_status' => 'settlement',
                'payment_type' => 'gopay',
                'transaction_id' => 'mid-1',
            ], 200),
        ]);

        $component = Livewire::actingAs($admin)->test(TransactionMonitor::class)
            ->assertSee('TRX-ADM-0001');

        // Pending tidak boleh dicatat dikembalikan
        $component->call('startReview', $transaction->id, 'refund')->call('submitReview')->assertHasErrors('list');

        $component->call('sync', $transaction->id)->assertHasNoErrors();
        $this->assertSame(TransactionStatus::Paid, $transaction->fresh()->status);

        $component->call('startReview', $transaction->id, 'review')
            ->set('reviewNote', 'Sudah dicek manual')
            ->call('submitReview')
            ->assertHasNoErrors();

        $reviewed = $transaction->fresh();
        $this->assertFalse($reviewed->needs_review);
        $this->assertSame($admin->id, $reviewed->reviewed_by);
        $this->assertSame('Sudah dicek manual', $reviewed->review_note);

        $component->call('startReview', $transaction->id, 'refund')->call('submitReview')->assertHasNoErrors();
        $this->assertSame(TransactionStatus::Refunded, $transaction->fresh()->status);
    }

    public function test_booking_monitor_lists_all_bookings(): void
    {
        $admin = User::factory()->admin()->create();
        $booking = ArchitectBooking::factory()->create();

        $this->actingAs($admin)->get('/admin/pesanan')->assertOk()->assertSee($booking->code);
    }

    public function test_admin_logs_in_to_the_admin_dashboard(): void
    {
        User::factory()->admin()->create(['email' => 'boss@example.com']);

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'boss@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));
    }
       
    public function test_suspended_users_are_logged_out_on_their_next_web_request(): void
    {
        $buyer = User::factory()->buyer()->create();
        $this->actingAs($buyer)->get('/pesanan')->assertOk();

        $buyer->update(['status' => 'suspended']);

        $this->actingAs($buyer->fresh())->get('/pesanan')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
