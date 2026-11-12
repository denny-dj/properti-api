<?php

namespace App\Livewire\Profile;

use App\Enums\PartnerType;
use App\Exceptions\ApiException;
use App\Models\PartnerProfile;
use App\Services\ProfileService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Pengaturan profil terpadu: Profil Saya, Branding Studio/Bisnis (mitra), dan Keamanan. */
class ProfileSettings extends Component
{
    use WithFileUploads;

    private const PHONE_REGEX = '/^(\+62|62|0)8[0-9]{8,12}$/';

    #[Url(as: 'tab', except: 'profil')]
    public string $tab = 'profil';

    // ---- Tab 1: profil ----
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    /** Wajib diisi hanya bila email diganti. */
    public string $emailPassword = '';

    public $avatar = null;

    // ---- Tab 2: bisnis (hanya mitra) ----
    public string $business_name = '';

    public string $business_phone = '';

    public string $city = '';

    public string $address = '';

    public string $description = '';

    public string $license_number = '';

    public string $portfolio_url = '';

    public $logo = null;

    // ---- Tab 3: keamanan ----
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    // ---- pesan per tab ----
    public ?string $accountNotice = null;

    public ?string $businessNotice = null;

    public ?string $passwordNotice = null;

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->phone = (string) $user->phone;

        $this->fillBusiness($user->partnerProfile);
        $this->tab = $this->normalizeTab($this->tab);
    }

    // ------------------------------------------------------------------ tab

    public function setTab(string $tab): void
    {
        $this->tab = $this->normalizeTab($tab);
        $this->resetErrorBag();
        $this->accountNotice = $this->businessNotice = $this->passwordNotice = null;
    }

    public function updatedTab(): void
    {
        $this->tab = $this->normalizeTab($this->tab);
    }

    private function normalizeTab(string $tab): string
    {
        $allowed = ['profil', 'keamanan'];

        if (auth()->user()?->partnerProfile) {
            $allowed[] = 'bisnis';
        }

        return in_array($tab, $allowed, true) ? $tab : 'profil';
    }

    // ------------------------------------------------------------------ tab 1: profil

    public function updatedAvatar(): void
    {
        $this->validateOnly('avatar', ['avatar' => $this->imageRules()], $this->imageMessages('foto'));
    }

    public function saveProfile(): void
    {
        $this->resetErrorBag();
        $this->accountNotice = null;
        $user = auth()->user();

        $this->email = mb_strtolower(trim($this->email));
        $this->phone = preg_replace('/[\s\-().]/', '', $this->phone) ?? '';

        $emailChanged = $this->email !== mb_strtolower((string) $user->email);

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'regex:'.self::PHONE_REGEX, Rule::unique('users', 'phone')->ignore($user->id)],
            'avatar' => $this->imageRules(),
        ];

        // Mengganti email = tindakan sensitif: konfirmasi dengan kata sandi saat ini.
        if ($emailChanged) {
            $rules['emailPassword'] = ['required', 'current_password'];
        }

        $this->validate($rules, [
            'phone.regex' => 'Format nomor HP tidak valid (contoh: 081234567890).',
            'phone.unique' => 'Nomor HP ini sudah dipakai akun lain.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'emailPassword.required' => 'Masukkan kata sandi Anda untuk mengganti email.',
            'emailPassword.current_password' => 'Kata sandi salah.',
        ] + $this->imageMessages('foto'), [
            'name' => 'nama lengkap',
            'email' => 'email',
            'phone' => 'nomor HP',
        ]);

        try {
            $result = app(ProfileService::class)->updateAccount($user, [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
            ], $this->avatar);
        } catch (ApiException $e) {
            $this->addError('avatar', $e->getMessage());

            return;
        }

        $this->reset('avatar', 'emailPassword');
        $this->accountNotice = $result['email_changed']
            ? 'Profil disimpan. Kami mengirim tautan verifikasi ke email baru Anda; selama belum diverifikasi, beberapa layanan dibatasi.'
            : 'Profil berhasil disimpan.';
    }

    public function removeAvatar(): void
    {
        $this->resetErrorBag('avatar');
        $this->reset('avatar');
        $this->accountNotice = null;

        app(ProfileService::class)->removeAvatar(auth()->user());
        $this->accountNotice = 'Foto profil dihapus.';
    }

    // ------------------------------------------------------------------ tab 2: bisnis

    public function updatedLogo(): void
    {
        $this->validateOnly('logo', ['logo' => $this->imageRules()], $this->imageMessages('logo'));
    }

    public function saveBusiness(): void
    {
        $user = auth()->user();
        abort_unless($user->partnerProfile, 403);

        $this->resetErrorBag();
        $this->businessNotice = null;

        $this->business_phone = trim($this->business_phone);
        $this->portfolio_url = trim($this->portfolio_url);

        $this->validate([
            'business_name' => ['required', 'string', 'min:2', 'max:150'],
            'business_phone' => ['nullable', 'string', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:3000'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'portfolio_url' => ['nullable', 'url:http,https', 'max:255'],
            'logo' => $this->imageRules(),
        ], [
            'business_phone.regex' => 'Format nomor telepon bisnis tidak valid (contoh: 021 5551234).',
            'portfolio_url.url' => 'Tautan portofolio harus berupa alamat http:// atau https:// yang valid.',
        ] + $this->imageMessages('logo'), [
            'business_name' => 'nama usaha',
            'business_phone' => 'telepon bisnis',
            'city' => 'kota',
            'address' => 'alamat',
            'description' => 'deskripsi',
            'license_number' => 'nomor registrasi',
            'portfolio_url' => 'tautan portofolio',
        ]);

        try {
            $result = app(ProfileService::class)->updatePartnerProfile($user, [
                'business_name' => $this->business_name,
                'business_phone' => $this->business_phone,
                'city' => $this->city,
                'address' => $this->address,
                'description' => $this->description,
                'license_number' => $this->license_number,
                'portfolio_url' => $this->portfolio_url,
            ], $this->logo);
        } catch (ApiException $e) {
            $this->addError('logo', $e->getMessage());

            return;
        }

        $this->reset('logo');
        $this->businessNotice = $result['reverify']
            ? 'Perubahan disimpan. Karena nama usaha atau nomor registrasi berubah, akun mitra Anda ditinjau ulang oleh admin sebelum dapat dipakai kembali.'
            : 'Profil bisnis berhasil disimpan.';
    }

    public function removeLogo(): void
    {
        abort_unless(auth()->user()->partnerProfile, 403);

        $this->reset('logo');
        app(ProfileService::class)->removeLogo(auth()->user());
        $this->businessNotice = 'Logo dihapus.';
    }

    // ------------------------------------------------------------------ tab 3: keamanan

    public function updatePassword(): void
    {
        $this->resetErrorBag();
        $this->passwordNotice = null;

        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Masukkan kata sandi Anda saat ini.',
            'current_password.current_password' => 'Kata sandi saat ini salah.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.different' => 'Kata sandi baru harus berbeda dari yang lama.',
        ], [
            'current_password' => 'kata sandi saat ini',
            'password' => 'kata sandi baru',
        ]);

        try {
            app(ProfileService::class)->changePassword(auth()->user(), $this->current_password, $this->password);
        } catch (ApiException $e) {
            $this->addError('current_password', $e->getMessage());

            return;
        }

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->passwordNotice = 'Kata sandi diperbarui. Token API lama dicabut.';
    }

    // ------------------------------------------------------------------

    private function fillBusiness(?PartnerProfile $profile): void
    {
        if (! $profile) {
            return;
        }

        $this->business_name = (string) $profile->business_name;
        $this->business_phone = (string) $profile->business_phone;
        $this->city = (string) $profile->city;
        $this->address = (string) $profile->address;
        $this->description = (string) $profile->description;
        $this->license_number = (string) $profile->license_number;
        $this->portfolio_url = (string) $profile->portfolio_url;
    }

    /** @return array<int, string> */
    private function imageRules(): array
    {
        return ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
    }

    /** @return array<string, string> */
    private function imageMessages(string $label): array
    {
        $field = $label === 'logo' ? 'logo' : 'avatar';

        return [
            "{$field}.image" => "Berkas {$label} harus berupa gambar.",
            "{$field}.mimes" => "Format {$label} harus JPG, PNG, atau WEBP.",
            "{$field}.max" => "Ukuran {$label} maksimal 2 MB.",
            "{$field}.uploaded" => "Unggahan {$label} gagal. Coba lagi dengan berkas yang lebih kecil.",
        ];
    }

    public function render()
    {
        $user = auth()->user();
        $profile = $user->partnerProfile;

        return view('livewire.profile.profile-settings', [
            'user' => $user,
            'profile' => $profile,
            'isArchitect' => $profile?->partner_type === PartnerType::Architect,
        ]);
    }
}
