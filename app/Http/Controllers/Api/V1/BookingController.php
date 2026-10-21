<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\BookingIndexRequest;
use App\Http\Requests\Booking\CancelBookingRequest;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\ArchitectBooking;
use App\Services\BookingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/** Pemesanan konsultasi oleh klien (buyer). */
class BookingController extends Controller
{
    public function __construct(private BookingService $bookings)
    {
    }

    public function index(BookingIndexRequest $request): JsonResponse
    {
        return ApiResponse::success(
            BookingResource::collection($this->bookings->listForClient($request->user(), $request->validated())),
            'Daftar pesanan konsultasi Anda.',
        );
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        return ApiResponse::success(
            BookingResource::make($this->bookings->create($request->user(), $request->validated())),
            'Pesanan dibuat. Menunggu konfirmasi arsitek.',
            201,
        );
    }

    public function show(ArchitectBooking $booking): JsonResponse
    {
        Gate::authorize('view', $booking);

        return ApiResponse::success(BookingResource::make($this->bookings->loadDetail($booking)), 'Detail pesanan.');
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
