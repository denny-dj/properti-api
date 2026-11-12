<?php

namespace Tests\Unit;

use App\Models\PartnerProfile;
use App\Models\User;
use App\Support\AvatarGenerator;
use App\Support\ImageUploader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class AvatarAndUploaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_initials_are_built_from_the_first_two_words(): void
    {
        $this->assertSame('BS', AvatarGenerator::initials('Budi Santoso'));
        $this->assertSame('BS', AvatarGenerator::initials('  budi   santoso  prasetyo '));
        $this->assertSame('M', AvatarGenerator::initials('madonna'));
        $this->assertSame('?', AvatarGenerator::initials('   '));
    }

    public function test_data_uri_is_a_safe_local_svg_with_escaped_text(): void
    {
        $uri = AvatarGenerator::dataUri('Budi Santoso');
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $uri);

        $svg = base64_decode(substr($uri, strlen('data:image/svg+xml;base64,')));
        $this->assertStringContainsString('>BS</text>', $svg);
        $this->assertSame($uri, AvatarGenerator::dataUri('Budi Santoso'));          // warna konsisten per nama

        $hostile = base64_decode(substr(AvatarGenerator::dataUri('<script>alert(1)</script>'), 26));
        $this->assertStringNotContainsString('<script>', $hostile);
    }

    public function test_user_and_partner_profile_expose_url_helpers(): void
    {
        Storage::fake('public');

        $user = new User(['name' => 'Budi Santoso']);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $user->avatar_url);
        $this->assertFalse($user->hasCustomAvatar());

        $user->avatar = 'avatars/abc.jpg';
        $this->assertSame(Storage::disk('public')->url('avatars/abc.jpg'), $user->avatar_url);
        $this->assertTrue($user->hasCustomAvatar());

        $user->avatar = 'https://cdn.example.com/a.jpg';
        $this->assertSame('https://cdn.example.com/a.jpg', $user->avatar_url);

        $profile = new PartnerProfile(['business_name' => 'Studio Garis']);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $profile->logo_url);
        $profile->logo = 'logos/x.png';
        $this->assertSame(Storage::disk('public')->url('logos/x.png'), $profile->logo_url);
    }

    public function test_uploader_only_deletes_files_in_managed_folders(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/a.jpg', 'x');
        Storage::disk('public')->put('properties/p.jpg', 'x');

        $uploader = new ImageUploader();
        $uploader->delete('properties/p.jpg');
        $uploader->delete('../.env');
        $uploader->delete('https://cdn.example.com/a.jpg');
        $uploader->delete(null);
        Storage::disk('public')->assertExists('properties/p.jpg');

        $uploader->delete('avatars/a.jpg');
        Storage::disk('public')->assertMissing('avatars/a.jpg');
    }

    public function test_uploader_rejects_unknown_folders(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ImageUploader())->storeSquare(\Illuminate\Http\UploadedFile::fake()->image('a.jpg'), 'rahasia');
    }
}
