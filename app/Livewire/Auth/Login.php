<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Masuk')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function login()
    {
        $this->validate();

        $key = 'login:'.Str::lower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.',
            ]);
        }

        // 'status' => 'active' : akun yang disuspend admin tidak bisa masuk
        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password, 'status' => 'active'], $this->remember)) {
            RateLimiter::hit($key);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah, atau akun dinonaktifkan.',
            ]);
        }

        RateLimiter::clear($key);
        session()->regenerate();

        $user = Auth::user();
        $home = match (true) {
            $user->hasRole('admin') => route('admin.dashboard'),
            $user->hasRole('partner') => route('partner.dashboard'),
            default => route('home'),
        };

        return redirect()->intended($home);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
