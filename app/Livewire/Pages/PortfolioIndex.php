<?php

namespace App\Livewire\Pages;

use App\Enums\ArchitecturalStyle;
use App\Enums\PortfolioCategory;
use App\Services\ArchitectPortfolioService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PortfolioIndex extends Component
{
    use WithPagination;

    /** Rentang RAB siap pilih: [min, max, label]. */
    public const RAB_RANGES = [
        'lt200' => [null, 200_000_000, 'Di bawah Rp 200 juta'],
        '200-500' => [200_000_000, 500_000_000, 'Rp 200 - 500 juta'],
        '500-1000' => [500_000_000, 1_000_000_000, 'Rp 500 juta - 1 miliar'],
        'gt1000' => [1_000_000_000, null, 'Di atas Rp 1 miliar'],
    ];

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: '')]
    public string $style = '';

    #[Url(except: '')]
    public string $rab = '';

    #[Url(except: '')]
    public string $ready = '';

    #[Url(except: 'newest')]
    public string $sort = 'newest';

    public function updated($name, $value = null): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['q', 'category', 'style', 'rab', 'ready']);
        $this->sort = 'newest';
        $this->resetPage();
    }

    private function filters(): array
    {
        $range = self::RAB_RANGES[$this->rab] ?? null;

        $filters = [
            'q' => trim($this->q),
            'category' => PortfolioCategory::tryFrom($this->category)?->value,
            'style' => ArchitecturalStyle::tryFrom($this->style)?->value,
            'min_rab' => $range[0] ?? null,
            'max_rab' => $range[1] ?? null,
            'is_ready_to_build' => $this->ready === '1' ? true : null,
            'sort' => in_array($this->sort, ['newest', 'rab_asc', 'rab_desc'], true) ? $this->sort : 'newest',
            'per_page' => 9,
        ];

        return array_filter($filters, fn ($v) => $v !== null && $v !== '');
    }

    public function render()
    {
        return view('livewire.pages.portfolio-index', [
            'portfolios' => app(ArchitectPortfolioService::class)->listPublic($this->filters()),
            'categories' => PortfolioCategory::cases(),
            'styles' => ArchitecturalStyle::cases(),
            'rabRanges' => self::RAB_RANGES,
        ]);
    }
}
