<?php

namespace Tests\Feature\Api\V1;

use App\Enums\PartnerType;
use App\Models\ArchitectAvailability;
use App\Models\ArchitectService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArchitectBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_booking_flow_prevents_double_booking_and_frees_slot_on_cancel(): void
    {
        $architect = User::factory()->partner(PartnerType::Architect)->create();
        $service = ArchitectService::factory()->create([
            'architect_id' => $architect->id, 'duration_minutes' => 60, 'price' => 500000,
        ]);
        ArchitectAvailability::factory()->create([
            'architect_id' => $architect->id, 'day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '12:00',
        ]);
        $buyer = User::factory()->buyer()->create();

        $monday = CarbonImmutable::now(config('app.timezone'))->next(CarbonInterface::MONDAY)->toDateString();
        $slotsUrl = "/api/v1/architect-services/{$service->slug}/slots?date={$monday}";

        // Katalog publik & slot
        $this->getJson('/api/v1/architect-services')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson($slotsUrl)->assertOk()->assertJsonCount(3, 'data.slots');

        // Buyer memesan jam 10:00
        Sanctum::actingAs($buyer);
        $payload = [
            'architect_service_id' => $service->id,
            'scheduled_start' => "{$monday} 10:00",
            'meeting_type' => 'online',
            'notes' => 'Diskusi denah',
        ];
        $id = $this->postJson('/api/v1/bookings', $payload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->json('data.id');

        // Slot yang sama tidak bisa dipesan lagi; slot tersisa 2
        $this->postJson('/api/v1/bookings', $payload)->assertStatus(422);
        $this->getJson($slotsUrl)->assertJsonCount(2, 'data.slots');

        // Di luar jam praktik ditolak
        $this->postJson('/api/v1/bookings', array_merge($payload, ['scheduled_start' => "{$monday} 14:00"]))
            ->assertStatus(422);

        // Pihak ketiga tidak boleh melihat pesanan
        Sanctum::actingAs(User::factory()->buyer()->create());
        $this->getJson("/api/v1/bookings/{$id}")->assertForbidden();

        // Arsitek mengonfirmasi
        Sanctum::actingAs($architect);
        $this->postJson("/api/v1/partner/bookings/{$id}/confirm")
            ->assertOk()->assertJsonPath('data.status', 'confirmed');

        // Klien membatalkan -> slot kembali tersedia
        Sanctum::actingAs($buyer);
        $this->postJson("/api/v1/bookings/{$id}/cancel", ['reason' => 'Berubah jadwal'])
            ->assertOk()->assertJsonPath('data.status', 'cancelled');
        $this->getJson($slotsUrl)->assertJsonCount(3, 'data.slots');
    }

    public function test_architect_cannot_book_own_service_and_past_dates_are_rejected(): void
    {
        $architect = User::factory()->partner(PartnerType::Architect)->create();
        $service = ArchitectService::factory()->create(['architect_id' => $architect->id]);

        // Arsitek bukan role "user" -> tidak punya akses endpoint booking klien
        Sanctum::actingAs($architect);
        $this->postJson('/api/v1/bookings', [])->assertForbidden();

        Sanctum::actingAs(User::factory()->buyer()->create());
        $this->postJson('/api/v1/bookings', [
            'architect_service_id' => $service->id,
            'scheduled_start' => now()->subDay()->format('Y-m-d H:i'),
            'meeting_type' => 'online',
        ])->assertStatus(422);
    }

    public function test_architect_can_manage_weekly_availability(): void
    {
        $architect = User::factory()->partner(PartnerType::Architect)->create();
        Sanctum::actingAs($architect);

        $this->putJson('/api/v1/partner/availabilities', ['availabilities' => [
            ['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '12:00'],
            ['day_of_week' => 1, 'start_time' => '13:00', 'end_time' => '16:00'],
        ]])->assertOk()->assertJsonCount(2, 'data');

        // Jadwal tumpang tindih ditolak
        $this->putJson('/api/v1/partner/availabilities', ['availabilities' => [
            ['day_of_week' => 2, 'start_time' => '09:00', 'end_time' => '12:00'],
            ['day_of_week' => 2, 'start_time' => '11:00', 'end_time' => '14:00'],
        ]])->assertStatus(422);
    }
}
