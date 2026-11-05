<?php

namespace Tests\Feature\Web;

use App\Enums\PartnerType;
use App\Models\ArchitectPortfolio;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionExportTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->seller = User::factory()->partner(PartnerType::Seller)->create();
        $this->seller->partnerProfile->update(['business_name' => 'PT Griya Uji']);
        $this->buyer = User::factory()->buyer()->create(['name' => '=SUM(1+1)']);
    }

    /** @return array<int, array<int, string>> */
    private function parse(string $content, string $delimiter = ';'): array
    {
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content, 'BOM UTF-8 harus ada');
        $lines = array_values(array_filter(preg_split('/\r?\n/', substr($content, 3)), fn ($l) => $l !== ''));

        return array_map(fn (string $l) => str_getcsv($l, $delimiter, '"', '\\'), $lines);
    }

    /** @return array<string, array<int, string>> baris data diindeks Order ID */
    private function byCode(array $rows): array
    {
        $out = [];

        foreach (array_slice($rows, 1) as $row) {
            $out[$row[1]] = $row;
        }

        return $out;
    }

    private function property(string $status, int $price): Property
    {
        return Property::factory()->create(['partner_id' => $this->seller->id, 'status' => $status, 'price' => $price, 'title' => 'Rumah '.$status.' '.$price]);
    }

    private function fee(string $code, ?Property $property, string $status, string $paidAt, array $extra = []): Transaction
    {
        return Transaction::create($extra + [
            'code' => $code,
            'user_id' => $this->buyer->id,
            'payee_id' => $this->seller->id,
            'type' => 'property_booking_fee',
            'property_id' => $property?->id,
            'description' => 'Booking fee',
            'amount' => 5_000_000,
            'platform_fee' => 17_000_000,
            'net_partner_amount' => 0,
            'status' => $status,
            'gateway' => 'midtrans',
            'payment_type' => 'bank_transfer',
            'paid_at' => $paidAt,
        ]);
    }

    private function seedPropertyScenario(): void
    {
        $sold = $this->property('sold', 850_000_000);
        $this->fee('TRX-EX-A1', $sold, 'paid', '2026-10-01 09:00:00');                     // tergantikan A2
        $this->fee('TRX-EX-A2', $sold, 'paid', '2026-10-05 09:00:00');

        $reserved = $this->property('reserved', 1_200_000_000);
        $this->fee('TRX-EX-B1', $reserved, 'paid', '2026-10-10 09:00:00', ['payment_type' => 'credit_card']);

        $released = $this->property('published', 700_000_000);
        $this->fee('TRX-EX-C1', $released, 'paid', '2026-09-20 09:00:00', ['payment_type' => 'gopay']);

        $refunded = $this->property('published', 600_000_000);
        $this->fee('TRX-EX-D1', $refunded, 'refunded', '2026-09-25 09:00:00', ['payment_type' => 'qris']);

        $this->fee('TRX-EX-F1', null, 'paid', '2026-10-12 09:00:00', ['payment_type' => 'cstore']);   // properti hilang
        $this->fee('TRX-EX-P1', $reserved, 'pending', '2026-10-13 09:00:00');                         // tidak diekspor
    }

    public function test_property_export_matches_the_finance_sheet_columns_and_statuses(): void
    {
        $this->seedPropertyScenario();

        $response = $this->actingAs(User::factory()->admin()->create())->get('/admin/transaksi/ekspor');
        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('transaksi-properti-', (string) $response->headers->get('content-disposition'));

        $rows = $this->parse($response->streamedContent());

        $this->assertSame([
            'Tanggal Bayar', 'Order ID', 'Nama Buyer', 'Nama Partner', 'Properti', 'Harga Properti (Rp)',
            'Status Penjualan', 'Metode Bayar', 'Booking Fee Masuk (Rp)', 'Pembayaran Lain via Platform (Rp)',
        ], array_slice($rows[0], 0, 10));

        $data = $this->byCode($rows);
        $this->assertCount(6, $data);                       // pending tidak ikut
        $this->assertArrayNotHasKey('TRX-EX-P1', $data);

        $a2 = $data['TRX-EX-A2'];
        $this->assertSame('2026-10-05', $a2[0]);
        $this->assertSame('PT Griya Uji', $a2[3]);
        $this->assertSame('850000000', $a2[5]);
        $this->assertSame('Terjual', $a2[6]);
        $this->assertSame('VA', $a2[7]);
        $this->assertSame('5000000', $a2[8]);
        $this->assertSame('0', $a2[9]);
        $this->assertSame('17000000', $a2[10]);             // rujukan komisi tercatat

        $this->assertSame('Batal', $data['TRX-EX-A1'][6]);          // sudah tergantikan transaksi lain
        $this->assertSame('Booking Aktif', $data['TRX-EX-B1'][6]);
        $this->assertSame('Kartu Kredit', $data['TRX-EX-B1'][7]);
        $this->assertSame('Batal', $data['TRX-EX-C1'][6]);          // booking dilepas
        $this->assertSame('E-Wallet', $data['TRX-EX-C1'][7]);
        $this->assertSame('Batal', $data['TRX-EX-D1'][6]);          // dikembalikan
        $this->assertSame('QRIS', $data['TRX-EX-D1'][7]);
        $this->assertSame('Perlu dicek', $data['TRX-EX-F1'][6]);    // properti tidak ada
        $this->assertSame('', $data['TRX-EX-F1'][7]);               // metode tidak dikenali -> kosong
    }

    public function test_export_neutralises_csv_injection_and_honours_filters_and_delimiter(): void
    {
        $this->seedPropertyScenario();
        $admin = User::factory()->admin()->create();

        $all = $this->byCode($this->parse($this->actingAs($admin)->get('/admin/transaksi/ekspor')->streamedContent()));
        $this->assertSame("'=SUM(1+1)", $all['TRX-EX-A2'][2]);     // nama buyer berawalan "=" diberi apostrof

        $period = $this->byCode($this->parse($this->actingAs($admin)->get('/admin/transaksi/ekspor?from=2026-10-04&to=2026-10-06')->streamedContent()));
        $this->assertSame(['TRX-EX-A2'], array_keys($period));

        $refunded = $this->byCode($this->parse($this->actingAs($admin)->get('/admin/transaksi/ekspor?status=refunded')->streamedContent()));
        $this->assertSame(['TRX-EX-D1'], array_keys($refunded));

        $empty = $this->parse($this->actingAs($admin)->get('/admin/transaksi/ekspor?from=2999-01-01')->streamedContent());
        $this->assertCount(1, $empty);                             // hanya header

        $comma = $this->parse($this->actingAs($admin)->get('/admin/transaksi/ekspor?delimiter=comma&status=refunded')->streamedContent(), ',');
        $this->assertSame('Tanggal Bayar', $comma[0][0]);
        $this->assertSame('TRX-EX-D1', $comma[1][1]);
    }

    public function test_design_export_includes_commission_and_handover_status(): void
    {
        $architect = User::factory()->partner(PartnerType::Architect)->create();
        $architect->partnerProfile->update(['business_name' => 'Studio Uji']);
        $portfolio = ArchitectPortfolio::factory()->published()->readyToBuild()->create(['user_id' => $architect->id, 'title' => 'Rumah Desain Uji']);

        $make = fn (string $code, string $status, array $extra = []) => Transaction::create($extra + [
            'code' => $code, 'user_id' => $this->buyer->id, 'payee_id' => $architect->id, 'type' => 'architect_blueprint',
            'portfolio_id' => $portfolio->id, 'description' => 'Paket desain', 'amount' => 5_000_000, 'platform_fee' => 625_000,
            'net_partner_amount' => 4_375_000, 'status' => $status, 'gateway' => 'midtrans', 'payment_type' => 'bank_transfer',
            'paid_at' => '2026-10-05 09:00:00',
        ]);

        $make('TRX-DS-1', 'paid', ['deliverable_link' => 'https://drive.google.com/drive/folders/a', 'client_confirmed_at' => '2026-10-08 10:00:00']);
        $make('TRX-DS-2', 'paid');
        $make('TRX-DS-3', 'paid', ['deliverable_link' => 'https://drive.google.com/drive/folders/b']);
        $make('TRX-DS-4', 'refunded');

        $response = $this->actingAs(User::factory()->admin()->create())->get('/admin/transaksi/ekspor?jenis=desain');
        $rows = $this->parse($response->streamedContent());
        $data = $this->byCode($rows);

        $this->assertContains('Hak Bersih Mitra (Rp)', $rows[0]);
        $this->assertCount(4, $data);
        $this->assertSame('Studio Uji', $data['TRX-DS-1'][3]);
        $this->assertSame('Rumah Desain Uji', $data['TRX-DS-1'][4]);
        $this->assertSame('Desain siap bangun', $data['TRX-DS-1'][5]);
        $this->assertSame(['5000000', '625000', '4375000'], array_slice($data['TRX-DS-1'], 6, 3));
        $this->assertSame('Dikonfirmasi', $data['TRX-DS-1'][9]);
        $this->assertSame('2026-10-08', $data['TRX-DS-1'][10]);
        $this->assertSame('Menunggu berkas', $data['TRX-DS-2'][9]);
        $this->assertSame('Menunggu konfirmasi pembeli', $data['TRX-DS-3'][9]);
        $this->assertSame('Dikembalikan', $data['TRX-DS-4'][9]);
    }

    public function test_export_is_restricted_validated_and_linked_from_the_transactions_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->get('/admin/transaksi/ekspor')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->buyer()->create())->get('/admin/transaksi/ekspor')->assertForbidden();
        $this->actingAs($this->seller)->get('/admin/transaksi/ekspor')->assertForbidden();

        $this->actingAs($admin)->get('/admin/transaksi/ekspor?jenis=ngawur')->assertSessionHasErrors('jenis');
        $this->actingAs($admin)->get('/admin/transaksi/ekspor?from=2026-10-10&to=2026-10-01')->assertSessionHasErrors('to');

        $this->actingAs($admin)->get('/admin/transaksi')
            ->assertOk()
            ->assertSee('Ekspor CSV')
            ->assertSee('admin/transaksi/ekspor');
    }
}
