<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\PublicContentService;
use Illuminate\Http\RedirectResponse;

/** Mencatat klik banner iklan lalu meneruskan ke tujuan (hanya http/https). */
class PromoClickController extends Controller
{
    public function __invoke(int $id, PublicContentService $content): RedirectResponse
    {
        try {
            $banner = $content->trackClick($id);
        } catch (ApiException) {
            abort(404);
        }

        $scheme = strtolower((string) parse_url($banner->target_url, PHP_URL_SCHEME));
        abort_unless(in_array($scheme, ['http', 'https'], true), 404);

        return redirect()->away($banner->target_url);
    }
}
