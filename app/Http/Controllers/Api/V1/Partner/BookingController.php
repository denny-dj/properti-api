<?php

namespace App\Http\Controllers\Api\V1\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectRequest;
use App\Http\Requests\Booking\BookingIndexRequest;
use App\Http\Requests\Booking\CancelBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\ArchitectBooking;
use App\Services\BookingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Pesanan masuk untuk arsitek. */
class BookingController extends Controller
{
    public function __construct(private BookingService $bookings)
    {
    }

    public function index(BookingIndexRequest $request): JsonResponse
    {
        return ApiResponse::success(
            BookingResource::collection($this->bookings->listForArchitect($request->user(), $request->validated())),
            'Daftar pesanan masuk.',
        );
    }

    public function show(ArchitectBooking $booking): JsonResponse
    {
        Gate::authorize('manage', $booking);

        return ApiResponse::success(BookingResource::make($this->bookings->loadDetail($booking)), 'Detail pesanan.');
    }

    public function confirm(ArchitectBooking $booking): JsonResponse
    {
        Gate::authorize('manage', $booking);

        return ApiResponse::success(BookingResource::make($this->bookings->confirm($booking)), 'Pesanan dikonfirmasi.');
    }

    public function reject(RejectRequest $request, ArchitectBooking $booking): JsonResponse
    {
        Gate::authorize('manage', $booking);

        return ApiResponse::success(
            BookingResource::make($this->bookings->reject($booking, $request->user(), $request->validated('reason'))),
            'Pesanan ditolak.',
        );
    }

    public function complete(Request $request, ArchitectBooking $booking): JsonResponse
    {
        Gate::authorize('manage', $booking);

        return ApiResponse::success(BookingResource::make($this->bookings->complete($booking)), 'Pesanan diselesaikan.');
    }

    public function cancel(CancelBookingRequest $request, ArchitectBooking $booking): JsonResponse
    {
        Gate::authorize('cancel', $booking);

        return ApiResponse::success(
            BookingResource::make($this->bookings->cancel($booking, $request->user(), $request->validated('reason'))),
            'Pesanan dibatalkan.',
        );
    }
}
