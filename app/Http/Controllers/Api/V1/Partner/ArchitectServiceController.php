<?php

namespace App\Http\Controllers\Api\V1\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Architect\StoreArchitectServiceRequest;
use App\Http\Requests\Architect\UpdateArchitectServiceRequest;
use App\Http\Resources\ArchitectServiceResource;
use App\Models\ArchitectService;
use App\Services\ArchitectCatalogService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** CRUD layanan milik arsitek yang sedang login. */
class ArchitectServiceController extends Controller
{
    public function __construct(private ArchitectCatalogService $catalog)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            ArchitectServiceResource::collection($this->catalog->listForArchitect($request->user(), $request->only('per_page'))),
            'Daftar layanan Anda.',
        );
    }

    public function store(StoreArchitectServiceRequest $request): JsonResponse
    {
        return ApiResponse::success(
            ArchitectServiceResource::make($this->catalog->create($request->user(), $request->validated())),
            'Layanan dibuat.',
            201,
        );
    }

    public function show(ArchitectService $architectService): JsonResponse
    {
        Gate::authorize('view', $architectService);

        return ApiResponse::success(ArchitectServiceResource::make($architectService), 'Detail layanan.');
    }

    public function update(UpdateArchitectServiceRequest $request, ArchitectService $architectService): JsonResponse
    {
        Gate::authorize('update', $architectService);

        return ApiResponse::success(
            ArchitectServiceResource::make($this->catalog->update($architectService, $request->validated())),
            'Layanan diperbarui.',
        );
    }

    public function destroy(ArchitectService $architectService): JsonResponse
    {
        Gate::authorize('delete', $architectService);

        $this->catalog->delete($architectService);

        return ApiResponse::success(null, 'Layanan dihapus.');
    }
}
