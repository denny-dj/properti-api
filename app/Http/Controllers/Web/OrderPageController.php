<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ArchitectBooking;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class OrderPageController extends Controller
{
    public function booking(ArchitectBooking $booking): View
    {
        Gate::authorize('view', $booking);

        return view('pages.order-show', ['bookingId' => $booking->id]);
    }

    public function transaction(Transaction $transaction): View
    {
        Gate::authorize('view', $transaction);

        return view('pages.transaction-show', ['code' => $transaction->code]);
    }
}
