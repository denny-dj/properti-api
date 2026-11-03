<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Concierge\LeadIndexRequest;
use App\Http\Requests\Concierge\UpdateLeadStatusRequest;
use App\Http\Resources\LeadResource;
use App\Services\ConciergeLeadService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class LeadController extends Controller
{
    public function __construct(private ConciergeLeadService $leads)
    {
    }

    public function index(LeadIndexRequest $request): JsonResponse
    {
        return ApiResponse::success(LeadResource::collection($this->leads->list($request->validated())), 'Daftar prospek.');
    }

    public function updateStatus(UpdateLeadStatusRequest $request, string $id): JsonResponse
    {
        $lead = $this->leads->updateStatus((int) $id, $request->user(), $request->validated('status'), $request->validated('note'));

        return ApiResponse::success(LeadResource::make($lead), 'Status prospek diperbarui.');
    }
}
