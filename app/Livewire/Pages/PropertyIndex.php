<?php

namespace App\Livewire\Pages;

use App\Repositories\Contracts\FacilityRepositoryInterface;
use App\Services\PropertyService;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Cari Properti')]
class PropertyIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $property_type = '';

    #[Url(except: '')]
    public string $city = '';

    #[Url(except: '')]
    public string $min_price = '';

    #[Url(except: '')]
    public string $max_price = '';

    #[Url(except: '')]
    public string $bedrooms = '';

    #[Url(except: [])]
    public array $facilities = [];

    #[Url(except: 'newest')]
    public string $sort = 'newest';

    public function updated($name, $value = null): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['q', 'property_type', 'city', 'min_price', 'max_price', 'bedrooms', 'facilities']);
        $this->sort = 'newest';
        $this->resetPage();
    }

    private function filters(): array
    {
        $number = fn (string $v) => is_numeric($v) ? $v : null;

        $filters = [
            'q' => trim($this->q),
            'property_type' => in_array($this->property_type, ['house', 'shop'], true) ? $this->property_type : null,
            'city' => trim($this->city),
            'min_price' => $number($this->min_price),
            'max_price' => $number($this->max_price),
            'bedrooms' => $number($this->bedrooms),
            'facilities' => array_values(array_filter($this->facilities, 'is_string')),
            'sort' => in_array($this->sort, ['newest', 'price_asc', 'price_desc'], true) ? $this->sort : 'newest',
            'per_page' => 12,
        ];

        return array_filter($filters, fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    public function render()
    {
        return view('livewire.pages.property-index', [
            'properties' => app(PropertyService::class)->listPublished($this->filters()),
            'allFacilities' => app(FacilityRepositoryInterface::class)->all(),
        ]);
    }
}
