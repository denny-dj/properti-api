<?php

namespace App\Livewire\Partner;

use App\Models\ArchitectBooking;
use App\Models\Property;
use App\Services\PartnerStatsService;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $profile = $user->partnerProfile;
        $type = $profile?->partner_type?->value;
        $approved = (bool) $profile?->isApproved();
        $stats = app(PartnerStatsService::class);

        $isSeller = $approved && $type === 'seller';
        $isArchitect = $approved && $type === 'architect';

        return view('livewire.partner.dashboard', [
            'profile' => $profile,
            'isSeller' => $isSeller,
            'isArchitect' => $isArchitect,
            'seller' => $isSeller ? $stats->seller($user) : null,
            'architect' => $isArchitect ? $stats->architect($user) : null,
            'revenue' => $stats->revenue($user),
            'recentProperties' => $isSeller
                ? Property::where('partner_id', $user->id)->latest()->limit(5)->get()
                : collect(),
            'upcoming' => $isArchitect
                ? ArchitectBooking::with(['service', 'client'])
                    ->where('architect_id', $user->id)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->where('scheduled_start', '>=', now())
                    ->orderBy('scheduled_start')->limit(5)->get()
                : collect(),
        ]);
    }
}
