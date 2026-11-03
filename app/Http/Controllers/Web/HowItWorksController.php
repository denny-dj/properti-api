<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PublicContentService;
use Illuminate\Contracts\View\View;

class HowItWorksController extends Controller
{
    public function __invoke(PublicContentService $content): View
    {
        return view('pages.how-it-works', ['content' => $content->howItWorks()]);
    }
}
