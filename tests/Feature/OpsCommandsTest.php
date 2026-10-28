<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class OpsCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_preflight_flags_unsafe_debug_and_missing_admin(): void
    {
        $this->seed(RoleSeeder::class);
        config(['app.debug' => true]);

        $exit = Artisan::call('app:preflight');
        $output = Artisan::output();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('APP_DEBUG', $output);
        $this->assertStringContainsString('Administrator', $output);
        $this->assertStringContainsString('GAGAL', $output);
    }

    public function test_preflight_flags_demo_accounts_and_sandbox_keys_in_production_mode(): void
    {
        $this->seed(RoleSeeder::class);
        User::factory()->admin()->create(['email' => 'admin@example.com']);
        config([
            'payments.midtrans.is_production' => true,
            'payments.midtrans.server_key' => 'SB-Mid-server-xxx',
        ]);

        Artisan::call('app:preflight');
        $output = Artisan::output();

        $this->assertStringContainsString('Akun demo', $output);
        $this->assertStringContainsString('kunci sandbox', $output);
    }

    public function test_create_admin_command_makes_a_verified_admin(): void
    {
        $this->seed(RoleSeeder::class);

        $this->artisan('app:create-admin', ['email' => 'owner@domain-anda.test'])
            ->expectsQuestion('Password (minimal 12 karakter, huruf dan angka)', 'KataSandiKuat123')
            ->expectsQuestion('Ulangi password', 'KataSandiKuat123')
            ->assertSuccessful();

        $admin = User::where('email', 'owner@domain-anda.test')->firstOrFail();
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->hasVerifiedEmail());
    }

    public function test_create_admin_rejects_weak_passwords_and_duplicates(): void
    {
        $this->seed(RoleSeeder::class);

        $this->artisan('app:create-admin', ['email' => 'owner@domain-anda.test'])
            ->expectsQuestion('Password (minimal 12 karakter, huruf dan angka)', 'pendek1')
            ->expectsQuestion('Ulangi password', 'pendek1')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'owner@domain-anda.test']);

        User::factory()->create(['email' => 'ada@domain-anda.test']);
        $this->artisan('app:create-admin', ['email' => 'ada@domain-anda.test'])->assertFailed();
    }

    public function test_expired_transactions_scheduler_command_is_registered(): void
    {
        $this->artisan('payments:expire-stale')->assertSuccessful();
    }
}
