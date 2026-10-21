<?php

namespace App\Http\Controllers\Api\V1\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Architect\UpdateAvailabilityRequest;
use App\Http\Resources\AvailabilityResource;
use App\Services\AvailabilityService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Jadwal kerja mingguan arsitek. */
class AvailabilityController extends Controller
{
    public function __construct(private AvailabilityService $availabilities)
    {
    }

    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success(
            AvailabilityResource::collection($this->availabilities->get($request->user())),
            'Jadwal ketersediaan.',
        );
    }

    /** Mengganti seluruh jadwal. Kirim array kosong untuk mengosongkan. */
    public function update(UpdateAvailabilityRequest $request): JsonResponse
    {
        return ApiResponse::success(
            AvailabilityResource::collection(
                $this->availabilities->replace($request->user(), $request->validated('availabilities'))
            ),
            'Jadwal ketersediaan diperbarui.',
        );
    }
}
