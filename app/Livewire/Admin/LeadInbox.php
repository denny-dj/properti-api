<?php

namespace App\Livewire\Admin;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Exceptions\ApiException;
use App\Services\ConciergeLeadService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Kotak masuk prospek concierge: tindak lanjut WhatsApp, ubah status, catatan. */
class LeadInbox extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $source = '';

    public ?int $editing = null;

    public string $newStatus = '';

    public string $newNote = '';

    public ?string $notice = null;

    public function updated($name): void
    {
        if (in_array($name, ['q', 'status', 'source'], true)) {
            $this->resetPage();
        }
    }

    public function startEdit(int $id, string $currentStatus): void
    {
        $this->resetErrorBag();
        $this->editing = $id;
        $this->newStatus = in_array($currentStatus, ['contacted', 'surveying', 'committed', 'lost'], true) ? $currentStatus : 'contacted';
        $this->newNote = '';
    }

    public function cancelEdit(): void
    {
        $this->editing = null;
        $this->resetErrorBag();
    }

    public function saveStatus(): void
    {
        $this->resetErrorBag();
        $this->notice = null;

        if ($this->editing === null) {
            return;
        }

        $this->validate([
            'newStatus' => ['required', 'in:contacted,surveying,committed,lost'],
            'newNote' => ['nullable', 'string', 'max:1000'],
        ], [], ['newStatus' => 'status', 'newNote' => 'catatan']);

        try {
            app(ConciergeLeadService::class)->updateStatus($this->editing, auth()->user(), $this->newStatus, $this->newNote);
        } catch (ApiException $e) {
            $this->addError('list', $e->getMessage());

            return;
        }

        $this->notice = 'Prospek diperbarui.';
        $this->editing = null;
        $this->newNote = '';
    }

    public function render()
    {
        $service = app(ConciergeLeadService::class);

        $filters = array_filter([
            'q' => trim($this->q),
            'status' => LeadStatus::tryFrom($this->status)?->value,
            'source' => LeadSource::tryFrom($this->source)?->value,
            'per_page' => 15,
        ], fn ($v) => $v !== null && $v !== '');

        $leads = $service->list($filters);

        return view('livewire.admin.lead-inbox', [
            'leads' => $leads,
            'refs' => $service->referenceTitles($leads->items()),
        ]);
    }
}
