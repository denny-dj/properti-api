<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian: ->middleware('partner.approved')          (semua tipe partner)
 *            ->middleware('partner.approved:seller')   (khusus seller)
 */
class EnsurePartnerApproved
{
    public function handle(Request $request, Closure $next, ?string $type = null): Response
    {
        $profile = $request->user()?->partnerProfile;

        if (! $profile || ! $profile->isApproved()) {
            return ApiResponse::error('Akun partner Anda belum diverifikasi oleh administrator.', 403);
        }

        if ($type !== null && $profile->partner_type->value !== $type) {
            return ApiResponse::error("Fitur ini hanya untuk partner bertipe {$type}.", 403);
        }

        return $next($request);
    }
}
