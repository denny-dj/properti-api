<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Concierge\PortfolioIndexRequest;
use App\Http\Resources\PortfolioResource;
use App\Services\ArchitectPortfolioService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/** Publik: etalase portofolio arsitek berbasis estimasi RAB. */
class ArchitectPortfolioController extends Controller
{
    public function __construct(private ArchitectPortfolioService $portfolios)
    {
    }

    public function index(PortfolioIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();

        if ($request->has('is_ready_to_build')) {
            $filters['is_ready_to_build'] = $request->boolean('is_ready_to_build');
        }

        return ApiResponse::success(PortfolioResource::collection($this->portfolios->listPublic($filters)), 'Daftar portofolio arsitek.');
    }

    public function show(string $slug): JsonResponse
    {
        return ApiResponse::success(PortfolioResource::make($this->portfolios->showPublic($slug)), 'Detail portofolio.');
    }
}
