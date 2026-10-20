<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectRequest;
use App\Http\Requests\Property\PropertyIndexRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Services\PropertyService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(private PropertyService $properties)
    {
    }

    public function index(PropertyIndexRequest $request): JsonResponse
    {
        return ApiResponse::success(
            PropertyResource::collection($this->properties->listForAdmin($request->validated())),
            'Daftar properti (admin).',
        );
    }

    public function show(Property $property): JsonResponse
    {
        return ApiResponse::success(
            PropertyResource::make($this->properties->loadDetail($property)),
            'Detail properti.',
        );
    }

    public function approve(Request $request, Property $property): JsonResponse
    {
        return ApiResponse::success(
            PropertyResource::make($this->properties->approve($property, $request->user())),
            'Properti diverifikasi dan tayang.',
        );
    }

    public function reject(RejectRequest $request, Property $property): JsonResponse
    {
        return ApiResponse::success(
            PropertyResource::make(
                $this->properties->reject($property, $request->user(), $request->validated('reason'))
            ),
            'Properti ditolak.',
        );
    }
}
