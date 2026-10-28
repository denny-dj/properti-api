<?php

namespace App\Livewire\Auth;

use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Daftar')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    protected function messages(): array
    {
        return ['phone.regex' => 'Format nomor HP tidak valid (contoh: 081234567890).'];
    }

    public function register()
    {
        $data = $this->validate();

        $user = app(AuthService::class)->createBuyer($data);

        Auth::login($user);
        session()->regenerate();

        return redirect()->route('home')->with('status', 'Selamat datang! Akun Anda berhasil dibuat. Kami mengirim tautan verifikasi ke email Anda.');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
