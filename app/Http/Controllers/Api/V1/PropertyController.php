<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyIndexRequest;
use App\Http\Resources\PropertyResource;
use App\Services\PropertyService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/** Endpoint publik: listing & detail properti yang sudah terverifikasi. */
class PropertyController extends Controller
{
    public function __construct(private PropertyService $properties)
    {
    }

    public function index(PropertyIndexRequest $request): JsonResponse
    {
        $paginator = $this->properties->listPublished($request->validated());

        return ApiResponse::success(PropertyResource::collection($paginator), 'Daftar properti.');
    }

    public function show(string $slug): JsonResponse
    {
        return ApiResponse::success(
            PropertyResource::make($this->properties->showPublished($slug)),
            'Detail properti.',
        );
    }
}
