<?php

namespace App\Http\Controllers\Web\Partner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PropertyPageController extends Controller
{
    public function edit(Property $property): View|RedirectResponse
    {
        abort_unless($property->partner_id === auth()->id(), 403);

        if (! Gate::allows('update', $property)) {
            return redirect()->route('partner.properties.index')
                ->with('status', 'Properti yang sudah dipesan atau terjual tidak dapat diubah.');
        }

        return view('pages.partner.property-edit', ['propertyId' => $property->id]);
    }
}
