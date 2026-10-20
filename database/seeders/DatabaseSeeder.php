<?php

namespace Database\Seeders;

use App\Enums\PartnerType;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RoleSeeder::class, FacilitySeeder::class]);

        $this->demoUser('admin@example.com', 'Administrator', fn ($f) => $f->admin());
        $this->demoUser('buyer@example.com', 'Buyer Demo', fn ($f) => $f->buyer());
        $seller = $this->demoUser('seller@example.com', 'Seller Demo', fn ($f) => $f->partner(PartnerType::Seller));
        $this->demoUser('architect@example.com', 'Architect Demo', fn ($f) => $f->partner(PartnerType::Architect));

        // Properti demo hanya dibuat sekali
        if (! Property::where('partner_id', $seller->id)->exists()) {
            Property::factory()
                ->count(15)
                ->published()
                ->create(['partner_id' => $seller->id])
                ->each(fn (Property $p) => PropertyImage::factory()->cover()->create(['property_id' => $p->id]));

            Property::factory()->count(3)->pendingReview()->create(['partner_id' => $seller->id]);
        }
    }

    /** Buat user demo hanya jika email belum ada. */
    private function demoUser(string $email, string $name, callable $state): User
    {
        return User::where('email', $email)->first()
            ?? $state(User::factory())->create(['name' => $name, 'email' => $email]);
    }
}