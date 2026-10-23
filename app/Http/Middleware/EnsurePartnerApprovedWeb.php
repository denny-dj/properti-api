<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Versi web dari 'partner.approved': mengalihkan ke dashboard (bukan JSON) bila akun mitra
 * belum diverifikasi atau jenisnya tidak sesuai. Pemakaian: EnsurePartnerApprovedWeb::class.':seller'
 */
class EnsurePartnerApprovedWeb
{
    public function handle(Request $request, Closure $next, string $type): Response
    {
        $profile = $request->user()?->partnerProfile;

        if (! $profile || ! $profile->isApproved()) {
            return redirect()->route('partner.dashboard')
                ->with('status', 'Fitur ini aktif setelah akun mitra Anda diverifikasi administrator.');
        }

        if ($profile->partner_type->value !== $type) {
            return redirect()->route('partner.dashboard')
                ->with('status', 'Fitur ini tidak tersedia untuk jenis akun mitra Anda.');
        }

        return $next($request);
    }
}
