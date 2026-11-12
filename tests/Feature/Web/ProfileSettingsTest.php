<?php

namespace Tests\Feature\Web;

use App\Enums\PartnerType;
use App\Livewire\Profile\ProfileSettings;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('public');
    }

    private function requireGd(): void
    {
        if (! function_exists('imagecreatefromstring')) {
            $this->markTestSkipped('Ekstensi GD tidak tersedia.');
        }
    }

    private function architect(): User
    {
        return User::factory()->partner(PartnerType::Architect)->create();
    }

    // ------------------------------------------------------------------ halaman & tab

    public function test_profile_page_requires_login_and_shows_tabs_for_each_role(): void
    {
        $this->get('/profil')->assertRedirect(route('login'));

        $buyer = User::factory()->buyer()->create();
        $this->actingAs($buyer)->get('/profil')
            ->assertOk()
            ->assertSee('Profil Saya')
            ->assertSee('Keamanan')
            ->assertSee('Pembeli')
            ->assertDontSee('Branding Studio')
            ->assertDontSee('Profil Bisnis')
            ->assertSee('data:image/svg+xml;base64', false)     // avatar inisial lokal
            ->assertSee('Pengaturan Profil');                    // tautan di menu pengguna

        $this->actingAs($this->architect())->get('/profil')->assertOk()->assertSee('Branding Studio')->assertSee('Arsitek');
        $this->actingAs(User::factory()->partner(PartnerType::Seller)->create())->get('/profil')->assertOk()->assertSee('Profil Bisnis');
        $this->actingAs(User::factory()->admin()->create())->get('/profil')->assertOk()->assertSee('Administrator');
    }

    public function test_business_tab_exists_only_for_partners_and_tab_is_kept_in_the_url(): void
    {
        $buyer = User::factory()->buyer()->create();

        Livewire::actingAs($buyer)->test(ProfileSettings::class)
            ->call('setTab', 'bisnis')->assertSet('tab', 'profil')
            ->set('tab', 'bisnis')->assertSet('tab', 'profil')
            ->call('setTab', 'keamanan')->assertSet('tab', 'keamanan');

        Livewire::actingAs($this->architect())->test(ProfileSettings::class)
            ->call('setTab', 'bisnis')->assertSet('tab', 'bisnis')->assertSee('Nama studio');

        Livewire::withQueryParams(['tab' => 'keamanan'])->actingAs($buyer)->test(ProfileSettings::class)
            ->assertSet('tab', 'keamanan')->assertSee('Ganti kata sandi');
    }

    // ------------------------------------------------------------------ tab 1: profil

    public function test_user_updates_name_and_phone(): void
    {
        $user = User::factory()->buyer()->create();

        Livewire::actingAs($user)->test(ProfileSettings::class)
            ->set('name', 'Nama Baru Uji')
            ->set('phone', '0812 9999-0000')
            ->call('saveProfile')
            ->assertHasNoErrors()
            ->assertSee('Profil berhasil disimpan');

        $fresh = $user->fresh();
        $this->assertSame('Nama Baru Uji', $fresh->name);
        $this->assertSame('081299990000', $fresh->phone);
    }

    public function test_phone_and_email_are_validated_and_unique(): void
    {
        $user = User::factory()->buyer()->create();
        $other = User::factory()->buyer()->create(['email' => 'dipakai@example.com', 'phone' => '081211112222']);

        Livewire::actingAs($user)->test(ProfileSettings::class)
            ->set('phone', '12345')->call('saveProfile')->assertHasErrors(['phone'])
            ->set('phone', $other->phone)->call('saveProfile')->assertHasErrors(['phone'])
            ->set('phone', '081233334444')->set('name', '')->call('saveProfile')->assertHasErrors(['name'])
            ->set('name', 'Valid')->set('email', 'bukan-email')->call('saveProfile')->assertHasErrors(['email'])
            ->set('email', 'dipakai@example.com')->set('emailPassword', 'apa-saja')->call('saveProfile')->assertHasErrors(['email']);
    }

    public function test_changing_email_needs_the_password_and_triggers_reverification(): void
    {
        Notification::fake();
        $user = User::factory()->buyer()->create(['email' => 'lama@example.com', 'password' => 'Rahasia123']);

        $component = Livewire::actingAs($user)->test(ProfileSettings::class)
            ->set('email', 'Baru@Example.com')
            ->assertSee('Kata sandi Anda');                                // kolom kata sandi muncul

        $component->call('saveProfile')->assertHasErrors(['emailPassword']);
        $component->set('emailPassword', 'salah-total')->call('saveProfile')->assertHasErrors(['emailPassword']);
        $this->assertSame('lama@example.com', $user->fresh()->email);

        $component->set('emailPassword', 'Rahasia123')->call('saveProfile')->assertHasNoErrors();

        $fresh = $user->fresh();
        $this->assertSame('baru@example.com', $fresh->email);               // dinormalisasi huruf kecil
        $this->assertNull($fresh->email_verified_at);
        Notification::assertSentTo($fresh, VerifyEmailNotification::class);
    }

    public function test_avatar_is_processed_replaced_and_removable(): void
    {
        $this->requireGd();
        $user = User::factory()->buyer()->create();
        $component = Livewire::actingAs($user)->test(ProfileSettings::class);

        $component->set('avatar', UploadedFile::fake()->image('foto.jpg', 800, 600))->call('saveProfile')->assertHasNoErrors();

        $first = $user->fresh()->avatar;
        $this->assertStringStartsWith('avatars/', $first);
        Storage::disk('public')->assertExists($first);
        [$width, $height] = getimagesize(Storage::disk('public')->path($first));
        $this->assertSame([512, 512], [$width, $height]);                    // dipotong persegi & dikecilkan
        $this->assertStringContainsString($first, $user->fresh()->avatar_url);

        $component->set('avatar', UploadedFile::fake()->image('baru.png', 300, 300))->call('saveProfile')->assertHasNoErrors();

        $second = $user->fresh()->avatar;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);                      // berkas lama dihapus
        Storage::disk('public')->assertExists($second);

        $component->call('removeAvatar');
        $this->assertNull($user->fresh()->avatar);
        Storage::disk('public')->assertMissing($second);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $user->fresh()->avatar_url);
    }

    public function test_avatar_rejects_wrong_type_and_oversize_files(): void
    {
        $user = User::factory()->buyer()->create();

        Livewire::actingAs($user)->test(ProfileSettings::class)
            ->set('avatar', UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'))->assertHasErrors(['avatar'])
            ->set('avatar', UploadedFile::fake()->create('skrip.svg', 10, 'image/svg+xml'))->assertHasErrors(['avatar'])
            ->set('avatar', UploadedFile::fake()->image('besar.jpg', 100, 100)->size(3000))->assertHasErrors(['avatar']);

        $this->assertNull($user->fresh()->avatar);
    }

    // ------------------------------------------------------------------ tab 2: bisnis

    public function test_architect_updates_the_studio_profile_and_logo(): void
    {
        $this->requireGd();
        $architect = $this->architect();

        $component = Livewire::actingAs($architect)->test(ProfileSettings::class)
            ->call('setTab', 'bisnis')
            ->assertSet('business_name', $architect->partnerProfile->business_name)
            ->set('business_name', 'Studio Garis Uji')
            ->set('business_phone', '021 5551234')
            ->set('city', 'Bandung')
            ->set('address', 'Jl. Braga No. 10')
            ->set('description', 'Desain yang jujur terhadap material.')
            ->set('license_number', 'IAI-12345')
            ->set('portfolio_url', 'https://studio.example.com/karya')
            ->set('logo', UploadedFile::fake()->image('logo.png', 900, 300))
            ->call('saveBusiness')
            ->assertHasNoErrors()
            ->assertSee('Profil bisnis berhasil disimpan');

        $profile = $architect->partnerProfile->fresh();
        $this->assertSame('Studio Garis Uji', $profile->business_name);
        $this->assertSame('021 5551234', $profile->business_phone);
        $this->assertSame('Bandung', $profile->city);
        $this->assertSame('IAI-12345', $profile->license_number);
        $this->assertSame('https://studio.example.com/karya', $profile->portfolio_url);

        $this->assertStringStartsWith('logos/', $profile->logo);
        $this->assertStringEndsWith('.png', $profile->logo);
        Storage::disk('public')->assertExists($profile->logo);
        [$width, $height] = getimagesize(Storage::disk('public')->path($profile->logo));
        $this->assertSame([600, 200], [$width, $height]);                    // rasio asli dipertahankan

        $oldLogo = $profile->logo;
        $component->set('logo', UploadedFile::fake()->image('logo2.png', 200, 200))->call('saveBusiness')->assertHasNoErrors();
        Storage::disk('public')->assertMissing($oldLogo);

        $component->call('removeLogo');
        $this->assertNull($profile->fresh()->logo);
    }

    public function test_business_form_validation_and_access_control(): void
    {
        $architect = $this->architect();

        Livewire::actingAs($architect)->test(ProfileSettings::class)
            ->call('setTab', 'bisnis')
            ->set('business_name', '')->call('saveBusiness')->assertHasErrors(['business_name'])
            ->set('business_name', 'Studio Valid')
            ->set('portfolio_url', 'javascript:alert(1)')->call('saveBusiness')->assertHasErrors(['portfolio_url'])
            ->set('portfolio_url', 'ftp://example.com/karya')->call('saveBusiness')->assertHasErrors(['portfolio_url'])
            ->set('portfolio_url', '')
            ->set('business_phone', 'abc')->call('saveBusiness')->assertHasErrors(['business_phone'])
            ->set('business_phone', '')->call('saveBusiness')->assertHasNoErrors();

        $this->assertNull($architect->partnerProfile->fresh()->portfolio_url);   // kosong disimpan sebagai null

        Livewire::actingAs(User::factory()->buyer()->create())->test(ProfileSettings::class)
            ->call('saveBusiness')->assertForbidden();
        Livewire::actingAs(User::factory()->buyer()->create())->test(ProfileSettings::class)
            ->call('removeLogo')->assertForbidden();
    }

    public function test_identity_change_can_send_an_approved_partner_back_for_review(): void
    {
        $architect = $this->architect();
        $this->assertSame('approved', $architect->partnerProfile->verification_status->value);

        // bawaan: dimatikan
        Livewire::actingAs($architect)->test(ProfileSettings::class)->call('setTab', 'bisnis')
            ->set('business_name', 'Nama Usaha Berubah')->call('saveBusiness')->assertHasNoErrors();
        $this->assertSame('approved', $architect->partnerProfile->fresh()->verification_status->value);

        config(['concierge.partner_reverify_on_identity_change' => true]);

        // perubahan non-identitas tidak memicu peninjauan
        Livewire::actingAs($architect)->test(ProfileSettings::class)->call('setTab', 'bisnis')
            ->set('city', 'Surabaya')->call('saveBusiness')->assertHasNoErrors();
        $this->assertSame('approved', $architect->partnerProfile->fresh()->verification_status->value);

        // perubahan nama usaha / nomor izin memicu
        Livewire::actingAs($architect)->test(ProfileSettings::class)->call('setTab', 'bisnis')
            ->set('license_number', 'IAI-BARU-99')->call('saveBusiness')->assertHasNoErrors()->assertSee('ditinjau ulang');

        $profile = $architect->partnerProfile->fresh();
        $this->assertSame('pending', $profile->verification_status->value);
        $this->assertNull($profile->verified_at);
    }

    // ------------------------------------------------------------------ tab 3: keamanan

    public function test_password_change_is_validated_separately_and_revokes_api_tokens(): void
    {
        $user = User::factory()->buyer()->create(['password' => 'Rahasia123']);
        $user->createToken('perangkat-lama');
        $this->assertSame(1, $user->tokens()->count());

        $component = Livewire::actingAs($user)->test(ProfileSettings::class)->call('setTab', 'keamanan');

        $component->set('current_password', 'salah')->set('password', 'BaruAman123')->set('password_confirmation', 'BaruAman123')
            ->call('updatePassword')->assertHasErrors(['current_password']);

        $component->set('current_password', 'Rahasia123')->set('password', 'pendek')->set('password_confirmation', 'pendek')
            ->call('updatePassword')->assertHasErrors(['password']);

        $component->set('password', 'BaruAman123')->set('password_confirmation', 'tidak-sama')
            ->call('updatePassword')->assertHasErrors(['password']);

        $component->set('password', 'Rahasia123')->set('password_confirmation', 'Rahasia123')
            ->call('updatePassword')->assertHasErrors(['password']);                 // sama dengan yang lama

        $this->assertTrue(Hash::check('Rahasia123', $user->fresh()->password));      // belum berubah

        $component->set('password', 'BaruAman123')->set('password_confirmation', 'BaruAman123')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSet('password', '')
            ->assertSet('current_password', '')
            ->assertSee('Kata sandi diperbarui');

        $this->assertTrue(Hash::check('BaruAman123', $user->fresh()->password));
        $this->assertSame(0, $user->tokens()->count());
    }
}
