<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Support\EmailVerification;
use App\Exceptions\ApiException;
use App\Models\ArchitectBooking;
use App\Models\User;
use App\Repositories\Contracts\ArchitectServiceRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    private const RELATIONS = ['service', 'client', 'architect.partnerProfile'];

    public function __construct(
        private BookingRepositoryInterface $bookings,
        private ArchitectServiceRepositoryInterface $services,
        private SlotService $slots,
    ) {
    }

    // ---------- Daftar ----------

    public function listForClient(User $client, array $filters): LengthAwarePaginator
    {
        return $this->bookings->paginateForClient($client->id, $filters, $this->perPage($filters));
    }

    public function listForArchitect(User $architect, array $filters): LengthAwarePaginator
    {
        return $this->bookings->paginateForArchitect($architect->id, $filters, $this->perPage($filters));
    }

    public function listForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->bookings->paginateForAdmin($filters, $this->perPage($filters));
    }

    public function loadDetail(ArchitectBooking $booking): ArchitectBooking
    {
        return $booking->load(self::RELATIONS);
    }

    // ---------- Pemesanan ----------

    public function create(User $client, array $data): ArchitectBooking
    {
        EmailVerification::require($client);

        $service = $this->services->findActiveById((int) $data['architect_service_id'])
            ?? throw new ApiException('Layanan arsitek tidak tersedia.', 404);

        if ($service->architect_id === $client->id) {
            throw new ApiException('Anda tidak dapat memesan layanan milik sendiri.', 422);
        }

        $tz = config('app.timezone');
        $start = CarbonImmutable::createFromFormat('Y-m-d H:i', $data['scheduled_start'], $tz);

        if ($start->greaterThan(CarbonImmutable::now($tz)->addDays(SlotService::MAX_ADVANCE_DAYS))) {
            throw new ApiException('Pemesanan maksimal '.SlotService::MAX_ADVANCE_DAYS.' hari ke depan.', 422);
        }

        return DB::transaction(function () use ($service, $client, $start, $data) {
            // Serialisasi per-arsitek: dua klien tidak bisa merebut slot yang sama.
            $this->bookings->lockArchitect($service->architect_id);

            if (! $this->slots->isAvailable($service, $start)) {
                throw new ApiException('Slot waktu tidak tersedia. Silakan pilih jadwal lain.', 422);
            }

            /** @var ArchitectBooking $booking */
            $booking = $this->bookings->create([
                'code' => $this->generateCode(),
                'client_id' => $client->id,
                'architect_id' => $service->architect_id,
                'architect_service_id' => $service->id,
                'scheduled_start' => $start,
                'scheduled_end' => $start->addMinutes($service->duration_minutes),
                'meeting_type' => $data['meeting_type'],
                'location' => $data['location'] ?? null,
                'notes' => $data['notes'] ?? null,
                'price' => $service->price,
                'duration_minutes' => $service->duration_minutes,
                'status' => BookingStatus::Pending,
            ]);

            return $booking->load(self::RELATIONS);
        });
    }

    // ---------- Transisi status ----------

    public function confirm(ArchitectBooking $booking): ArchitectBooking
    {
        $this->assertStatus($booking, [BookingStatus::Pending], 'Hanya pesanan berstatus pending yang dapat dikonfirmasi.');

        return $this->transition($booking, [
            'status' => BookingStatus::Confirmed,
            'confirmed_at' => now(),
        ]);
    }

    public function reject(ArchitectBooking $booking, User $architect, string $reason): ArchitectBooking
    {
        $this->assertStatus($booking, [BookingStatus::Pending], 'Hanya pesanan berstatus pending yang dapat ditolak.');

        return $this->transition($booking, [
            'status' => BookingStatus::Rejected,
            'cancelled_at' => now(),
            'cancelled_by' => $architect->id,
            'cancel_reason' => $reason,
        ]);
    }

    public function complete(ArchitectBooking $booking): ArchitectBooking
    {
        $this->assertStatus($booking, [BookingStatus::Confirmed], 'Hanya pesanan terkonfirmasi yang dapat diselesaikan.');

        if ($booking->scheduled_start->isFuture()) {
            throw new ApiException('Sesi konsultasi belum dimulai.', 422);
        }

        return $this->transition($booking, [
            'status' => BookingStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function cancel(ArchitectBooking $booking, User $actor, ?string $reason = null): ArchitectBooking
    {
        $this->assertStatus(
            $booking,
            [BookingStatus::Pending, BookingStatus::Confirmed],
            'Pesanan ini tidak dapat dibatalkan.',
        );

        if ($actor->id === $booking->client_id && $booking->scheduled_start->isPast()) {
            throw new ApiException('Pesanan yang sudah dimulai tidak dapat dibatalkan.', 422);
        }

        return $this->transition($booking, [
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancelled_by' => $actor->id,
            'cancel_reason' => $reason,
        ]);
    }

    // ---------- Helper ----------

    private function transition(ArchitectBooking $booking, array $attributes): ArchitectBooking
    {
        /** @var ArchitectBooking $booking */
        $booking = $this->bookings->update($booking, $attributes);

        return $booking->load(self::RELATIONS);
    }

    /** @param  array<int, BookingStatus>  $allowed */
    private function assertStatus(ArchitectBooking $booking, array $allowed, string $message): void
    {
        if (! in_array($booking->status, $allowed, true)) {
            throw new ApiException($message, 422);
        }
    }

    private function generateCode(): string
    {
        do {
            $code = 'BK-'.now()->format('ymd').'-'.strtoupper(Str::random(6));
        } while ($this->bookings->codeExists($code));

        return $code;
    }

    private function perPage(array $filters): int
    {
        return (int) ($filters['per_page'] ?? 15);
    }
}
