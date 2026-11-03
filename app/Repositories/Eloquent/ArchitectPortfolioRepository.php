<?php

namespace App\Repositories\Eloquent;

use App\Models\ArchitectPortfolio;
use App\Models\PortfolioImage;
use App\Repositories\Contracts\ArchitectPortfolioRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ArchitectPortfolioRepository extends BaseRepository implements ArchitectPortfolioRepositoryInterface
{
    public function __construct(ArchitectPortfolio $model)
    {
        parent::__construct($model);
    }

    public function paginatePublic(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['architect.partnerProfile', 'coverImage'])
            ->published()
            ->when($filters['min_rab'] ?? null, fn (Builder $q, $v) => $q->where('estimated_rab', '>=', $v))
            ->when($filters['max_rab'] ?? null, fn (Builder $q, $v) => $q->where('estimated_rab', '<=', $v))
            ->when($filters['category'] ?? null, fn (Builder $q, $v) => $q->where('category', $v))
            ->when($filters['style'] ?? null, fn (Builder $q, $v) => $q->where('architectural_style', $v))
            ->when(
                array_key_exists('is_ready_to_build', $filters) && $filters['is_ready_to_build'] !== null,
                fn (Builder $q) => $q->where('is_ready_to_build', (bool) $filters['is_ready_to_build'])
            )
            ->when($filters['q'] ?? null, function (Builder $q, string $term) {
                $like = '%'.$term.'%';
                $q->where(fn (Builder $w) => $w->where('title', 'like', $like)->orWhere('description', 'like', $like));
            });

        match ($filters['sort'] ?? 'newest') {
            'rab_asc' => $query->orderBy('estimated_rab')->orderByDesc('id'),
            'rab_desc' => $query->orderByDesc('estimated_rab')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function findPublishedBySlug(string $slug): ?ArchitectPortfolio
    {
        return $this->model->newQuery()
            ->with(['architect.partnerProfile', 'images'])
            ->published()
            ->where('slug', $slug)
            ->first();
    }

    public function paginateByArchitect(int $architectId, int $perPage = 12): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['coverImage'])
            ->where('user_id', $architectId)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOwned(int $architectId, int $id): ?ArchitectPortfolio
    {
        return $this->model->newQuery()
            ->with(['images'])
            ->where('user_id', $architectId)
            ->whereKey($id)
            ->first();
    }

    public function addImage(ArchitectPortfolio $portfolio, string $path, bool $isCover = false, ?string $caption = null): PortfolioImage
    {
        if ($isCover) {
            PortfolioImage::where('portfolio_id', $portfolio->id)->update(['is_cover' => false]);
        }

        return PortfolioImage::create([
            'portfolio_id' => $portfolio->id,
            'image_path' => $path,
            'is_cover' => $isCover,
            'caption' => $caption,
        ]);
    }

    public function removeImages(ArchitectPortfolio $portfolio, array $imageIds): array
    {
        $images = PortfolioImage::where('portfolio_id', $portfolio->id)->whereIn('id', $imageIds)->get();

        if ($images->isEmpty()) {
            return [];
        }

        PortfolioImage::whereIn('id', $images->pluck('id')->all())->delete();

        return $images->pluck('image_path')->all();
    }

    public function countImages(int $portfolioId): int
    {
        return PortfolioImage::where('portfolio_id', $portfolioId)->count();
    }

    public function ensureCover(ArchitectPortfolio $portfolio): void
    {
        if (PortfolioImage::where('portfolio_id', $portfolio->id)->where('is_cover', true)->exists()) {
            return;
        }

        PortfolioImage::where('portfolio_id', $portfolio->id)->orderBy('id')->first()?->update(['is_cover' => true]);
    }

    public function setCover(ArchitectPortfolio $portfolio, int $imageId): void
    {
        PortfolioImage::where('portfolio_id', $portfolio->id)->update(['is_cover' => false]);
        PortfolioImage::where('portfolio_id', $portfolio->id)->whereKey($imageId)->update(['is_cover' => true]);
    }
}
