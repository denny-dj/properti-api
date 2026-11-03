<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BannerPlacement;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdBannerResource;
use App\Services\PublicContentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContentController extends Controller
{
    public function __construct(private PublicContentService $content)
    {
    }

    public function howItWorks(): JsonResponse
    {
        return ApiResponse::success($this->content->howItWorks(), 'Cara kerja.');
    }

    public function promotions(Request $request): JsonResponse
    {
        $data = $request->validate(['placement' => ['nullable', Rule::enum(BannerPlacement::class)]]);

        return ApiResponse::success(
            AdBannerResource::collection($this->content->promotions($data['placement'] ?? null)),
            'Promosi aktif.',
        );
    }

    public function click(int $id): JsonResponse
    {
        $banner = $this->content->trackClick($id);

        return ApiResponse::success(['target_url' => $banner->target_url], 'Klik tercatat.');
    }
}
