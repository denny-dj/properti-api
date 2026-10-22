<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\TransactionIndexRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\PaymentService;
use App\Services\TransactionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/** Riwayat transaksi milik pembayar (buyer). */
class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactions,
        private PaymentService $payments,
    ) {
    }

    public function index(TransactionIndexRequest $request): JsonResponse
    {
        return ApiResponse::success(
            TransactionResource::collection($this->transactions->listForPayer($request->user(), $request->validated())),
            'Riwayat transaksi.',
        );
    }

    public function show(Transaction $transaction): JsonResponse
    {
        Gate::authorize('view', $transaction);

        return ApiResponse::success(
            TransactionResource::make($this->transactions->loadDetail($transaction)),
            'Detail transaksi.',
        );
    }

    /** Tarik status terbaru dari gateway (bila webhook belum masuk). */
    public function sync(Transaction $transaction): JsonResponse
    {
        Gate::authorize('sync', $transaction);

        return ApiResponse::success(
            TransactionResource::make($this->transactions->loadDetail($this->payments->sync($transaction))),
            'Status transaksi disinkronkan.',
        );
    }
}
