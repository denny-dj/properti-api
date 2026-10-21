<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\BookingIndexRequest;
use App\Http\Resources\BookingResource;
use App\Models\ArchitectBooking;
use App\Services\BookingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings)
    {
    }

    public function index(BookingIndexRequest $request): JsonResponse
    {
        return ApiResponse::success(
            BookingResource::collection($this->bookings->listForAdmin($request->validated())),
            'Daftar seluruh pesanan konsultasi.',
        );
    }

    public function show(ArchitectBooking $booking): JsonResponse
    {
        return ApiResponse::success(BookingResource::make($this->bookings->loadDetail($booking)), 'Detail pesanan.');
    }
}
