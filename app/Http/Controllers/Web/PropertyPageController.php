<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Contracts\View\View;

class PropertyPageController extends Controller
{
    public function show(string $slug, PropertyService $properties): View
    {
        try {
            $property = $properties->showPublished($slug);
        } catch (ApiException) {
            abort(404);
        }

        $related = collect($properties->listPublished(['city' => $property->city, 'per_page' => 4])->items())
            ->reject(fn ($p) => $p->id === $property->id)
            ->take(3);

        return view('pages.property-show', compact('property', 'related'));
    }
}
