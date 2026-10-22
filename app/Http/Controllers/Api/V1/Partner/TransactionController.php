<?php

namespace App\Http\Controllers\Api\V1\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\TransactionIndexRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/** Pembayaran masuk untuk partner (seller/arsitek) sebagai penerima. */
class TransactionController extends Controller
{
    public function __construct(private TransactionService $transactions)
    {
    }

    public function index(TransactionIndexRequest $request): JsonResponse
    {
        return ApiResponse::success(
            TransactionResource::collection($this->transactions->listForPayee($request->user(), $request->validated())),
            'Pembayaran masuk.',
        );
    }
}
