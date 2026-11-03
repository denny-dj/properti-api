<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\ArchitectPortfolioService;
use Illuminate\Contracts\View\View;

class PortfolioPageController extends Controller
{
    public function show(string $slug, ArchitectPortfolioService $portfolios): View
    {
        try {
            $portfolio = $portfolios->showPublic($slug);
        } catch (ApiException) {
            abort(404);
        }

        $related = collect($portfolios->listPublic(['category' => $portfolio->category->value, 'per_page' => 4])->items())
            ->reject(fn ($p) => $p->id === $portfolio->id)
            ->take(3);

        return view('pages.portfolio-show', compact('portfolio', 'related'));
    }
}
