<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Angka ringkasan untuk dashboard mitra (query baca-saja). */
class PartnerStatsService
{
    /** @return array{by_status: array<string,int>, total: int, views: int} */
    public function seller(User $partner): array
    {
        $base = fn () => DB::table('properties')->where('partner_id', $partner->id)->whereNull('deleted_at');

        $byStatus = $base()
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->map(fn ($c) => (int) $c)
            ->all();

        return [
            'by_status' => $byStatus,
            'total' => array_sum($byStatus),
            'views' => (int) $base()->sum('views_count'),
        ];
    }

    /** @return array{pending: int, upcoming: int, completed: int, services: int} */
    public function architect(User $partner): array
    {
        $bookings = fn () => DB::table('architect_bookings')->where('architect_id', $partner->id);

        return [
            'pending' => $bookings()->where('status', 'pending')->count(),
            'upcoming' => $bookings()->where('status', 'confirmed')->where('scheduled_start', '>=', now())->count(),
            'completed' => $bookings()->where('status', 'completed')->count(),
            'services' => DB::table('architect_services')->where('architect_id', $partner->id)->whereNull('deleted_at')->count(),
        ];
    }

    /** @return array{paid: float, pending: float} */
    public function revenue(User $partner): array
    {
        $base = fn () => DB::table('transactions')->where('payee_id', $partner->id);

        return [
            'paid' => (float) $base()->where('status', 'paid')->sum('amount'),
            'pending' => (float) $base()->where('status', 'pending')->sum('amount'),
        ];
    }
}
