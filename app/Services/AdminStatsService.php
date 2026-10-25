<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Angka untuk dashboard admin dan lencana menu (query baca-saja). */
class AdminStatsService
{
    /** Jumlah antrean yang menunggu tindakan admin. */
    public function badges(): array
    {
        return [
            'partners' => DB::table('partner_profiles')->where('verification_status', 'pending')->count(),
            'properties' => DB::table('properties')->whereNull('deleted_at')->where('status', 'pending_review')->count(),
            'transactions' => DB::table('transactions')->where('needs_review', true)->count(),
        ];
    }

    public function overview(): array
    {
        $countBy = fn (string $table, string $column, bool $softDeletes = false) => (function () use ($table, $column, $softDeletes) {
            $query = DB::table($table);
            if ($softDeletes) {
                $query->whereNull('deleted_at');
            }

            return $query->selectRaw("{$column} as k, count(*) as c")->groupBy($column)->pluck('c', 'k')
                ->map(fn ($c) => (int) $c)->all();
        })();

        $roles = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', (new User())->getMorphClass())
            ->selectRaw('roles.name as k, count(*) as c')
            ->groupBy('roles.name')
            ->pluck('c', 'k')
            ->map(fn ($c) => (int) $c)
            ->all();

        $paid = fn () => DB::table('transactions')->where('status', 'paid');

        return [
            'users' => [
                'total' => DB::table('users')->whereNull('deleted_at')->count(),
                'suspended' => DB::table('users')->whereNull('deleted_at')->where('status', 'suspended')->count(),
                'by_role' => $roles,
            ],
            'properties' => $countBy('properties', 'status', true),
            'bookings' => $countBy('architect_bookings', 'status'),
            'revenue' => [
                'paid_total' => (float) $paid()->sum('amount'),
                'paid_count' => $paid()->count(),
                'month_total' => (float) $paid()->where('paid_at', '>=', now()->startOfMonth())->sum('amount'),
                'pending_total' => (float) DB::table('transactions')->where('status', 'pending')->sum('amount'),
            ],
        ];
    }
}
