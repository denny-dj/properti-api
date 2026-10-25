<?php

namespace App\Livewire\Admin;

use App\Models\PartnerProfile;
use App\Models\Property;
use App\Services\AdminStatsService;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $stats = app(AdminStatsService::class);

        return view('livewire.admin.dashboard', [
            'badges' => $stats->badges(),
            'overview' => $stats->overview(),
            'pendingPartners' => PartnerProfile::with('user')
                ->where('verification_status', 'pending')->latest()->limit(5)->get(),
            'pendingProperties' => Property::with('partner')
                ->where('status', 'pending_review')->latest()->limit(5)->get(),
        ]);
    }
}
