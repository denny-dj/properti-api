@php
    $methods = ['bank_transfer' => 'Virtual Account / transfer', 'echannel' => 'Mandiri Bill', 'credit_card' => 'Kartu kredit', 'gopay' => 'GoPay', 'shopeepay' => 'ShopeePay', 'qris' => 'QRIS'];
    $typeLabels = ['property_booking_fee' => 'Booking fee properti', 'architect_booking' => 'Konsultasi arsitek'];
@endphp
<div class="space-y-5">
    <h1 class="text-2xl font-bold">Transaksi</h1>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5"><p class="text-sm text-gray-500">Lunas (sesuai filter)</p><p class="mt-1 text-xl font-bold text-green-700"><x-rupiah :value="$summary['paid_total_amount']" /></p></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5"><p class="text-sm text-gray-500">Jumlah transaksi lunas</p><p class="mt-1 text-xl font-bold">{{ $summary['paid_count'] }}</p></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Per status</p>
            <p class="mt-1 text-sm">
                @forelse ($summary['by_status'] as $row)
                    <span class="mr-2 inline-block"><span class="font-semibold">{{ $row['count'] }}</span> {{ $row['status'] }}</span>
                @empty
                    <span class="text-gray-400">-</span>
                @endforelse
            </p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <select wire:model.live="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="paid">Lunas</option>
            <option value="pending">Menunggu pembayaran</option>
            <option value="expired">Kedaluwarsa</option>
            <option value="failed">Gagal</option>
            <option value="cancelled">Dibatalkan</option>
            <option value="refunded">Dikembalikan</option>
        </select>
        <select wire:model.live="type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua jenis</option>
            <option value="property_booking_fee">Booking fee properti</option>
            <option value="architect_booking">Konsultasi arsitek</option>
        </select>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="needsReview" class="rounded border-gray-300"> Perlu ditinjau saja</label>
        <label class="flex items-center gap-2 text-sm text-gray-600">Dari <input type="date" wire:model.live="from" class="rounded-lg border border-gray-300 px-3 py-2 text-sm"></label>
        <label class="flex items-center gap-2 text-sm text-gray-600">Sampai <input type="date" wire:model.live="to" class="rounded-lg border border-gray-300 px-3 py-2 text-sm"></label>
    </div>

    @if ($notice)<p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>@endif
    @error('list')<p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror

    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white" wire:loading.class="opacity-60">
        @forelse ($transactions as $t)
            <div wire:key="atrx-{{ $t->id }}" class="space-y-3 px-5 py-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-status-badge :status="$t->status" context="transaction" />
                            @if ($t->needs_review)
                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">Perlu ditinjau</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $typeLabels[$t->type->value] ?? $t->type->value }}</span>
                        </div>
                        <p class="mt-1 font-medium">{{ $t->description }}</p>
                        <p class="text-sm text-gray-500">{{ $t->payer?->name }} → {{ $t->payee?->name ?? '-' }} · {{ $t->created_at->format('d/m/Y H:i') }}</p>
                        <p class="text-xs text-gray-400">{{ $t->code }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-blue-700"><x-rupiah :value="$t->amount" /></p>
                        <button type="button" wire:click="toggle({{ $t->id }})" class="mt-1 text-sm font-medium text-blue-700 hover:underline">{{ $expandedId === $t->id ? 'Sembunyikan' : 'Detail & tindakan' }}</button>
                    </div>
                </div>

                @if ($expandedId === $t->id)
                    <div class="space-y-3 rounded-xl bg-gray-50 p-4 text-sm">
                        <dl class="grid gap-3 sm:grid-cols-2">
                            <div><dt class="text-gray-500">Metode</dt><dd class="font-medium">{{ $t->payment_type ? ($methods[$t->payment_type] ?? $t->payment_type) : '-' }}@if ($t->payment_channel) ({{ strtoupper($t->payment_channel) }})@endif @if ($t->installment_term) · cicilan {{ $t->installment_term }} bulan @endif</dd></div>
                            <div><dt class="text-gray-500">ID transaksi gateway</dt><dd class="font-medium">{{ $t->gateway_transaction_id ?: '-' }}</dd></div>
                            <div><dt class="text-gray-500">Status fraud</dt><dd class="font-medium">{{ $t->fraud_status ?: '-' }}</dd></div>
                            <div><dt class="text-gray-500">Dibayar</dt><dd class="font-medium">{{ $t->paid_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                            <div><dt class="text-gray-500">Batas pembayaran</dt><dd class="font-medium">{{ $t->expires_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                            <div>
                                <dt class="text-gray-500">Objek</dt>
                                <dd class="font-medium">
                                    @if ($t->property){{ $t->property->title }}@elseif ($t->architectBooking){{ $t->architectBooking->code }}@else - @endif
                                </dd>
                            </div>
                            @if ($t->reviewed_at)
                                <div class="sm:col-span-2"><dt class="text-gray-500">Peninjauan terakhir</dt><dd class="font-medium">{{ $t->reviewed_at->format('d/m/Y H:i') }}@if ($t->review_note) — {{ $t->review_note }}@endif</dd></div>
                            @endif
                        </dl>

                        @if ($t->gateway_payload)
                            <details>
                                <summary class="cursor-pointer text-gray-500">Payload terakhir dari gateway</summary>
                                <pre class="mt-2 max-h-60 overflow-auto rounded-lg bg-white p-3 text-xs">{{ json_encode($t->gateway_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        @endif

                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="sync({{ $t->id }})" wire:loading.attr="disabled" wire:target="sync({{ $t->id }})" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-medium hover:bg-gray-50">Sinkronkan dari Midtrans</button>
                            @if ($t->needs_review)
                                <button type="button" wire:click="startReview({{ $t->id }}, 'review')" class="rounded-lg bg-blue-600 px-3 py-1.5 font-medium text-white hover:bg-blue-700">Tandai sudah ditinjau</button>
                            @endif
                            @if ($t->status->value === 'paid')
                                <button type="button" wire:click="startReview({{ $t->id }}, 'refund')" class="rounded-lg border border-red-300 bg-white px-3 py-1.5 font-medium text-red-700 hover:bg-red-50">Catat pengembalian dana</button>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($reviewingId === $t->id)
                    <form wire:submit="submitReview" class="space-y-2 rounded-xl border {{ $reviewMode === 'refund' ? 'border-red-200 bg-red-50' : 'border-blue-200 bg-blue-50' }} p-4">
                        @if ($reviewMode === 'refund')
                            <p class="text-sm text-red-800">Ini hanya <strong>mencatat</strong> bahwa dana sudah dikembalikan. Lakukan pengembalian dana terlebih dahulu di dashboard Midtrans. Untuk booking fee properti yang batal, pastikan penjual sudah melepas reservasi.</p>
                        @endif
                        <label class="block text-sm font-medium">Catatan (opsional)</label>
                        <textarea rows="2" wire:model="reviewNote" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        @error('reviewNote')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        <div class="flex gap-2">
                            <button type="submit" class="rounded-lg {{ $reviewMode === 'refund' ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }} px-4 py-1.5 text-sm font-medium text-white">{{ $reviewMode === 'refund' ? 'Catat dikembalikan' : 'Simpan tinjauan' }}</button>
                            <button type="button" wire:click="dismissReview" class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium hover:bg-gray-50">Batal</button>
                        </div>
                    </form>
                @endif
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Tidak ada transaksi pada filter ini.</div>
        @endforelse
    </div>

    <div>{{ $transactions->links() }}</div>
</div>
