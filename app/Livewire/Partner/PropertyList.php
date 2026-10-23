<?php

namespace App\Livewire\Partner;

use App\Exceptions\ApiException;
use App\Models\Property;
use App\Services\PropertySaleService;
use App\Services\PropertyService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PropertyList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $q = '';

    public ?string $notice = null;

    public function updated($name, $value = null): void
    {
        $this->resetPage();
        $this->notice = null;
    }

    private function owned(int $id): Property
    {
        return Property::where('partner_id', auth()->id())->findOrFail($id);
    }

    public function submit(int $id): void
    {
        $property = $this->owned($id);
        Gate::authorize('update', $property);

        $this->run(function () use ($property) {
            app(PropertyService::class)->submit($property);

            return 'Properti diajukan dan menunggu verifikasi administrator.';
        });
    }

    public function delete(int $id): void
    {
        $property = $this->owned($id);
        Gate::authorize('delete', $property);

        $this->run(function () use ($property) {
            app(PropertyService::class)->delete($property);

            return 'Properti dihapus.';
        });
    }

    public function markSold(int $id): void
    {
        $property = $this->owned($id);
        Gate::authorize('manageSale', $property);

        $this->run(function () use ($property) {
            app(PropertySaleService::class)->markSold($property);

            return 'Properti ditandai terjual.';
        });
    }

    public function release(int $id): void
    {
        $property = $this->owned($id);
        Gate::authorize('manageSale', $property);

        $this->run(function () use ($property) {
            app(PropertySaleService::class)->release($property);

            return 'Reservasi dilepas. Properti tayang kembali; pengembalian booking fee ditangani administrator.';
        });
    }

    private function run(callable $action): void
    {
        $this->resetErrorBag();

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

        return view('livewire.partner.property-list', [
            'properties' => app(PropertyService::class)->listForPartner(auth()->user(), $filters),
        ]);
    }
}
