<?php

namespace Tests\Feature\Api\V1;

use App\Enums\PartnerType;
use App\Events\MessageSent;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_buyer_and_seller_exchange_messages_with_unread_counts(): void
    {
        Event::fake([MessageSent::class]);

        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $property = Property::factory()->published()->create(['partner_id' => $seller->id]);
        $buyer = User::factory()->buyer()->create();

        // Buyer memulai percakapan dari halaman properti, sekaligus kirim pesan pertama
        Sanctum::actingAs($buyer);
        $conversationId = $this->postJson('/api/v1/conversations', [
            'property_id' => $property->id,
            'message' => 'Halo, apakah masih tersedia?',
        ])->assertCreated()
            ->assertJsonPath('data.subject.type', 'property')
            ->assertJsonPath('data.counterpart.id', $seller->id)
            ->json('data.id');

        Event::assertDispatched(MessageSent::class);

        // Memulai lagi dengan konteks yang sama -> percakapan yang sama (bukan duplikat)
        $this->postJson('/api/v1/conversations', ['property_id' => $property->id])
            ->assertOk()->assertJsonPath('data.id', $conversationId);

        // Seller melihat inbox: 1 pesan belum dibaca
        Sanctum::actingAs($seller);
        $this->getJson('/api/v1/conversations')
            ->assertOk()
            ->assertJsonPath('data.0.id', $conversationId)
            ->assertJsonPath('data.0.unread_count', 1)
            ->assertJsonPath('data.0.counterpart.id', $buyer->id);

        $this->getJson("/api/v1/conversations/{$conversationId}/messages")
            ->assertOk()->assertJsonCount(1, 'data');

        // Seller membalas, lalu membaca -> unread seller 0
        $this->postJson("/api/v1/conversations/{$conversationId}/messages", ['body' => 'Masih, silakan survei.'])
            ->assertCreated();
        $this->postJson("/api/v1/conversations/{$conversationId}/read")->assertOk();
        $this->getJson('/api/v1/conversations')->assertJsonPath('data.0.unread_count', 0);

        // Buyer: ada 1 balasan belum dibaca, setelah read menjadi 0
        Sanctum::actingAs($buyer);
        $this->getJson('/api/v1/conversations')->assertJsonPath('data.0.unread_count', 1);
        $this->postJson("/api/v1/conversations/{$conversationId}/read")->assertOk();
        $this->getJson('/api/v1/conversations')->assertJsonPath('data.0.unread_count', 0);
    }

    public function test_outsiders_cannot_read_or_post_and_sellers_cannot_start_chats(): void
    {
        Event::fake([MessageSent::class]);

        $seller = User::factory()->partner(PartnerType::Seller)->create();
        $property = Property::factory()->published()->create(['partner_id' => $seller->id]);
        $buyer = User::factory()->buyer()->create();

        Sanctum::actingAs($buyer);
        $id = $this->postJson('/api/v1/conversations', ['property_id' => $property->id, 'message' => 'Halo'])
            ->assertCreated()->json('data.id');

        // Pihak ketiga ditolak
        Sanctum::actingAs(User::factory()->buyer()->create());
        $this->getJson("/api/v1/conversations/{$id}/messages")->assertForbidden();
        $this->postJson("/api/v1/conversations/{$id}/messages", ['body' => 'nyelonong'])->assertForbidden();

        // Partner tidak boleh memulai percakapan (hanya buyer)
        Sanctum::actingAs($seller);
        $this->postJson('/api/v1/conversations', ['property_id' => $property->id])->assertForbidden();

        // Konteks wajib tepat satu
        Sanctum::actingAs($buyer);
        $this->postJson('/api/v1/conversations', [])->assertStatus(422);
        $this->postJson('/api/v1/conversations', [
            'property_id' => $property->id, 'partner_id' => $seller->id,
        ])->assertStatus(422);

        // Pesan kosong ditolak
        $this->postJson("/api/v1/conversations/{$id}/messages", ['body' => '   '])->assertStatus(422);
    }
}
