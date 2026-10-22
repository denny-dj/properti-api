<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ArchitectCatalogService;
use App\Services\PropertyService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(PropertyService $properties, ArchitectCatalogService $architects): View
    {
        return view('pages.home', [
            'latest' => $properties->listPublished(['per_page' => 6])->items(),
            'services' => $architects->listPublic(['per_page' => 3])->items(),
        ]);
    }
}
