<?php

namespace App\Livewire\Pages;

use App\Services\ArchitectCatalogService;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Jasa Arsitek')]
class ArchitectIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $service_type = '';

    #[Url(except: '')]
    public string $city = '';

    #[Url(except: '')]
    public string $max_price = '';

    #[Url(except: 'newest')]
    public string $sort = 'newest';

    public function updated($name, $value = null): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['q', 'service_type', 'city', 'max_price']);
        $this->sort = 'newest';
        $this->resetPage();
    }

    private function filters(): array
    {
        $filters = [
            'q' => trim($this->q),
            'service_type' => in_array($this->service_type, ['consultation', 'design', 'supervision'], true) ? $this->service_type : null,
            'city' => trim($this->city),
            'max_price' => is_numeric($this->max_price) ? $this->max_price : null,
            'sort' => in_array($this->sort, ['newest', 'price_asc', 'price_desc'], true) ? $this->sort : 'newest',
            'per_page' => 9,
        ];

        return array_filter($filters, fn ($v) => $v !== null && $v !== '');
    }

    public function render()
    {
        return view('livewire.pages.architect-index', [
            'services' => app(ArchitectCatalogService::class)->listPublic($this->filters()),
        ]);
    }
}
