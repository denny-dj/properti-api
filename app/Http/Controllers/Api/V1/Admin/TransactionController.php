<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\TransactionIndexRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $transactions)
    {
    }

    public function index(TransactionIndexRequest $request): JsonResponse
    {
        return ApiResponse::success(
            TransactionResource::collection($this->transactions->listForAdmin($request->validated())),
            'Daftar seluruh transaksi.',
        );
    }

    /** Ringkasan jumlah & nominal per status (mendukung filter yang sama dengan index). */
    public function summary(TransactionIndexRequest $request): JsonResponse
    {
        return ApiResponse::success($this->transactions->summary($request->validated()), 'Ringkasan transaksi.');
    }

    public function show(Transaction $transaction): JsonResponse
    {
        return ApiResponse::success(
            TransactionResource::make($this->transactions->loadDetail($transaction)),
            'Detail transaksi.',
        );
    }
}
