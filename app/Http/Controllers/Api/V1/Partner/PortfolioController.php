<?php

namespace App\Http\Controllers\Api\V1\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Concierge\StorePortfolioRequest;
use App\Http\Requests\Concierge\UpdatePortfolioRequest;
use App\Http\Resources\PortfolioResource;
use App\Services\ArchitectPortfolioService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Mitra arsitek (terverifikasi): kelola portofolio sendiri. */
class PortfolioController extends Controller
{
    public function __construct(private ArchitectPortfolioService $portfolios)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            PortfolioResource::collection($this->portfolios->listForArchitect($request->user())),
            'Daftar portofolio Anda.',
        );
    }

    public function store(StorePortfolioRequest $request): JsonResponse
    {
        $portfolio = $this->portfolios->create(
            $request->user(),
            $request->safe()->except(['images', 'cover_index']),
            $request->file('images', []),
            $request->filled('cover_index') ? (int) $request->input('cover_index') : null,
        );

        return ApiResponse::success(PortfolioResource::make($portfolio), 'Portofolio dibuat.', 201);
    }

    public function update(UpdatePortfolioRequest $request, string $id): JsonResponse
    {
        $portfolio = $this->portfolios->update(
            $request->user(),
            (int) $id,
            $request->safe()->except(['images', 'cover_index', 'remove_image_ids']),
            $request->file('images', []),
            array_map('intval', (array) $request->input('remove_image_ids', [])),
            $request->filled('cover_index') ? (int) $request->input('cover_index') : null,
        );

        return ApiResponse::success(PortfolioResource::make($portfolio), 'Portofolio diperbarui.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->portfolios->delete($request->user(), (int) $id);

        return ApiResponse::success(null, 'Portofolio dihapus.');
    }
}
