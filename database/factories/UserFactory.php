<?php

namespace Database\Factories;

use App\Enums\PartnerType;
use App\Enums\RoleName;
use App\Models\PartnerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->unique()->numerify('08##########'),
            'status' => 'active',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => 'suspended']);
    }

    // NOTE: state di bawah mengasumsikan RoleSeeder sudah dijalankan.
    public function admin(): static
    {
        return $this->afterCreating(fn (User $u) => $u->assignRole(RoleName::Admin->value));
    }

    public function buyer(): static
    {
        return $this->afterCreating(fn (User $u) => $u->assignRole(RoleName::User->value));
    }

    public function partner(PartnerType $type = PartnerType::Seller, bool $verified = true): static
    {
        return $this->afterCreating(function (User $u) use ($type, $verified) {
            $u->assignRole(RoleName::Partner->value);

            $profile = PartnerProfile::factory()->state([
                'user_id' => $u->id,
                'partner_type' => $type,
            ]);

            ($verified ? $profile->verified() : $profile)->create();
        });
    }
}
