<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kpr\KprSimulationRequest;
use App\Http\Requests\Kpr\PropertyKprRequest;
use App\Services\KprService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class KprController extends Controller
{
    public function __construct(private KprService $kpr)
    {
    }

    public function simulate(KprSimulationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['include_schedule'] = $request->boolean('include_schedule');

        return ApiResponse::success($this->kpr->simulate($data), 'Simulasi KPR.');
    }

    public function forProperty(PropertyKprRequest $request, string $slug): JsonResponse
    {
        $data = $request->validated();
        $data['include_schedule'] = $request->boolean('include_schedule');

        return ApiResponse::success($this->kpr->simulateForProperty($slug, $data), 'Simulasi KPR untuk properti.');
    }
}
