<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ConciergeRedirectRequest;
use App\Services\ConciergeRoutingService;
use Illuminate\Http\RedirectResponse;

/**
 * Tombol WhatsApp ringan untuk kartu & hero: mencatat prospek lalu mengarahkan ke WhatsApp admin.
 * Dikirim lewat POST (form biasa, tanpa JavaScript) sehingga prefetch/crawler tidak membuat prospek.
 */
class ConciergeRedirectController extends Controller
{
    public function __invoke(ConciergeRedirectRequest $request, ConciergeRoutingService $routing): RedirectResponse
    {
        $user = $request->user();

        $data = array_filter([
            'source' => $request->validated('source'),
            'reference_id' => $request->validated('reference_id'),
            'customer_name' => $user?->name,
            'customer_phone' => $user?->phone,
        ], fn ($value) => $value !== null && $value !== '');

        try {
            $result = $routing->generateWhatsAppLink($data);
        } catch (ApiException $e) {
            return redirect()->route('home')->with('status', $e->getMessage());
        }

        return redirect()->away($result['wa_url']);
    }
}
