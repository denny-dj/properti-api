@php
    $roleLabels = ['user' => 'Pembeli', 'partner' => 'Mitra', 'admin' => 'Admin'];
@endphp
<div class="space-y-5">
    <h1 class="text-2xl font-bold">Pengguna</h1>

    <div class="flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.500ms="q" placeholder="Cari nama, email, HP" class="w-64 rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <select wire:model.live="role" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua peran</option>
            <option value="user">Pembeli</option>
            <option value="partner">Mitra</option>
            <option value="admin">Admin</option>
        </select>
        <select wire:model.live="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="active">Aktif</option>
            <option value="suspended">Disuspend</option>
        </select>
    </div>

    @if ($notice)<p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>@endif
    @error('list')<p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror

    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white" wire:loading.class="opacity-60">
        @forelse ($users as $u)
            @php $isAdminUser = $u->roles->contains('name', 'admin'); @endphp
            <div wire:key="usr-{{ $u->id }}" class="flex flex-wrap items-start justify-between gap-4 px-5 py-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-medium">{{ $u->name }}</p>
                        <x-status-badge :status="$u->status" context="user" />
                        @foreach ($u->roles as $r)
                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ $roleLabels[$r->name] ?? $r->name }}</span>
                        @endforeach
                        @if ($u->partnerProfile)
                            <x-status-badge :status="$u->partnerProfile->verification_status" context="verification" />
                        @endif
                    </div>
                    <p class="text-sm text-gray-500">{{ $u->email }}@if ($u->phone) · {{ $u->phone }}@endif</p>
                    <p class="text-xs text-gray-400">Bergabung {{ $u->created_at->format('d/m/Y') }}</p>
                </div>
                <div class="flex gap-2 text-sm">
                    @if ($isAdminUser || $u->id === auth()->id())
                        <span class="rounded-lg bg-gray-100 px-3 py-1.5 text-gray-500">Tidak dapat diubah</span>
                    @elseif ($u->status === 'active')
                        <button type="button" wire:click="suspend({{ $u->id }})" wire:confirm="Suspend akun {{ $u->name }}? Seluruh sesinya akan diakhiri dan layanannya disembunyikan." class="rounded-lg border border-red-300 px-3 py-1.5 font-medium text-red-700 hover:bg-red-50">Suspend</button>
                    @else
                        <button type="button" wire:click="activate({{ $u->id }})" class="rounded-lg bg-green-600 px-3 py-1.5 font-medium text-white hover:bg-green-700">Aktifkan</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Tidak ada pengguna pada filter ini.</div>
        @endforelse
    </div>

    <div>{{ $users->links() }}</div>
</div>
