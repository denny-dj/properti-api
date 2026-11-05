<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menutup fitur lama (chat langsung, konsultasi per jam, bayar mandiri) saat CONCIERGE_LEGACY_UI=false.
 * Web dialihkan ke halaman pengganti; API menjawab 410 Gone. Data lama tidak dihapus.
 * Pemakaian: EnsureLegacyUiEnabled::class.':nama.rute.pengganti'
 */
class EnsureLegacyUiEnabled
{
    public const MESSAGE = 'Fitur ini telah digantikan layanan concierge. Hubungi admin kami lewat tombol WhatsApp pada halaman properti atau portofolio.';

    public function handle(Request $request, Closure $next, ?string $fallback = null): Response
    {
        if (config('concierge.legacy_ui', true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return ApiResponse::error(self::MESSAGE, 410);
        }

        $target = $fallback !== null && Route::has($fallback) ? route($fallback) : route('home');

        return redirect($target)->with('status', self::MESSAGE);
    }
}
