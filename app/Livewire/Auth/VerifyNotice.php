<?php

namespace App\Livewire\Auth;

use App\Support\Notifier;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class VerifyNotice extends Component
{
    public bool $sent = false;

    public ?string $error = null;

    public function resend()
    {
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $key = 'verify-resend:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $this->error = 'Tunggu '.RateLimiter::availableIn($key).' detik sebelum meminta tautan baru.';
            $this->sent = false;

            return;
        }

        RateLimiter::hit($key, 60);

        Notifier::verification($user);

        $this->error = null;
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.verify-notice', ['verified' => auth()->user()->hasVerifiedEmail()]);
    }
}
