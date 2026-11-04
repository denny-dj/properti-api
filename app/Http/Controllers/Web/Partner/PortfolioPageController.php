<?php

namespace App\Http\Controllers\Web\Partner;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ArchitectPortfolioService;
use Illuminate\Contracts\View\View;

class PortfolioPageController extends Controller
{
    public function edit(int $id, ArchitectPortfolioService $portfolios): View
    {
        try {
            $portfolio = $portfolios->findOwnedOrFail(auth()->user(), $id);
        } catch (ApiException) {
            abort(404);
        }

        return view('pages.partner.portfolio-edit', ['portfolioId' => $portfolio->id]);
    }
}
