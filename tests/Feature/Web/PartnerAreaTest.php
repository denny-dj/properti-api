<?php

namespace Tests\Feature\Web;

use App\Enums\BookingStatus;
use App\Enums\PartnerType;
use App\Enums\PropertyStatus;
use App\Livewire\Partner\AvailabilityEditor;
use App\Livewire\Partner\BookingInbox;
use App\Livewire\Partner\PropertyForm;
use App\Livewire\Partner\PropertyList;
use App\Livewire\Partner\ServiceManager;
use App\Models\ArchitectBooking;
use App\Models\ArchitectService;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PartnerAreaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_access_rules_for_the_partner_area(): void
    {
        $this->get('/mitra')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->buyer()->create())->get('/mitra')->assertForbidden();

        $pendingSeller = User::factory()->partner(PartnerType::Seller, false)->create();
        $this->actingAs($pendingSeller)->get('/mitra')->assertOk()->assertSee('menunggu verifikasi');
        $this->actingAs($pendingSeller)->get('/mitra/properti')->assertRedirect(route('partner.dashboard'));

        $architect = User::factory()->partner(PartnerType::Architect)->create();
        $this->actingAs($architect)->get('/mitra')->assertOk();
        $this->actingAs($architect)->get('/mitra/jadwal')->assertOk();
        $this->actingAs($architect)->get('/mitra/properti')->assertRedirect(route('partner.dashboard'));

        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $this->actingAs($seller)->get('/mitra/properti')->assertOk();
        $this->actingAs($seller)->get('/mitra/properti/baru')->assertOk();
        $this->actingAs($seller)->get('/mitra/jadwal')->assertRedirect(route('partner.dashboard'));
    }

    public function test_seller_creates_edits_uploads_photos_and_submits_a_property(): void
    {
        Storage::fake('public');
        $seller = User::factory()->partner(PartnerType::Seller)->create();

        Livewire::actingAs($seller)->test(PropertyForm::class)
            ->set('title', 'Rumah Uji Mitra')
            ->set('property_type', 'house')
            ->set('listing_type', 'sale')
            ->set('price', '850000000')
            ->set('bedrooms', '3')
            ->set('bathrooms', '2')
            ->set('floors', '2')
            ->set('land_area', '90')
            ->set('building_area', '72')
            ->set('certificate', 'SHM')
            ->set('address', 'Jl. Melati No. 5')
            ->set('city', 'Depok')
            ->set('province', 'Jawa Barat')
            ->set('latitude', '-6.4025')
            ->set('longitude', '106.7942')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $property = Property::where('partner_id', $seller->id)->firstOrFail();
        $this->assertSame(PropertyStatus::Draft, $property->status);

        $form = Livewire::actingAs($seller)->test(PropertyForm::class, ['property' => $property->id])
            ->assertSet('title', 'Rumah Uji Mitra');

        // Belum ada foto -> tidak bisa diajukan
        $form->call('submit')->assertHasErrors('form');

        $form->set('photos', [UploadedFile::fake()->image('depan.jpg')])
            ->call('uploadPhotos')
            ->assertHasNoErrors();
        $this->assertDatabaseCount('property_images', 1);

        $form->call('submit')->assertHasNoErrors();
        $this->assertSame(PropertyStatus::PendingReview, $property->fresh()->status);
    }

    public function test_seller_cannot_touch_other_sellers_property_and_can_mark_reserved_as_sold(): void
    {
        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $other = User::factory()->partner(PartnerType::Seller)->create();

        $foreign = Property::factory()->published()->create(['partner_id' => $other->id]);
        $this->actingAs($seller)->get(route('partner.properties.edit', $foreign->id))->assertForbidden();

        $mine = Property::factory()->published()->create(['partner_id' => $seller->id]);
        $mine->update(['status' => PropertyStatus::Reserved]);

        // Properti yang sudah dipesan tidak bisa diubah, hanya ditandai terjual
        $this->actingAs($seller)->get(route('partner.properties.edit', $mine->id))
            ->assertRedirect(route('partner.properties.index'));

        Livewire::actingAs($seller)->test(PropertyList::class)->call('markSold', $mine->id);
        $this->assertSame(PropertyStatus::Sold, $mine->fresh()->status);
    }

    public function test_architect_manages_services_and_weekly_availability(): void
    {
        $architect = User::factory()->partner(PartnerType::Architect)->create();

        Livewire::actingAs($architect)->test(ServiceManager::class)
            ->call('create')
            ->set('title', 'Konsultasi Denah')
            ->set('service_type', 'consultation')
            ->set('price', '350000')
            ->set('duration_minutes', '60')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('architect_services', ['architect_id' => $architect->id, 'title' => 'Konsultasi Denah']);

        Livewire::actingAs($architect)->test(AvailabilityEditor::class)
            ->set('rows', [
                ['day' => 1, 'start' => '09:00', 'end' => '12:00'],
                ['day' => 1, 'start' => '13:00', 'end' => '16:00'],
            ])
            ->call('save')
            ->assertHasNoErrors();
        $this->assertDatabaseCount('architect_availabilities', 2);

        Livewire::actingAs($architect)->test(AvailabilityEditor::class)
            ->set('rows', [
                ['day' => 2, 'start' => '09:00', 'end' => '12:00'],
                ['day' => 2, 'start' => '11:00', 'end' => '14:00'],
            ])
            ->call('save')
            ->assertHasErrors('rows');
    }

    public function test_architect_confirms_and_rejects_incoming_bookings(): void
    {
        $architect = User::factory()->partner(PartnerType::Architect)->create();
        $service = ArchitectService::factory()->create(['architect_id' => $architect->id]);

        $toConfirm = ArchitectBooking::factory()->create(['architect_service_id' => $service->id]);
        $toReject = ArchitectBooking::factory()->create(['architect_service_id' => $service->id]);

        $inbox = Livewire::actingAs($architect)->test(BookingInbox::class)
            ->call('confirm', $toConfirm->id);
        $this->assertSame(BookingStatus::Confirmed, $toConfirm->fresh()->status);

        $inbox->call('startAction', $toReject->id, 'reject')
            ->set('reason', 'x')
            ->call('submitAction')
            ->assertHasErrors('reason');

        $inbox->set('reason', 'Jadwal bentrok')->call('submitAction')->assertHasNoErrors();
        $this->assertSame(BookingStatus::Rejected, $toReject->fresh()->status);

        // Arsitek lain tidak boleh mengelola pesanan ini
        $untouched = ArchitectBooking::factory()->create(['architect_service_id' => $service->id]);
        $stranger = User::factory()->partner(PartnerType::Architect)->create();

        Livewire::actingAs($stranger)->test(BookingInbox::class)
            ->call('confirm', $untouched->id)
            ->assertStatus(404);

        $this->assertSame(BookingStatus::Pending, $untouched->fresh()->status);
    }

    public function test_partner_sees_incoming_payments(): void
    {
        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $buyer = User::factory()->buyer()->create();

        Transaction::create([
            'code' => 'TRX-TEST-0001',
            'user_id' => $buyer->id,
            'payee_id' => $seller->id,
            'type' => 'property_booking_fee',
            'description' => 'Booking fee uji',
            'amount' => 10000000,
            'status' => 'paid',
            'gateway' => 'midtrans',
            'payment_type' => 'bank_transfer',
        ]);

        $this->actingAs($seller)->get('/mitra/transaksi')
            ->assertOk()
            ->assertSee('TRX-TEST-0001')
            ->assertSee('Booking fee uji');
    }
}
