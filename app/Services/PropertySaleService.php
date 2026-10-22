<?php

namespace App\Services;

use App\Enums\PropertyStatus;
use App\Exceptions\ApiException;
use App\Models\Property;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

/** Tindak lanjut setelah booking fee dibayar (status "reserved"). */
class PropertySaleService
{
    public function __construct(
        private PropertyRepositoryInterface $properties,
        private TransactionRepositoryInterface $transactions,
    ) {
    }

    public function markSold(Property $property): Property
    {
        $this->assertReserved($property);

        /** @var Property $property */
        $property = $this->properties->update($property, ['status' => PropertyStatus::Sold]);

        return $this->properties->loadDetail($property);
    }

    /**
     * Transaksi jual-beli batal: properti tayang kembali.
     * Booking fee yang sudah dibayar ditandai needs_review agar admin menangani pengembaliannya.
     */
    public function release(Property $property): Property
    {
        $this->assertReserved($property);

        return DB::transaction(function () use ($property) {
            $this->transactions->flagPaidForProperty($property->id);

            /** @var Property $property */
            $property = $this->properties->update($property, ['status' => PropertyStatus::Published]);

            return $this->properties->loadDetail($property);
        });
    }

    private function assertReserved(Property $property): void
    {
        if ($property->status !== PropertyStatus::Reserved) {
            throw new ApiException('Hanya properti berstatus reserved yang dapat diproses.', 422);
        }
    }
}
