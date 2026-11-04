<?php

namespace App\Livewire\Partner;

use App\Exceptions\ApiException;
use App\Services\ArchitectPortfolioService;
use Livewire\Component;
use Livewire\WithPagination;

/** Daftar portofolio milik arsitek: ubah status tayang, hapus. */
class PortfolioList extends Component
{
    use WithPagination;

    public ?string $notice = null;

    public function setStatus(int $id, string $status): void
    {
        $this->resetErrorBag('list');
        $this->notice = null;

        if (! in_array($status, ['draft', 'published', 'archived'], true)) {
            return;
        }

        try {
            app(ArchitectPortfolioService::class)->update(auth()->user(), $id, ['status' => $status]);
            $this->notice = match ($status) {
                'published' => 'Portofolio ditayangkan.',
                'archived' => 'Portofolio diarsipkan dan tidak tampil di etalase.',
                default => 'Portofolio dikembalikan ke draft.',
            };
        } catch (ApiException $e) {
            $this->addError('list', $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        $this->resetErrorBag('list');
        $this->notice = null;

        try {
            app(ArchitectPortfolioService::class)->delete(auth()->user(), $id);
            $this->notice = 'Portofolio dihapus.';
        } catch (ApiException $e) {
            $this->addError('list', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.partner.portfolio-list', [
            'portfolios' => app(ArchitectPortfolioService::class)->listForArchitect(auth()->user(), 10),
        ]);
    }
}
