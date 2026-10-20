<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PartnerIndexRequest;
use App\Http\Requests\Admin\RejectRequest;
use App\Http\Resources\PartnerProfileResource;
use App\Models\PartnerProfile;
use App\Services\PartnerService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function __construct(private PartnerService $partners)
    {
    }

    public function index(PartnerIndexRequest $request): JsonResponse
    {
        return ApiResponse::success(
            PartnerProfileResource::collection($this->partners->list($request->validated())),
            'Daftar partner.',
        );
    }

    public function approve(Request $request, PartnerProfile $partnerProfile): JsonResponse
    {
        return ApiResponse::success(
            PartnerProfileResource::make($this->partners->approve($partnerProfile, $request->user())),
            'Partner berhasil diverifikasi.',
        );
    }

    public function reject(RejectRequest $request, PartnerProfile $partnerProfile): JsonResponse
    {
        return ApiResponse::success(
            PartnerProfileResource::make(
                $this->partners->reject($partnerProfile, $request->user(), $request->validated('reason'))
            ),
            'Partner ditolak.',
        );
    }
}
