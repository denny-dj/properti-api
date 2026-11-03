<?php

namespace App\Repositories\Contracts;

use App\Models\ArchitectPortfolio;
use App\Models\PortfolioImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ArchitectPortfolioRepositoryInterface extends BaseRepositoryInterface
{
    /** Filter: min_rab, max_rab, category, style, is_ready_to_build, q, sort (newest|rab_asc|rab_desc). */
    public function paginatePublic(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug): ?ArchitectPortfolio;

    public function paginateByArchitect(int $architectId, int $perPage = 12): LengthAwarePaginator;

    public function findOwned(int $architectId, int $id): ?ArchitectPortfolio;

    public function addImage(ArchitectPortfolio $portfolio, string $path, bool $isCover = false, ?string $caption = null): PortfolioImage;

    /** @return array<int, string> path berkas yang baris-nya dihapus (untuk dihapus dari storage) */
    public function removeImages(ArchitectPortfolio $portfolio, array $imageIds): array;

    public function countImages(int $portfolioId): int;

    /** Menjadikan gambar pertama sebagai sampul bila belum ada sampul. */
    public function ensureCover(ArchitectPortfolio $portfolio): void;

    public function setCover(ArchitectPortfolio $portfolio, int $imageId): void;
}
