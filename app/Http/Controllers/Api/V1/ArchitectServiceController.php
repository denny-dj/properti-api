<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Architect\ArchitectServiceIndexRequest;
use App\Http\Requests\Architect\SlotsRequest;
use App\Http\Resources\ArchitectServiceResource;
use App\Services\ArchitectCatalogService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/** Endpoint publik: katalog layanan arsitek + slot jadwal kosong. */
class ArchitectServiceController extends Controller
{
    public function __construct(private ArchitectCatalogService $catalog)
    {
    }

    public function index(ArchitectServiceIndexRequest $request): JsonResponse
    {
        return ApiResponse::success(
            ArchitectServiceResource::collection($this->catalog->listPublic($request->validated())),
            'Daftar layanan arsitek.',
        );
    }

    public function show(string $slug): JsonResponse
    {
        return ApiResponse::success(
            ArchitectServiceResource::make($this->catalog->showPublic($slug)),
            'Detail layanan arsitek.',
        );
    }

    public function slots(SlotsRequest $request, string $slug): JsonResponse
    {
        return ApiResponse::success(
            $this->catalog->slots($slug, $request->validated('date')),
            'Slot jadwal tersedia.',
        );
    }
}
