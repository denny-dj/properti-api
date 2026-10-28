<?php

namespace Tests\Feature\Web;

use App\Enums\BookingStatus;
use App\Enums\PartnerType;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Pages\PropertyIndex;
use App\Livewire\PropertyActions;
use App\Models\ArchitectBooking;
use App\Models\PartnerProfile;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\EventNotification;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Services\BookingService;
use App\Services\PartnerService;
use App\Services\PaymentService;
use App\Services\PropertyService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class Phase7aTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    // ---------------- Lupa password ----------------

    public function test_forgot_password_sends_link_without_revealing_whether_the_email_exists(): void
    {
        Notification::fake();
        $user = User::factory()->buyer()->create(['email' => 'a@example.com']);

        Livewire::test(ForgotPassword::class)->set('email', 'a@example.com')->call('sendLink')->assertSet('sent', true);
        Notification::assertSentTo($user, ResetPasswordNotification::class);

        // Email tak terdaftar: respons identik, tidak ada email terkirim
        Livewire::test(ForgotPassword::class)->set('email', 'tidak@ada.com')->call('sendLink')->assertSet('sent', true);
        Notification::assertCount(1);

        $this->get('/lupa-password')->assertOk();
        $this->get('/masuk')->assertOk()->assertSee('Lupa password?');
    }

    public function test_password_can_be_reset_once_with_a_valid_token(): void
    {
        $user = User::factory()->buyer()->unverified()->create(['email' => 'b@example.com']);
        $token = Password::createToken($user);

        Livewire::test(ResetPassword::class, ['token' => $token])
            ->set('email', 'b@example.com')
            ->set('password', 'Baru12345')
            ->set('password_confirmation', 'Baru12345')
            ->call('resetPassword')
            ->assertHasNoErrors()
            ->assertRedirect(route('login'));

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check('Baru12345', $fresh->password));
        $this->assertTrue($fresh->hasVerifiedEmail()); // tautan email membuktikan kepemilikan

        // Token hanya berlaku sekali
        Livewire::test(ResetPassword::class, ['token' => $token])
            ->set('email', 'b@example.com')
            ->set('password', 'Lagi12345')
            ->set('password_confirmation', 'Lagi12345')
            ->call('resetPassword')
            ->assertHasErrors('email');
    }

    // ---------------- Verifikasi email ----------------

    public function test_registration_sends_verification_email_and_link_verifies_the_account(): void
    {
        Notification::fake();

        Livewire::test(Register::class)
            ->set('name', 'Siti')
            ->set('email', 'siti@example.com')
            ->set('phone', '081234567891')
            ->set('password', 'Rahasia123')
            ->set('password_confirmation', 'Rahasia123')
            ->call('register')
            ->assertHasNoErrors();

        $user = User::where('email', 'siti@example.com')->firstOrFail();
        Notification::assertSentTo($user, VerifyEmailNotification::class);
        $this->assertFalse($user->hasVerifiedEmail());

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id, 'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect(route('home'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // Hash palsu ditolak
        $other = User::factory()->buyer()->unverified()->create();
        $bad = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $other->id, 'hash' => 'salah']);
        $this->actingAs($other)->get($bad)->assertForbidden();
        $this->assertFalse($other->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_buyers_cannot_chat_book_or_pay(): void
    {
        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $property = Property::factory()->published()->create(['partner_id' => $seller->id]);
        $buyer = User::factory()->buyer()->unverified()->create();

        $component = Livewire::actingAs($buyer)->test(PropertyActions::class, ['property' => $property->id]);
        $component->call('chat')->assertHasErrors('action');
        $component->call('payFee')->assertHasErrors('action');

        $this->assertDatabaseCount('conversations', 0);
        $this->assertDatabaseCount('transactions', 0);

        // Aturan yang sama berlaku di API
        Sanctum::actingAs($buyer);
        $this->postJson('/api/v1/conversations', ['property_id' => $property->id])
            ->assertForbidden()
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'Verifikasi email'));
    }

    public function test_unverified_users_see_a_reminder_banner(): void
    {
        $user = User::factory()->buyer()->unverified()->create();

        $this->actingAs($user)->get('/')->assertOk()->assertSee('Email Anda belum diverifikasi');
        $this->actingAs($user)->get('/verifikasi-email')->assertOk()->assertSee($user->email);
    }

    // ---------------- Notifikasi email ----------------

    public function test_partner_and_property_decisions_notify_the_owner(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();

        $partner = User::factory()->partner(PartnerType::Seller, false)->create();
        $profile = PartnerProfile::where('user_id', $partner->id)->firstOrFail();
        app(PartnerService::class)->approve($profile, $admin);

        Notification::assertSentTo($partner, EventNotification::class,
            fn ($n) => str_contains($n->subject, 'diverifikasi'));

        $toApprove = Property::factory()->pendingReview()->create(['partner_id' => $partner->id]);
        $toReject = Property::factory()->pendingReview()->create(['partner_id' => $partner->id]);

        app(PropertyService::class)->approve($toApprove, $admin);
        app(PropertyService::class)->reject($toReject, $admin, 'Foto tidak sesuai');

        Notification::assertSentTo($partner, EventNotification::class, fn ($n) => str_contains($n->subject, 'disetujui'));
        Notification::assertSentTo($partner, EventNotification::class, fn ($n) => str_contains($n->subject, 'ditolak'));
    }

    public function test_booking_lifecycle_notifies_architect_and_client(): void
    {
        Notification::fake();

        $booking = ArchitectBooking::factory()->create();
        $architect = $booking->architect;
        $client = $booking->client;

        Notification::assertSentTo($architect, EventNotification::class,
            fn ($n) => str_contains($n->subject, 'Pesanan konsultasi baru'));

        app(BookingService::class)->confirm($booking);

        Notification::assertSentTo($client, EventNotification::class,
            fn ($n) => str_contains($n->subject, 'dikonfirmasi'));

        app(BookingService::class)->cancel($booking->fresh(), $client, 'Berubah rencana');

        Notification::assertSentTo($architect, EventNotification::class,
            fn ($n) => str_contains($n->subject, 'dibatalkan'));
    }

    public function test_payment_notifies_payer_and_payee_once(): void
    {
        config(['payments.midtrans.server_key' => 'SB-Mid-server-test']);
        Notification::fake();

        $booking = ArchitectBooking::factory()->create(['status' => BookingStatus::Confirmed, 'price' => 500000]);

        $transaction = Transaction::create([
            'code' => 'TRX-NTF-0001',
            'user_id' => $booking->client_id,
            'payee_id' => $booking->architect_id,
            'type' => 'architect_booking',
            'architect_booking_id' => $booking->id,
            'description' => 'Konsultasi uji',
            'amount' => 500000,
            'status' => 'pending',
            'gateway' => 'midtrans',
        ]);

        $payload = [
            'order_id' => 'TRX-NTF-0001',
            'status_code' => '200',
            'gross_amount' => '500000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'gopay',
            'transaction_id' => 'mid-1',
            'signature_key' => hash('sha512', 'TRX-NTF-0001'.'200'.'500000.00'.'SB-Mid-server-test'),
        ];

        app(PaymentService::class)->handleNotification($payload);
        app(PaymentService::class)->handleNotification($payload); // webhook ganda

        Notification::assertSentToTimes($transaction->payer, EventNotification::class, 1);
        Notification::assertSentTo($transaction->payer, EventNotification::class,
            fn ($n) => str_contains($n->subject, 'Pembayaran Anda diterima'));
        Notification::assertSentTo($transaction->payee, EventNotification::class,
            fn ($n) => str_contains($n->subject, 'Pembayaran masuk'));
    }

    // ---------------- Properti mitra yang disuspend ----------------

    public function test_properties_of_suspended_partners_are_hidden_until_reactivated(): void
    {
        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $property = Property::factory()->published()->create([
            'partner_id' => $seller->id, 'title' => 'Rumah Tersembunyi Uji',
        ]);

        $this->get('/properti/'.$property->slug)->assertOk();
        $this->getJson('/api/v1/properties')->assertJsonPath('meta.total', 1);

        $seller->update(['status' => 'suspended']);

        $this->get('/properti/'.$property->slug)->assertNotFound();
        $this->getJson('/api/v1/properties')->assertJsonPath('meta.total', 0);
        Livewire::test(PropertyIndex::class)->assertDontSee('Rumah Tersembunyi Uji');

        $seller->update(['status' => 'active']);

        $this->get('/properti/'.$property->slug)->assertOk();
        $this->getJson('/api/v1/properties')->assertJsonPath('meta.total', 1);
    }
}
