<?php

namespace App\Livewire\Partner;

use App\Exceptions\ApiException;
use App\Models\ArchitectService;
use App\Services\ArchitectCatalogService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

/** Daftar + formulir layanan milik arsitek. */
class ServiceManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';
    public string $description = '';
    public string $service_type = 'consultation';
    public string $price = '';
    public string $duration_minutes = '60';
    public bool $is_active = true;

    public ?string $notice = null;

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'service_type' => ['required', Rule::in(['consultation', 'design', 'supervision'])],
            'price' => ['required', 'numeric', 'min:1', 'max:99999999999999'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480', 'multiple_of:15'],
            'is_active' => ['boolean'],
        ];
    }

    private function owned(int $id): ArchitectService
    {
        return ArchitectService::where('architect_id', auth()->id())->findOrFail($id);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $service = $this->owned($id);
        Gate::authorize('update', $service);

        $this->resetErrorBag();
        $this->editingId = $service->id;
        $this->title = $service->title;
        $this->description = (string) $service->description;
        $this->service_type = $service->service_type->value;
        $this->price = (string) (int) round((float) $service->price);
        $this->duration_minutes = (string) $service->duration_minutes;
        $this->is_active = $service->is_active;
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->resetErrorBag();
        $this->reset(['editingId', 'title', 'description', 'price', 'showForm']);
        $this->service_type = 'consultation';
        $this->duration_minutes = '60';
        $this->is_active = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title' => trim($this->title),
            'description' => trim($this->description) !== '' ? trim($this->description) : null,
            'service_type' => $this->service_type,
            'price' => (float) $this->price,
            'duration_minutes' => (int) $this->duration_minutes,
            'is_active' => $this->is_active,
        ];

        try {
            $catalog = app(ArchitectCatalogService::class);

            if ($this->editingId === null) {
                $catalog->create(auth()->user(), $data);
                $this->notice = 'Layanan dibuat.';
            } else {
                $service = $this->owned($this->editingId);
                Gate::authorize('update', $service);
                $catalog->update($service, $data);
                $this->notice = 'Layanan diperbarui.';
            }
        } catch (ApiException $e) {
            $this->addError('form', $e->getMessage());

            return;
        }

        $this->resetForm();
    }

    public function toggle(int $id): void
    {
        $service = $this->owned($id);
        Gate::authorize('update', $service);

        app(ArchitectCatalogService::class)->update($service, ['is_active' => ! $service->is_active]);
        $this->notice = $service->is_active ? 'Layanan dinonaktifkan.' : 'Layanan diaktifkan.';
    }

    public function delete(int $id): void
    {
        $service = $this->owned($id);
        Gate::authorize('delete', $service);

        $this->resetErrorBag('list');

        try {
            app(ArchitectCatalogService::class)->delete($service);
            $this->notice = 'Layanan dihapus.';
        } catch (ApiException $e) {
            $this->notice = null;
            $this->addError('list', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.partner.service-manager', [
            'services' => app(ArchitectCatalogService::class)->listForArchitect(auth()->user(), ['per_page' => 10]),
        ]);
    }
}
