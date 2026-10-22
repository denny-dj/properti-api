<?php

namespace Tests\Feature\Api\V1;

use App\Enums\BookingStatus;
use App\Enums\PartnerType;
use App\Enums\PropertyStatus;
use App\Enums\TransactionStatus;
use App\Models\ArchitectBooking;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private const SERVER_KEY = 'SB-Mid-server-test';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        config(['payments.midtrans.server_key' => self::SERVER_KEY]);

        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-123',
            ], 201),
        ]);
    }

    private function webhook(string $orderId, string $grossAmount, string $txStatus, array $extra = []): array
    {
        return array_merge([
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'transaction_status' => $txStatus,
            'payment_type' => 'bank_transfer',
            'va_numbers' => [['bank' => 'bca', 'va_number' => '1234567890']],
            'transaction_id' => 'mid-trx-1',
            'signature_key' => hash('sha512', $orderId.'200'.$grossAmount.self::SERVER_KEY),
        ], $extra);
    }

    public function test_property_booking_fee_checkout_webhook_reserves_property(): void
    {
        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $property = Property::factory()->published()->create(['partner_id' => $seller->id, 'price' => 1_000_000_000]);
        $buyer = User::factory()->buyer()->create();

        // Checkout -> tagihan 1% dari harga (Rp 10.000.000) + opsi cicilan kartu kredit dikirim ke Midtrans
        Sanctum::actingAs($buyer);
        $response = $this->postJson('/api/v1/payments/property-booking-fee', ['property_id' => $property->id])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.checkout.snap_token', 'snap-token-123');

        $this->assertEquals(10_000_000, $response->json('data.amount'));
        $code = $response->json('data.code');

        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'snap/v1/transactions')
            && isset($r->data()['credit_card']['installment'])
            && $r->data()['transaction_details']['order_id'] === $code);

        // Pembeli lain tidak bisa checkout properti yang sedang ditahan
        Sanctum::actingAs(User::factory()->buyer()->create());
        $this->postJson('/api/v1/payments/property-booking-fee', ['property_id' => $property->id])
            ->assertStatus(409);

        // Pembeli yang sama mendapat tagihan yang sama (tidak membuat ganda)
        Sanctum::actingAs($buyer);
        $this->postJson('/api/v1/payments/property-booking-fee', ['property_id' => $property->id])
            ->assertOk()->assertJsonPath('data.code', $code);

        // Webhook dengan signature palsu ditolak
        $bad = $this->webhook($code, '10000000.00', 'settlement');
        $bad['signature_key'] = 'palsu';
        $this->postJson('/api/v1/payments/notification', $bad)->assertForbidden();

        // Nominal tidak cocok ditolak
        $this->postJson('/api/v1/payments/notification', $this->webhook($code, '9000000.00', 'settlement'))
            ->assertStatus(422);

        // Webhook settlement sah -> lunas, properti reserved
        $paid = $this->webhook($code, '10000000.00', 'settlement');
        $this->postJson('/api/v1/payments/notification', $paid)->assertOk();

        $transaction = Transaction::where('code', $code)->first();
        $this->assertSame(TransactionStatus::Paid, $transaction->status);
        $this->assertSame('bca', $transaction->payment_channel);
        $this->assertNotNull($transaction->paid_at);
        $this->assertFalse($transaction->needs_review);
        $this->assertSame(PropertyStatus::Reserved, $property->fresh()->status);

        // Webhook yang dikirim ulang bersifat idempotent
        $this->postJson('/api/v1/payments/notification', $paid)->assertOk();
        $this->assertSame(TransactionStatus::Paid, $transaction->fresh()->status);

        // Properti reserved hilang dari listing publik & tidak bisa dibayar lagi
        $this->getJson('/api/v1/properties')->assertJsonPath('meta.total', 0);
        Sanctum::actingAs(User::factory()->buyer()->create());
        $this->postJson('/api/v1/payments/property-booking-fee', ['property_id' => $property->id])
            ->assertStatus(422);

        // Seller menandai terjual
        Sanctum::actingAs($seller);
        $this->postJson("/api/v1/partner/properties/{$property->id}/mark-sold")
            ->assertOk()->assertJsonPath('data.status', 'sold');
    }

    public function test_unknown_order_is_ignored_and_expired_transaction_can_be_marked(): void
    {
        $unknown = $this->webhook('TRX-TIDAK-ADA', '100000.00', 'settlement');
        $this->postJson('/api/v1/payments/notification', $unknown)->assertOk()->assertJsonPath('message', 'Ignored');

        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $property = Property::factory()->published()->create(['partner_id' => $seller->id, 'price' => 500_000_000]);
        $buyer = User::factory()->buyer()->create();

        Sanctum::actingAs($buyer);
        $code = $this->postJson('/api/v1/payments/property-booking-fee', ['property_id' => $property->id])
            ->assertCreated()->json('data.code');

        // Lewat batas waktu -> command menandai expired, properti bebas lagi untuk pembeli lain
        Transaction::where('code', $code)->update(['expires_at' => now()->subMinute()]);
        $this->artisan('payments:expire-stale')->assertSuccessful();
        $this->assertSame(TransactionStatus::Expired, Transaction::where('code', $code)->first()->status);

        Sanctum::actingAs(User::factory()->buyer()->create());
        $this->postJson('/api/v1/payments/property-booking-fee', ['property_id' => $property->id])->assertCreated();
    }

    public function test_architect_booking_payment_requires_confirmed_booking(): void
    {
        $client = User::factory()->buyer()->create();
        $pending = ArchitectBooking::factory()->create(['client_id' => $client->id, 'status' => BookingStatus::Pending]);
        $confirmed = ArchitectBooking::factory()->create([
            'client_id' => $client->id, 'status' => BookingStatus::Confirmed, 'price' => 750000,
        ]);

        Sanctum::actingAs($client);

        $this->postJson('/api/v1/payments/architect-bookings', ['architect_booking_id' => $pending->id])
            ->assertStatus(422);

        $code = $this->postJson('/api/v1/payments/architect-bookings', ['architect_booking_id' => $confirmed->id])
            ->assertCreated()
            ->assertJsonPath('data.type', 'architect_booking')
            ->json('data.code');

        // Lunas via e-wallet
        $this->postJson('/api/v1/payments/notification', $this->webhook($code, '750000.00', 'settlement', [
            'payment_type' => 'gopay', 'va_numbers' => null,
        ]))->assertOk();

        $this->assertSame(TransactionStatus::Paid, Transaction::where('code', $code)->first()->status);

        // Sudah dibayar -> tidak bisa membuat tagihan lagi
        $this->postJson('/api/v1/payments/architect-bookings', ['architect_booking_id' => $confirmed->id])
            ->assertStatus(422);

        // Pembayar & arsitek bisa melihat transaksi; pihak lain tidak
        $this->getJson("/api/v1/transactions/{$code}")->assertOk()->assertJsonPath('data.status', 'paid');
        Sanctum::actingAs($confirmed->architect);
        $this->getJson("/api/v1/transactions/{$code}")->assertOk();
        $this->getJson('/api/v1/partner/transactions')->assertOk()->assertJsonCount(1, 'data');
        Sanctum::actingAs(User::factory()->buyer()->create());
        $this->getJson("/api/v1/transactions/{$code}")->assertForbidden();
    }

    public function test_admin_can_list_transactions_and_see_summary(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->buyer()->create();
        $booking = ArchitectBooking::factory()->create(['client_id' => $client->id, 'status' => BookingStatus::Confirmed, 'price' => 300000]);

        Sanctum::actingAs($client);
        $code = $this->postJson('/api/v1/payments/architect-bookings', ['architect_booking_id' => $booking->id])->json('data.code');
        $this->postJson('/api/v1/payments/notification', $this->webhook($code, '300000.00', 'settlement'));

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/transactions')->assertOk()->assertJsonPath('meta.total', 1);
        $summary = $this->getJson('/api/v1/admin/transactions/summary')->assertOk();
        $this->assertEquals(300000, $summary->json('data.paid_total_amount'));
        $this->assertSame(1, $summary->json('data.paid_count'));

        // Non-admin tidak punya akses
        Sanctum::actingAs($client);
        $this->getJson('/api/v1/admin/transactions')->assertForbidden();
    }
}
