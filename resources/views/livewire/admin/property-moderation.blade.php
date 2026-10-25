<div class="space-y-5">
    <h1 class="text-2xl font-bold">Moderasi properti</h1>

    <div class="flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.500ms="q" placeholder="Cari judul, alamat, kota" class="w-64 rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <select wire:model.live="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="pending_review">Menunggu verifikasi</option>
            <option value="published">Tayang</option>
            <option value="reserved">Dipesan</option>
            <option value="rejected">Ditolak</option>
            <option value="sold">Terjual</option>
            <option value="draft">Draft</option>
            <option value="">Semua</option>
        </select>
    </div>

    @if ($notice)<p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>@endif
    @error('list')<p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror

    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white" wire:loading.class="opacity-60">
        @forelse ($properties as $p)
            <div wire:key="pm-{{ $p->id }}" class="space-y-3 px-5 py-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex min-w-0 flex-1 gap-4">
                        <div class="h-16 w-24 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                            @if ($p->coverImage)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($p->coverImage->path) }}" alt="" class="h-full w-full object-cover" onerror="this.remove()">
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-status-badge :status="$p->status" context="property" />
                                <p class="truncate font-medium">{{ $p->title }}</p>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ $p->partner?->partnerProfile?->business_name ?? $p->partner?->name }} · {{ $p->city }} · <x-rupiah :value="$p->price" /></p>
                            <p class="text-xs text-gray-400">Dibuat {{ $p->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 text-sm">
                        <button type="button" wire:click="toggle({{ $p->id }})" class="rounded-lg border border-gray-300 px-3 py-1.5 font-medium hover:bg-gray-50">{{ $expandedId === $p->id ? 'Sembunyikan' : 'Tinjau' }}</button>
                        @if ($p->status->value === 'pending_review')
                            <button type="button" wire:click="approve({{ $p->id }})" wire:confirm="Setujui dan tayangkan properti ini?" class="rounded-lg bg-green-600 px-3 py-1.5 font-medium text-white hover:bg-green-700">Setujui</button>
                            <button type="button" wire:click="startReject({{ $p->id }})" class="rounded-lg border border-red-300 px-3 py-1.5 font-medium text-red-700 hover:bg-red-50">Tolak</button>
                        @endif
                        @if ($p->status->value === 'published')
                            <a href="{{ route('properties.show', $p->slug) }}" target="_blank" class="rounded-lg border border-gray-300 px-3 py-1.5 font-medium hover:bg-gray-50">Halaman publik</a>
                        @endif
                    </div>
                </div>

                @if ($p->status->value === 'rejected' && $p->rejection_reason)
                    <p class="text-sm text-red-700">Alasan penolakan: {{ $p->rejection_reason }}</p>
                @endif

                @if ($detail && $expandedId === $p->id)
                    <div class="space-y-4 rounded-xl bg-gray-50 p-4 text-sm">
                        @if ($detail->images->isNotEmpty())
                            <div class="flex gap-2 overflow-x-auto">
                                @foreach ($detail->images as $img)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($img->path) }}" alt="" class="h-28 w-40 shrink-0 rounded-lg object-cover" onerror="this.remove()">
                                @endforeach
                            </div>
                        @else
                            <p class="text-amber-700">Belum ada foto.</p>
                        @endif

                        <dl class="grid gap-3 sm:grid-cols-3">
                            <div><dt class="text-gray-500">Tipe</dt><dd class="font-medium">{{ $detail->property_type->value === 'house' ? 'Rumah' : 'Ruko' }} · {{ $detail->listing_type->value === 'sale' ? 'Dijual' : 'Disewa' }}</dd></div>
                            <div><dt class="text-gray-500">Kamar tidur / mandi</dt><dd class="font-medium">{{ $detail->bedrooms }} / {{ $detail->bathrooms }}</dd></div>
                            <div><dt class="text-gray-500">Lantai</dt><dd class="font-medium">{{ $detail->floors }}</dd></div>
                            <div><dt class="text-gray-500">Luas tanah / bangunan</dt><dd class="font-medium">{{ $detail->land_area ? number_format((float) $detail->land_area, 0, ',', '.') : '-' }} / {{ $detail->building_area ? number_format((float) $detail->building_area, 0, ',', '.') : '-' }} m²</dd></div>
                            <div><dt class="text-gray-500">Sertifikat</dt><dd class="font-medium">{{ $detail->certificate ?: '-' }}</dd></div>
                            <div><dt class="text-gray-500">Harga</dt><dd class="font-medium"><x-rupiah :value="$detail->price" /></dd></div>
                            <div class="sm:col-span-3"><dt class="text-gray-500">Alamat</dt>
                                <dd class="font-medium">{{ $detail->address }}, {{ $detail->district ? $detail->district.', ' : '' }}{{ $detail->city }}, {{ $detail->province }}
                                    @if ($detail->latitude !== null && $detail->longitude !== null)
                                        · <a href="https://www.google.com/maps/search/?api=1&query={{ $detail->latitude }},{{ $detail->longitude }}" target="_blank" rel="noopener" class="text-blue-700 hover:underline">Lihat di peta</a>
                                    @else
                                        · <span class="text-amber-700">tanpa koordinat</span>
                                    @endif
                                </dd>
                            </div>
                            @if ($detail->facilities->isNotEmpty())
                                <div class="sm:col-span-3"><dt class="text-gray-500">Fasilitas</dt><dd class="font-medium">{{ $detail->facilities->pluck('name')->join(', ') }}</dd></div>
                            @endif
                            <div class="sm:col-span-3"><dt class="text-gray-500">Deskripsi</dt><dd class="whitespace-pre-line">{{ $detail->description ?: '-' }}</dd></div>
                            <div class="sm:col-span-3"><dt class="text-gray-500">Penjual</dt>
                                <dd class="font-medium">{{ $detail->partner?->partnerProfile?->business_name ?? $detail->partner?->name }} · {{ $detail->partner?->email }}
                                    @if ($detail->partner?->partnerProfile) · <x-status-badge :status="$detail->partner->partnerProfile->verification_status" context="verification" />@endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endif

                @if ($rejectingId === $p->id)
                    <form wire:submit="reject" class="space-y-2 rounded-xl border border-red-200 bg-red-50 p-4">
                        <label class="block text-sm font-medium">Alasan penolakan (dilihat oleh penjual)</label>
                        <textarea rows="2" wire:model="reason" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="mis. Foto tidak sesuai, data luas tidak wajar"></textarea>
                        @error('reason')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        <div class="flex gap-2">
                            <button type="submit" class="rounded-lg bg-red-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-red-700">Tolak properti</button>
                            <button type="button" wire:click="cancelReject" class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium hover:bg-gray-50">Batal</button>
                        </div>
                    </form>
                @endif
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Tidak ada properti pada filter ini.</div>
        @endforelse
    </div>

    <div>{{ $properties->links() }}</div>
</div>
