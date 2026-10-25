<?php

namespace App\Livewire\Admin;

use App\Exceptions\ApiException;
use App\Livewire\Admin\Concerns\RequiresPermission;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PropertyModeration extends Component
{
    use RequiresPermission, WithPagination;

    #[Url]
    public string $status = 'pending_review';

    #[Url(except: '')]
    public string $q = '';

    public ?int $expandedId = null;

    public ?int $rejectingId = null;

    public string $reason = '';

    public ?string $notice = null;

    public function mount(): void
    {
        $this->requirePermission('properties.verify');
    }

    public function updated($name, $value = null): void
    {
        if (in_array($name, ['status', 'q'], true)) {
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
        $this->requirePermission('properties.verify');
        $property = Property::findOrFail($id);

        $this->run(function () use ($property) {
            app(PropertyService::class)->approve($property, auth()->user());

            return 'Properti diverifikasi dan kini tayang.';
        });
    }

    public function startReject(int $id): void
    {
        $this->requirePermission('properties.verify');
        Property::findOrFail($id);

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
        $this->requirePermission('properties.verify');

        if ($this->rejectingId === null) {
            return;
        }

        $this->validate(
            ['reason' => ['required', 'string', 'min:5', 'max:1000']],
            ['reason.required' => 'Alasan penolakan wajib diisi.', 'reason.min' => 'Alasan minimal 5 karakter.'],
        );

        $property = Property::findOrFail($this->rejectingId);
        $reason = $this->reason;

        $this->run(function () use ($property, $reason) {
            app(PropertyService::class)->reject($property, auth()->user(), $reason);

            return 'Properti ditolak. Penjual dapat memperbaiki dan mengajukan kembali.';
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

        if (in_array($this->status, ['draft', 'pending_review', 'published', 'reserved', 'rejected', 'sold'], true)) {
            $filters['status'] = $this->status;
        }

        if (trim($this->q) !== '') {
            $filters['q'] = trim($this->q);
        }

        $service = app(PropertyService::class);
        $detail = null;

        if ($this->expandedId) {
            $model = Property::find($this->expandedId);

            if ($model) {
                Gate::authorize('view', $model);
                $detail = $service->loadDetail($model);
            }
        }

        return view('livewire.admin.property-moderation', [
            'properties' => $service->listForAdmin($filters),
            'detail' => $detail,
        ]);
    }
}
