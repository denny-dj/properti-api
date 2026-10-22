<?php

namespace App\Http\Controllers\Api\V1\Partner;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Services\PropertySaleService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PropertySaleController extends Controller
{
    public function __construct(private PropertySaleService $sales)
    {
    }

    public function markSold(Property $property): JsonResponse
    {
        Gate::authorize('manageSale', $property);

        return ApiResponse::success(PropertyResource::make($this->sales->markSold($property)), 'Properti ditandai terjual.');
    }

    public function release(Property $property): JsonResponse
    {
        Gate::authorize('manageSale', $property);

        return ApiResponse::success(
            PropertyResource::make($this->sales->release($property)),
            'Reservasi dilepas. Properti tayang kembali; pengembalian booking fee ditangani admin.',
        );
    }
}
