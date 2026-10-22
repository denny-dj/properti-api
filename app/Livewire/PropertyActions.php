<?php

namespace App\Livewire;

use App\Exceptions\ApiException;
use App\Models\Property;
use App\Models\User;
use App\Services\ChatService;
use App\Services\PaymentService;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Tombol "Chat penjual" dan "Bayar booking fee" di halaman detail properti. */
class PropertyActions extends Component
{
    #[Locked]
    public int $propertyId = 0;

    public function mount(int $property): void
    {
        $this->propertyId = $property;
    }

    public function goLogin()
    {
        $property = Property::find($this->propertyId);

        if ($property) {
            session()->put('url.intended', route('properties.show', $property->slug));
        }

        return redirect()->route('login');
    }

    public function chat()
    {
        $user = $this->buyerOrAbort();

        try {
            $conversation = app(ChatService::class)->start($user, ['property_id' => $this->propertyId]);
        } catch (ApiException $e) {
            $this->addError('action', $e->getMessage());

            return;
        }

        return redirect()->route('chat.room', $conversation->id);
    }

    public function payFee()
    {
        $user = $this->buyerOrAbort();

        try {
            $transaction = app(PaymentService::class)->checkoutProperty($user, $this->propertyId);
        } catch (ApiException $e) {
            $this->addError('action', $e->getMessage());

            return;
        }

        if (! $transaction->redirect_url) {
            $this->addError('action', 'Tagihan belum siap. Silakan coba lagi.');

            return;
        }

        // Alihkan ke halaman pembayaran Midtrans (VA, kartu kredit + cicilan, e-wallet, QRIS)
        return $this->redirect($transaction->redirect_url);
    }

    private function buyerOrAbort(): User
    {
        $user = auth()->user();
        abort_unless($user && $user->hasRole('user'), 403);

        return $user;
    }

    public function render()
    {
        $property = Property::findOrFail($this->propertyId);
        $user = auth()->user();

        return view('livewire.property-actions', [
            'isOwner' => $user && $user->id === $property->partner_id,
            'isBuyer' => $user && $user->hasRole('user'),
            'fee' => app(PaymentService::class)->bookingFeeFor($property),
        ]);
    }
}
