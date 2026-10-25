@php
    $typeLabels = ['seller' => 'Developer / Agen', 'architect' => 'Arsitek'];
@endphp
<div class="space-y-5">
    <h1 class="text-2xl font-bold">Verifikasi mitra</h1>

    <div class="flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.500ms="q" placeholder="Cari nama usaha, nama, email" class="w-64 rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <select wire:model.live="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="pending">Menunggu verifikasi</option>
            <option value="approved">Terverifikasi</option>
            <option value="rejected">Ditolak</option>
            <option value="">Semua</option>
        </select>
        <select wire:model.live="type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua jenis</option>
            <option value="seller">Developer / Agen</option>
            <option value="architect">Arsitek</option>
        </select>
    </div>

    @if ($notice)<p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>@endif
    @error('list')<p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror

    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white" wire:loading.class="opacity-60">
        @forelse ($profiles as $p)
            <div wire:key="pp-{{ $p->id }}" class="space-y-3 px-5 py-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-status-badge :status="$p->verification_status" context="verification" />
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">{{ $typeLabels[$p->partner_type->value] ?? $p->partner_type->value }}</span>
                        </div>
                        <p class="mt-1 font-medium">{{ $p->business_name }}</p>
                        <p class="text-sm text-gray-500">{{ $p->user?->name }} · {{ $p->user?->email }}@if ($p->user?->phone) · {{ $p->user->phone }}@endif</p>
                        <p class="text-xs text-gray-400">Mendaftar {{ $p->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-sm">
                        <button type="button" wire:click="toggle({{ $p->id }})" class="rounded-lg border border-gray-300 px-3 py-1.5 font-medium hover:bg-gray-50">{{ $expandedId === $p->id ? 'Sembunyikan' : 'Detail' }}</button>
                        @if ($p->verification_status->value !== 'approved')
                            <button type="button" wire:click="approve({{ $p->id }})" wire:confirm="Verifikasi mitra ini?" class="rounded-lg bg-green-600 px-3 py-1.5 font-medium text-white hover:bg-green-700">Setujui</button>
                        @endif
                        @if ($p->verification_status->value !== 'rejected')
                            <button type="button" wire:click="startReject({{ $p->id }})" class="rounded-lg border border-red-300 px-3 py-1.5 font-medium text-red-700 hover:bg-red-50">Tolak</button>
                        @endif
                    </div>
                </div>

                @if ($p->verification_status->value === 'rejected' && $p->rejection_reason)
                    <p class="text-sm text-red-700">Alasan penolakan: {{ $p->rejection_reason }}</p>
                @endif

                @if ($expandedId === $p->id)
                    <dl class="grid gap-3 rounded-xl bg-gray-50 p-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-gray-500">Nomor izin / keanggotaan</dt><dd class="font-medium">{{ $p->license_number ?: '-' }}</dd></div>
                        <div><dt class="text-gray-500">Telepon usaha</dt><dd class="font-medium">{{ $p->business_phone ?: '-' }}</dd></div>
                        <div><dt class="text-gray-500">Kota</dt><dd class="font-medium">{{ $p->city ?: '-' }}</dd></div>
                        <div><dt class="text-gray-500">Alamat</dt><dd class="font-medium">{{ $p->address ?: '-' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500">Portofolio</dt>
                            <dd class="font-medium">
                                @if ($p->portfolio_url)
                                    <a href="{{ $p->portfolio_url }}" target="_blank" rel="noopener nofollow" class="text-blue-700 hover:underline">{{ $p->portfolio_url }}</a>
                                @else - @endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2"><dt class="text-gray-500">Tentang usaha</dt><dd class="whitespace-pre-line">{{ $p->description ?: '-' }}</dd></div>
                    </dl>
                @endif

                @if ($rejectingId === $p->id)
                    <form wire:submit="reject" class="space-y-2 rounded-xl border border-red-200 bg-red-50 p-4">
                        <label class="block text-sm font-medium">Alasan penolakan (dilihat oleh mitra)</label>
                        <textarea rows="2" wire:model="reason" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        @error('reason')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        <div class="flex gap-2">
                            <button type="submit" class="rounded-lg bg-red-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-red-700">Tolak mitra</button>
                            <button type="button" wire:click="cancelReject" class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium hover:bg-gray-50">Batal</button>
                        </div>
                    </form>
                @endif
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Tidak ada pendaftaran mitra pada filter ini.</div>
        @endforelse
    </div>

    <div>{{ $profiles->links() }}</div>
</div>
