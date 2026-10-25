<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menghentikan sesi web akun yang disuspend admin pada request berikutnya.
 * Dipasang di grup middleware "web" (lihat bootstrap/app.php).
 */
class EnsureUserIsActiveWeb
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isActive()) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Permintaan Livewire (XHR): beri sinyal "sesi berakhir" agar halaman dimuat ulang.
            if ($request->hasHeader('X-Livewire')) {
                abort(419);
            }

            return redirect()->route('login')
                ->with('status', 'Akun Anda dinonaktifkan. Hubungi administrator.');
        }

        return $next($request);
    }
}