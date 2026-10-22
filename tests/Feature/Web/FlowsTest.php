<?php

namespace Tests\Feature\Web;

use App\Enums\BookingStatus;
use App\Enums\PartnerType;
use App\Enums\PropertyStatus;
use App\Enums\TransactionStatus;
use App\Events\MessageSent;
use App\Livewire\ArchitectSlots;
use App\Livewire\Chat\Room;
use App\Livewire\Orders\BookingDetail;
use App\Livewire\PropertyActions;
use App\Models\ArchitectAvailability;
use App\Models\ArchitectBooking;
use App\Models\ArchitectService;
use App\Models\Conversation;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class FlowsTest extends TestCase
{
    use RefreshDatabase;

    private const SNAP_URL = 'https://app.sandbox.midtrans.com/snap/v4/redirection/tok-123';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        config(['payments.midtrans.server_key' => 'SB-Mid-server-test']);
        Event::fake([MessageSent::class]);

        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response(['token' => 'tok-123', 'redirect_url' => self::SNAP_URL], 201),
        ]);
    }

    public function test_buyer_books_an_architect_slot_from_the_catalog(): void
    {
        $service = ArchitectService::factory()->create(['duration_minutes' => 60, 'price' => 500000]);
        ArchitectAvailability::factory()->create([
            'architect_id' => $service->architect_id, 'day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '12:00',
        ]);
        $buyer = User::factory()->buyer()->create();
        $monday = CarbonImmutable::now(config('app.timezone'))->next(CarbonInterface::MONDAY)->toDateString();

        Livewire::actingAs($buyer)->test(ArchitectSlots::class, ['slug' => $service->slug])
            ->set('date', $monday)
            ->call('selectSlot', "{$monday} 10:00")
            ->assertSet('selected', "{$monday} 10:00")
            ->set('meeting_type', 'online')
            ->call('book')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('architect_bookings', [
            'client_id' => $buyer->id, 'architect_service_id' => $service->id, 'status' => 'pending',
        ]);

        // Slot yang sama sudah terisi: pembeli lain tidak bisa memilihnya
        Livewire::actingAs(User::factory()->buyer()->create())->test(ArchitectSlots::class, ['slug' => $service->slug])
            ->set('date', $monday)
            ->call('selectSlot', "{$monday} 10:00")
            ->assertSet('selected', null);

        // Tamu diarahkan ke halaman login
        Livewire::test(ArchitectSlots::class, ['slug' => $service->slug])
            ->call('goLogin')
            ->assertRedirect(route('login'));
    }

    public function test_buyer_chats_with_seller_about_a_property(): void
    {
        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $property = Property::factory()->published()->create(['partner_id' => $seller->id]);
        $buyer = User::factory()->buyer()->create();

        Livewire::actingAs($buyer)->test(PropertyActions::class, ['property' => $property->id])->call('chat');

        $conversation = Conversation::firstOrFail();

        Livewire::actingAs($buyer)->test(Room::class, ['conversation' => $conversation->id])
            ->set('body', 'Halo, masih tersedia?')
            ->call('send')
            ->assertHasNoErrors();

        $this->actingAs($seller)->get(route('chat.room', $conversation->id))
            ->assertOk()->assertSee('Halo, masih tersedia?');
        $this->actingAs($seller)->get(route('chat.index'))
            ->assertOk()->assertSee('Halo, masih tersedia?');

        // Pihak ketiga tidak boleh membuka percakapan
        $this->actingAs(User::factory()->buyer()->create())
            ->get(route('chat.room', $conversation->id))->assertForbidden();

        // Tamu diarahkan login
        auth()->logout();
        $this->get(route('chat.index'))->assertRedirect(route('login'));
    }

    public function test_property_booking_fee_payment_and_finish_page_sync(): void
    {
        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $property = Property::factory()->published()->create(['partner_id' => $seller->id, 'price' => 1_000_000_000]);
        $buyer = User::factory()->buyer()->create();

        Livewire::actingAs($buyer)->test(PropertyActions::class, ['property' => $property->id])
            ->call('payFee')
            ->assertRedirect(self::SNAP_URL);

        $transaction = Transaction::firstOrFail();
        $this->assertSame(TransactionStatus::Pending, $transaction->status);
        $this->assertEquals(10_000_000, $transaction->amount);

        // Setelah membayar, Midtrans mengarahkan pembeli ke halaman selesai; status ditarik dari Midtrans
        Http::fake([
            'api.sandbox.midtrans.com/*' => Http::response([
                'status_code' => '200',
                'order_id' => $transaction->code,
                'gross_amount' => '10000000.00',
                'transaction_status' => 'settlement',
                'payment_type' => 'bank_transfer',
                'va_numbers' => [['bank' => 'bca', 'va_number' => '123']],
                'transaction_id' => 'mid-1',
            ], 200),
        ]);

        $this->actingAs($buyer)
            ->get('/pembayaran/selesai?order_id='.$transaction->code.'&transaction_status=settlement')
            ->assertRedirect(route('transactions.show', $transaction->code));

        $this->assertSame(TransactionStatus::Paid, $transaction->fresh()->status);
        $this->assertSame(PropertyStatus::Reserved, $property->fresh()->status);

        $this->actingAs($buyer)->get(route('transactions.show', $transaction->code))
            ->assertOk()->assertSee('Lunas');
        $this->actingAs($buyer)->get(route('transactions.index'))
            ->assertOk()->assertSee($transaction->code);

        // Pembeli lain tidak bisa membuka transaksi ini
        $this->actingAs(User::factory()->buyer()->create())
            ->get(route('transactions.show', $transaction->code))->assertForbidden();
    }

    public function test_architect_booking_detail_payment_and_cancellation(): void
    {
        $client = User::factory()->buyer()->create();
        $confirmed = ArchitectBooking::factory()->create([
            'client_id' => $client->id, 'status' => BookingStatus::Confirmed, 'price' => 750000,
        ]);

        Livewire::actingAs($client)->test(BookingDetail::class, ['booking' => $confirmed->id])
            ->assertSee('Bayar sekarang')
            ->call('pay')
            ->assertRedirect(self::SNAP_URL);

        $this->actingAs($client)->get(route('orders.index'))->assertOk()->assertSee($confirmed->code);
        $this->actingAs(User::factory()->buyer()->create())
            ->get(route('orders.show', $confirmed->id))->assertForbidden();

        $pending = ArchitectBooking::factory()->create(['client_id' => $client->id, 'status' => BookingStatus::Pending]);

        Livewire::actingAs($client)->test(BookingDetail::class, ['booking' => $pending->id])
            ->assertSee('Pembayaran dibuka setelah arsitek mengonfirmasi')
            ->set('cancelReason', 'Berubah rencana')
            ->call('cancel')
            ->assertHasNoErrors();

        $this->assertSame(BookingStatus::Cancelled, $pending->fresh()->status);
    }
}
