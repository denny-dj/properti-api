<?php

namespace App\Repositories\Contracts;

use App\Models\AdBanner;
use Illuminate\Database\Eloquent\Collection;

interface AdBannerRepositoryInterface extends BaseRepositoryInterface
{
    /** Banner yang aktif dan sedang dalam masa tayang. */
    public function live(?string $placement = null): Collection;

    public function findLive(int $id): ?AdBanner;

    public function incrementImpressions(array $ids): void;

    public function incrementClicks(int $id): void;
}
