<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Map\GeocodeRequest;
use App\Http\Requests\Map\MapMarkersRequest;
use App\Http\Requests\Map\ReverseGeocodeRequest;
use App\Http\Resources\PropertyMarkerResource;
use App\Services\MapService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class MapController extends Controller
{
    public function __construct(private MapService $maps)
    {
    }

    /** Publik: pin properti untuk tampilan peta. */
    public function markers(MapMarkersRequest $request): JsonResponse
    {
        return ApiResponse::success(
            PropertyMarkerResource::collection($this->maps->markers($request->validated())),
            'Marker properti.',
        );
    }

    /** Terautentikasi: alamat -> koordinat (untuk form posting listing). */
    public function geocode(GeocodeRequest $request): JsonResponse
    {
        return ApiResponse::success($this->maps->geocode($request->validated('address')), 'Hasil geocoding.');
    }

    /** Terautentikasi: koordinat (pin yang digeser) -> alamat. */
    public function reverseGeocode(ReverseGeocodeRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->maps->reverseGeocode((float) $request->validated('lat'), (float) $request->validated('lng')),
            'Hasil reverse geocoding.',
        );
    }
}
