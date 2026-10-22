<?php

namespace App\Livewire\Orders;

use App\Enums\BookingStatus;
use App\Enums\TransactionStatus;
use App\Exceptions\ApiException;
use App\Models\ArchitectBooking;
use App\Models\Transaction;
use App\Services\BookingService;
use App\Services\ChatService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingDetail extends Component
{
    #[Locked]
    public int $bookingId = 0;

    public bool $showCancel = false;

    public string $cancelReason = '';

    public function mount(int $booking): void
    {
        $this->bookingId = $booking;
        $this->booking();
    }

    private function booking(): ArchitectBooking
    {
        $booking = ArchitectBooking::findOrFail($this->bookingId);
        Gate::authorize('view', $booking);

        return $booking;
    }

    /** Bayar (atau lanjutkan pembayaran) lewat halaman Midtrans. */
    public function pay()
    {
        $this->booking();

        try {
            $transaction = app(PaymentService::class)->checkoutBooking(auth()->user(), $this->bookingId);
        } catch (ApiException $e) {
            $this->addError('action', $e->getMessage());

            return;
        }

        if (! $transaction->redirect_url) {
            $this->addError('action', 'Tagihan belum siap. Silakan coba lagi.');

            return;
        }

        return $this->redirect($transaction->redirect_url);
    }

    public function cancel(): void
    {
        $booking = $this->booking();
        Gate::authorize('cancel', $booking);

        $this->validate(['cancelReason' => ['nullable', 'string', 'max:1000']]);

        try {
            app(BookingService::class)->cancel($booking, auth()->user(), $this->cancelReason !== '' ? $this->cancelReason : null);
        } catch (ApiException $e) {
            $this->addError('action', $e->getMessage());

            return;
        }

        $this->showCancel = false;
        $this->reset('cancelReason');
        session()->flash('status', 'Pesanan dibatalkan.');
    }

    public function chat()
    {
        $booking = $this->booking();

        try {
            $conversation = app(ChatService::class)->start(auth()->user(), ['partner_id' => $booking->architect_id]);
        } catch (ApiException $e) {
            $this->addError('action', $e->getMessage());

            return;
        }

        return redirect()->route('chat.room', $conversation->id);
    }

    public function render()
    {
        $booking = app(BookingService::class)->loadDetail($this->booking());

        $transaction = Transaction::where('architect_booking_id', $booking->id)->latest('id')->first();
        $paid = $transaction && $transaction->status === TransactionStatus::Paid;

        return view('livewire.orders.booking-detail', [
            'booking' => $booking,
            'transaction' => $transaction,
            'paid' => $paid,
            'canPay' => $booking->status === BookingStatus::Confirmed && ! $paid,
            'canCancel' => in_array($booking->status, [BookingStatus::Pending, BookingStatus::Confirmed], true)
                && $booking->scheduled_start->isFuture(),
        ]);
    }
}
