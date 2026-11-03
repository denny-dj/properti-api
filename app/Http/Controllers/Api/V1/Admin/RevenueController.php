<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Concierge\RevenueSummaryRequest;
use App\Services\RevenueSummaryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class RevenueController extends Controller
{
    public function __construct(private RevenueSummaryService $revenue)
    {
    }

    public function summary(RevenueSummaryRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->revenue->summary($request->validated('from'), $request->validated('to')),
            'Rekap pendapatan platform.',
        );
    }
}
