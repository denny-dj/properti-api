<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Concierge\SubmitDeliverableRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\DeliverableService;
use App\Services\PartnerBalanceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Serah terima berkas desain lewat tautan Google Drive + saldo mitra. */
class DeliverableController extends Controller
{
    public function __construct(
        private DeliverableService $deliverables,
        private PartnerBalanceService $balances,
    ) {
    }

    /** Mitra: kirim tautan berkas final. */
    public function submit(SubmitDeliverableRequest $request, Transaction $transaction): JsonResponse
    {
        $updated = $this->deliverables->submit($transaction, $request->user(), $request->validated('deliverable_link'));

        return ApiResponse::success(
            TransactionResource::make($updated),
            'Tautan berkas dikirim ke pembeli untuk diperiksa.',
        );
    }

    /** Pembeli: konfirmasi berkas diterima & sesuai -> dana dilepas ke saldo mitra. */
    public function confirm(Request $request, Transaction $transaction): JsonResponse
    {
        $updated = $this->deliverables->confirm($transaction, $request->user());

        return ApiResponse::success(
            TransactionResource::make($updated),
            'Penerimaan berkas dikonfirmasi. Dana dilepas ke saldo mitra.',
        );
    }

    /** Mitra: ringkasan saldo (menunggu dilepas vs siap dicairkan). */
    public function balance(Request $request): JsonResponse
    {
        return ApiResponse::success($this->balances->summary($request->user()), 'Saldo mitra.');
    }
}
