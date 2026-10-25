<?php

namespace App\Livewire\Admin;

use App\Exceptions\ApiException;
use App\Livewire\Admin\Concerns\RequiresPermission;
use App\Models\PartnerProfile;
use App\Services\PartnerService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PartnerVerification extends Component
{
    use RequiresPermission, WithPagination;

    #[Url]
    public string $status = 'pending';

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: '')]
    public string $q = '';

    public ?int $expandedId = null;

    public ?int $rejectingId = null;

    public string $reason = '';

    public ?string $notice = null;

    public function mount(): void
    {
        $this->requirePermission('partners.verify');
    }

    public function updated($name, $value = null): void
    {
        if (in_array($name, ['status', 'type', 'q'], true)) {
            $this->resetPage();
            $this->expandedId = null;
        }
    }

    public function toggle(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function approve(int $id): void
    {
        $this->requirePermission('partners.verify');
        $profile = PartnerProfile::findOrFail($id);

        $this->run(function () use ($profile) {
            app(PartnerService::class)->approve($profile, auth()->user());

            return 'Mitra berhasil diverifikasi.';
        });
    }

    public function startReject(int $id): void
    {
        $this->requirePermission('partners.verify');
        PartnerProfile::findOrFail($id);

        $this->resetErrorBag();
        $this->rejectingId = $id;
        $this->reason = '';
    }

    public function cancelReject(): void
    {
        $this->reset(['rejectingId', 'reason']);
        $this->resetErrorBag();
    }

    public function reject(): void
    {
        $this->requirePermission('partners.verify');

        if ($this->rejectingId === null) {
            return;
        }

        $this->validate(
            ['reason' => ['required', 'string', 'min:5', 'max:1000']],
            ['reason.required' => 'Alasan penolakan wajib diisi.', 'reason.min' => 'Alasan minimal 5 karakter.'],
        );

        $profile = PartnerProfile::findOrFail($this->rejectingId);
        $reason = $this->reason;

        $this->run(function () use ($profile, $reason) {
            app(PartnerService::class)->reject($profile, auth()->user(), $reason);

            return 'Pendaftaran mitra ditolak.';
        });

        $this->reset(['rejectingId', 'reason']);
    }

    private function run(callable $action): void
    {
        $this->resetErrorBag('list');

        try {
            $this->notice = $action();
        } catch (ApiException $e) {
            $this->notice = null;
            $this->addError('list', $e->getMessage());
        }
    }

    public function render()
    {
        $filters = ['per_page' => 10];

        if (in_array($this->status, ['pending', 'approved', 'rejected'], true)) {
            $filters['status'] = $this->status;
        }

        if (in_array($this->type, ['seller', 'architect'], true)) {
            $filters['partner_type'] = $this->type;
        }

        if (trim($this->q) !== '') {
            $filters['q'] = trim($this->q);
        }

        return view('livewire.admin.partner-verification', [
            'profiles' => app(PartnerService::class)->list($filters),
        ]);
    }
}
