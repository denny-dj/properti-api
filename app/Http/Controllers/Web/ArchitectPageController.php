<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ArchitectCatalogService;
use Illuminate\Contracts\View\View;

class ArchitectPageController extends Controller
{
    public function show(string $slug, ArchitectCatalogService $catalog): View
    {
        try {
            $service = $catalog->showPublic($slug);
        } catch (ApiException) {
            abort(404);
        }

        return view('pages.architect-show', compact('service'));
    }
}
