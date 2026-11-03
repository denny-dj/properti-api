<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Concierge\GenerateWaLinkRequest;
use App\Http\Requests\Concierge\KprInterestRequest;
use App\Services\ConciergeRoutingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/** Publik: setiap minat dicatat sebagai prospek lalu diarahkan ke WhatsApp admin. */
class ConciergeController extends Controller
{
    public function __construct(private ConciergeRoutingService $routing)
    {
    }

    public function generateWaLink(GenerateWaLinkRequest $request): JsonResponse
    {
        return $this->respond($this->routing->generateWhatsAppLink($request->validated()));
    }

    public function kprInterest(KprInterestRequest $request): JsonResponse
    {
        return $this->respond($this->routing->recordKprInterest($request->validated()));
    }

    /** @param  array{lead: \App\Models\ConciergeLead, wa_url: string, message: string, created: bool}  $result */
    private function respond(array $result): JsonResponse
    {
        return ApiResponse::success([
            'lead_code' => $result['lead']->lead_code,
            'wa_url' => $result['wa_url'],
            'message' => $result['message'],
        ], $result['created']
            ? 'Prospek tercatat. Lanjutkan percakapan lewat WhatsApp.'
            : 'Prospek Anda sudah tercatat sebelumnya. Lanjutkan lewat WhatsApp.',
            $result['created'] ? 201 : 200,
        );
    }
}
