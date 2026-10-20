<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityResource;
use App\Repositories\Contracts\FacilityRepositoryInterface;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class FacilityController extends Controller
{
    public function __construct(private FacilityRepositoryInterface $facilities)
    {
    }

    public function index(): JsonResponse
    {
        return ApiResponse::success(FacilityResource::collection($this->facilities->all()), 'Daftar fasilitas.');
    }
}
