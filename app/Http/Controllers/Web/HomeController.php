<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ArchitectCatalogService;
use App\Services\ArchitectPortfolioService;
use App\Services\PropertyService;
use App\Services\PublicContentService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(
        PropertyService $properties,
        ArchitectCatalogService $architects,
        ArchitectPortfolioService $portfolios,
        PublicContentService $content,
    ): View {
        $featured = $properties->listPublished(['per_page' => 6])->items();

        return view('pages.home', [
            // Hero: satu banner top_hero aktif (null -> hero bawaan platform). Impression hanya dihitung untuk yang tampil.
            'heroBanner' => $content->heroBanner(),

            // Properti unggulan (6 terbaru). $latest dipertahankan sebagai alias agar view lama tidak rusak.
            'featuredProperties' => $featured,
            'latest' => $featured,

            // Portofolio arsitek pilihan.
            'portfolios' => $portfolios->listPublic(['per_page' => 3])->items(),

            // Banner strip "featured_developer" (dihitung impression-nya sekali di sini).
            'promotions' => $content->promotions('featured_developer'),

            'steps' => $content->howItWorks()['steps'],

            // Layanan konsultasi per jam (model lama): hanya dipakai bila CONCIERGE_LEGACY_UI=true.
            'services' => config('concierge.legacy_ui', true) ? $architects->listPublic(['per_page' => 3])->items() : [],
        ]);
    }
}
