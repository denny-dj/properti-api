<?php

namespace App\Livewire\Auth;

use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Daftar sebagai Mitra')]
class RegisterPartner extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $partner_type = 'seller';

    public string $business_name = '';

    public string $license_number = '';

    public string $city = '';

    public string $address = '';

    public string $description = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'partner_type' => ['required', Rule::in(['seller', 'architect'])],
            'business_name' => ['required', 'string', 'max:150'],
            'license_number' => ['required_if:partner_type,architect', 'nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'phone.regex' => 'Format nomor HP tidak valid (contoh: 081234567890).',
            'license_number.required_if' => 'Nomor izin/keanggotaan wajib diisi untuk arsitek.',
        ];
    }

    public function register()
    {
        $data = $this->validate();

        $user = app(AuthService::class)->createPartner($data);

        Auth::login($user);
        session()->regenerate();

        return redirect()->route('home')->with(
            'status',
            'Pendaftaran mitra berhasil. Akun Anda menunggu verifikasi administrator sebelum dapat memposting.'
        );
    }

    public function render()
    {
        return view('livewire.auth.register-partner');
    }
}
