<?php

namespace App\Services;

use App\Enums\PortfolioStatus;
use App\Exceptions\ApiException;
use App\Models\ArchitectPortfolio;
use App\Models\User;
use App\Repositories\Contracts\ArchitectPortfolioRepositoryInterface;
use App\Repositories\Contracts\ConciergeTransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ArchitectPortfolioService
{
    public function __construct(
        private ArchitectPortfolioRepositoryInterface $portfolios,
        private ConciergeTransactionRepositoryInterface $transactions,
    ) {
    }

    // ---------- Publik ----------

    public function listPublic(array $filters): LengthAwarePaginator
    {
        return $this->portfolios->paginatePublic($filters, (int) ($filters['per_page'] ?? 12));
    }

    public function showPublic(string $slug): ArchitectPortfolio
    {
        return $this->portfolios->findPublishedBySlug($slug)
            ?? throw new ApiException('Portofolio tidak ditemukan.', 404);
    }

    // ---------- Mitra ----------

    public function listForArchitect(User $architect, int $perPage = 12): LengthAwarePaginator
    {
        return $this->portfolios->paginateByArchitect($architect->id, $perPage);
    }

    /** @param  array<int, UploadedFile>  $images */
    public function create(User $architect, array $data, array $images = [], ?int $coverIndex = null): ArchitectPortfolio
    {
        $stored = [];

        try {
            return DB::transaction(function () use ($architect, $data, $images, $coverIndex, &$stored) {
                $payload = $this->prepare($data);
                $payload['user_id'] = $architect->id;
                $payload['status'] = $payload['status'] ?? PortfolioStatus::Draft->value;

                /** @var ArchitectPortfolio $portfolio */
                $portfolio = $this->portfolios->create($payload);

                $this->storeImages($portfolio, $images, $coverIndex, $stored);
                $this->assertInvariants($portfolio->refresh());

                return $portfolio->load(['images', 'architect.partnerProfile']);
            });
        } catch (Throwable $e) {
            $this->discard($stored);

            throw $e;
        }
    }

    /**
     * @param  array<int, UploadedFile>  $images
     * @param  array<int, int>  $removeImageIds
     */
    public function update(User $architect, int $id, array $data, array $images = [], array $removeImageIds = [], ?int $coverIndex = null): ArchitectPortfolio
    {
        $portfolio = $this->owned($architect, $id);
        $stored = [];
        $removedPaths = [];

        try {
            $result = DB::transaction(function () use ($portfolio, $data, $images, $removeImageIds, $coverIndex, &$stored, &$removedPaths) {
                $payload = $this->prepare($data, $portfolio);

                if ($payload !== []) {
                    /** @var ArchitectPortfolio $portfolio */
                    $portfolio = $this->portfolios->update($portfolio, $payload);
                }

                if ($removeImageIds !== []) {
                    $removedPaths = $this->portfolios->removeImages($portfolio, $removeImageIds);
                }

                $this->storeImages($portfolio, $images, $coverIndex, $stored);
                $this->portfolios->ensureCover($portfolio);
                $this->assertInvariants($portfolio->refresh());

                return $portfolio->load(['images', 'architect.partnerProfile']);
            });
        } catch (Throwable $e) {
            $this->discard($stored);

            throw $e;
        }

        $this->discard($removedPaths); // berkas lama dihapus hanya setelah transaksi berhasil

        return $result;
    }

    public function delete(User $architect, int $id): void
    {
        $portfolio = $this->owned($architect, $id);

        if ($this->transactions->hasOpenTransactions($portfolio->id)) {
            throw new ApiException('Portofolio masih memiliki tagihan aktif atau berkas yang belum dikonfirmasi pembeli. Arsipkan, jangan dihapus.', 422);
        }

        $this->portfolios->delete($portfolio); // soft delete
    }

    /** Portofolio milik arsitek ini (beserta foto) atau ApiException 404. */
    public function findOwnedOrFail(User $architect, int $id): ArchitectPortfolio
    {
        return $this->owned($architect, $id);
    }

    public function setCover(User $architect, int $portfolioId, int $imageId): void
    {
        $portfolio = $this->owned($architect, $portfolioId);

        if (! $portfolio->images->contains('id', $imageId)) {
            throw new ApiException('Foto tidak ditemukan.', 404);
        }

        $this->portfolios->setCover($portfolio, $imageId);
    }

    // ------------------------------------------------------------------

    private function owned(User $architect, int $id): ArchitectPortfolio
    {
        return $this->portfolios->findOwned($architect->id, $id)
            ?? throw new ApiException('Portofolio tidak ditemukan.', 404);
    }

    /** Normalisasi input; cost_per_sqm selalu dihitung server (RAB / luas). */
    private function prepare(array $data, ?ArchitectPortfolio $current = null): array
    {
        $payload = array_intersect_key($data, array_flip([
            'title', 'category', 'architectural_style', 'estimated_rab', 'area_sqm', 'description',
            'is_ready_to_build', 'blueprint_price', 'blueprint_deliverables', 'status',
        ]));

        if (array_key_exists('blueprint_deliverables', $payload) && $payload['blueprint_deliverables'] !== null) {
            $payload['blueprint_deliverables'] = array_values(array_filter(
                array_map(fn ($v) => trim((string) $v), (array) $payload['blueprint_deliverables']),
                fn (string $v) => $v !== '',
            ));
        }

        if (array_key_exists('estimated_rab', $payload) || array_key_exists('area_sqm', $payload)) {
            $rab = (float) ($payload['estimated_rab'] ?? $current?->estimated_rab ?? 0);
            $area = (float) ($payload['area_sqm'] ?? $current?->area_sqm ?? 0);

            if ($area <= 0) {
                throw new ApiException('Luas bangunan (m²) harus lebih dari 0.', 422);
            }

            $payload['cost_per_sqm'] = (int) round($rab / $area);
        }

        // Bukan paket siap bangun: kosongkan data paket agar tidak menyesatkan.
        if (array_key_exists('is_ready_to_build', $payload) && ! $payload['is_ready_to_build']) {
            $payload['blueprint_price'] = null;
            $payload['blueprint_deliverables'] = null;
        }

        return $payload;
    }

    /** Aturan pada kondisi akhir (berlaku untuk create maupun update parsial). */
    private function assertInvariants(ArchitectPortfolio $portfolio): void
    {
        if ($portfolio->is_ready_to_build) {
            if (! $portfolio->blueprint_price || $portfolio->blueprint_price < 1) {
                throw new ApiException('Paket siap bangun wajib memiliki harga paket (blueprint_price).', 422);
            }

            if (empty($portfolio->blueprint_deliverables)) {
                throw new ApiException('Paket siap bangun wajib mencantumkan daftar berkas yang diserahkan (blueprint_deliverables).', 422);
            }
        }

        if ($portfolio->status === PortfolioStatus::Published && $this->portfolios->countImages($portfolio->id) < 1) {
            throw new ApiException('Unggah minimal 1 foto sebelum menayangkan portofolio.', 422);
        }
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @param  array<int, string>  $stored  diisi path yang tersimpan (untuk pembersihan bila gagal)
     */
    private function storeImages(ArchitectPortfolio $portfolio, array $files, ?int $coverIndex, array &$stored): void
    {
        if ($files === []) {
            return;
        }

        $existing = $this->portfolios->countImages($portfolio->id);

        if ($existing + count($files) > 10) {
            throw new ApiException('Maksimal 10 foto per portofolio.', 422);
        }

        foreach (array_values($files) as $index => $file) {
            $path = $file->store("portfolios/{$portfolio->id}", 'public');
            $stored[] = $path;

            $this->portfolios->addImage($portfolio, $path, $coverIndex !== null && $coverIndex === $index);
        }

        $this->portfolios->ensureCover($portfolio);
    }

    /** @param  array<int, string>  $paths */
    private function discard(array $paths): void
    {
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }
    }
}
