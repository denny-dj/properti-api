<?php

namespace Tests\Feature\Web;

use App\Enums\VerificationStatus;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\RegisterPartner;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_login_page_and_successful_login(): void
    {
        $this->get('/masuk')->assertOk();

        $user = User::factory()->buyer()->create(['email' => 'budi@example.com']);

        Livewire::test(Login::class)
            ->set('email', 'budi@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_and_suspended_accounts_cannot_login(): void
    {
        User::factory()->buyer()->create(['email' => 'a@example.com']);
        User::factory()->buyer()->suspended()->create(['email' => 'b@example.com']);

        Livewire::test(Login::class)
            ->set('email', 'a@example.com')->set('password', 'salah')
            ->call('login')->assertHasErrors(['email']);

        Livewire::test(Login::class)
            ->set('email', 'b@example.com')->set('password', 'password')
            ->call('login')->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_buyer_registration_creates_user_role_and_logs_in(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Siti')
            ->set('email', 'siti@example.com')
            ->set('phone', '081234567891')
            ->set('password', 'Rahasia123')
            ->set('password_confirmation', 'Rahasia123')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $user = User::where('email', 'siti@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('user'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_partner_registration_requires_license_for_architect_and_is_pending(): void
    {
        Livewire::test(RegisterPartner::class)
            ->set('name', 'Arsitek Baru')
            ->set('email', 'arsitek@example.com')
            ->set('phone', '081234567892')
            ->set('password', 'Rahasia123')
            ->set('password_confirmation', 'Rahasia123')
            ->set('partner_type', 'architect')
            ->set('business_name', 'Biro Uji')
            ->call('register')
            ->assertHasErrors(['license_number']);

        Livewire::test(RegisterPartner::class)
            ->set('name', 'Arsitek Baru')
            ->set('email', 'arsitek@example.com')
            ->set('phone', '081234567892')
            ->set('password', 'Rahasia123')
            ->set('password_confirmation', 'Rahasia123')
            ->set('partner_type', 'architect')
            ->set('business_name', 'Biro Uji')
            ->set('license_number', 'IAI-123456')
            ->call('register')
            ->assertHasNoErrors();

        $user = User::where('email', 'arsitek@example.com')->first();
        $this->assertTrue($user->hasRole('partner'));
        $this->assertSame(VerificationStatus::Pending, $user->partnerProfile->verification_status);
    }

    public function test_authenticated_users_are_redirected_away_from_guest_pages_and_can_logout(): void
    {
        $user = User::factory()->buyer()->create();

        $this->actingAs($user)->get('/masuk')->assertRedirect('/');
        $this->actingAs($user)->post('/keluar')->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
