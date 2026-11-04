@php
    $input = 'mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none';
@endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ $portfolioId ? 'Ubah portofolio' : 'Portofolio baru' }}</h1>
        <a href="{{ route('partner.portfolios.index') }}" class="text-sm font-medium text-blue-700 hover:underline">&larr; Kembali ke daftar</a>
    </div>

    @if ($notice)<p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>@endif
    @error('form')<p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror

    <form wire:submit="save" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-5">
        <div>
            <label class="block text-sm font-medium">Judul</label>
            <input type="text" wire:model="title" class="{{ $input }}" placeholder="mis. Rumah Japandi 2 Lantai 120 m²">
            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Kategori</label>
                <select wire:model="category" class="{{ $input }}">
                    @foreach ($categories as $option)<option value="{{ $option->value }}">{{ $option->label() }}</option>@endforeach
                </select>
                @error('category')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Gaya desain</label>
                <select wire:model="architectural_style" class="{{ $input }}">
                    @foreach ($styles as $option)<option value="{{ $option->value }}">{{ $option->label() }}</option>@endforeach
                </select>
                @error('architectural_style')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Estimasi RAB (Rp)</label>
                <input type="text" inputmode="numeric" wire:model="estimated_rab" class="{{ $input }}" placeholder="600000000">
                @error('estimated_rab')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Luas bangunan (m²)</label>
                <input type="text" inputmode="decimal" wire:model="area_sqm" class="{{ $input }}" placeholder="120">
                @error('area_sqm')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <p class="-mt-2 text-xs text-gray-500">Biaya per m² dihitung otomatis dari RAB dibagi luas.</p>

        <div>
            <label class="block text-sm font-medium">Deskripsi</label>
            <textarea rows="5" wire:model="description" class="{{ $input }}" placeholder="Konsep, material utama, dan keunggulan desain."></textarea>
            @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-3 rounded-xl border border-green-200 bg-green-50 p-4">
            <label class="flex items-center gap-2 text-sm font-medium text-green-900">
                <input type="checkbox" wire:model.live="is_ready_to_build" class="rounded border-gray-300">
                Jual sebagai desain siap bangun (Jalur A)
            </label>

            @if ($is_ready_to_build)
                <div>
                    <label class="block text-sm font-medium">Harga paket (Rp)</label>
                    <input type="text" inputmode="numeric" wire:model="blueprint_price" class="{{ $input }}" placeholder="5000000">
                    @error('blueprint_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium">Berkas yang diserahkan (satu per baris)</label>
                    <textarea rows="4" wire:model="deliverables" class="{{ $input }}" placeholder="DED (AutoCAD)&#10;Model 3D (SketchUp)&#10;RAB (Excel)"></textarea>
                    @error('deliverables')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <p class="text-xs text-green-800">Berkas final diserahkan lewat tautan Google Drive setelah pembeli membayar. Dana Anda dilepas setelah pembeli mengonfirmasi penerimaan.</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium">Status</label>
            <select wire:model="status" class="{{ $input }} sm:max-w-xs">
                <option value="draft">Draft (belum tampil)</option>
                <option value="published">Tayang (minimal 1 foto)</option>
                <option value="archived">Diarsipkan</option>
            </select>
            @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- Foto --}}
        <div class="space-y-3 border-t border-gray-100 pt-5">
            <h2 class="font-semibold">Foto ({{ $images->count() }}/10)</h2>
            @error('photos')<p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror

            @if ($images->isNotEmpty())
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($images as $image)
                        <div wire:key="pimg-{{ $image->id }}" class="overflow-hidden rounded-xl border border-gray-200">
                            <div class="relative aspect-[4/3] bg-gray-100">
                                <img src="{{ $image->url() }}" alt="" class="h-full w-full object-cover" onerror="this.remove()">
                                @if ($image->is_cover)<span class="absolute left-2 top-2 rounded bg-blue-600 px-2 py-0.5 text-xs font-medium text-white">Sampul</span>@endif
                            </div>
                            <div class="flex justify-between gap-2 px-2 py-2 text-xs">
                                @if (! $image->is_cover)
                                    <button type="button" wire:click="makeCover({{ $image->id }})" class="font-medium text-blue-700 hover:underline">Jadikan sampul</button>
                                @else
                                    <span></span>
                                @endif
                                <button type="button" wire:click="deleteImage({{ $image->id }})" wire:confirm="Hapus foto ini?" class="font-medium text-red-600 hover:underline">Hapus</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div>
                <input type="file" wire:model="photos" multiple accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:font-medium file:text-blue-700">
                <div wire:loading wire:target="photos" class="mt-2 text-sm text-gray-500">Mengunggah berkas...</div>
                @error('photos.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

                @if (count($photos) > 0)
                    <div class="mt-3 flex flex-wrap gap-3">
                        @foreach ($photos as $i => $photo)
                            @if ($photo->isPreviewable())
                                <div class="relative" wire:key="new-{{ $i }}">
                                    <img src="{{ $photo->temporaryUrl() }}" alt="" class="h-16 w-24 rounded-lg object-cover">
                                    <button type="button" wire:click="removeNewPhoto({{ $i }})" class="absolute -right-2 -top-2 h-5 w-5 rounded-full bg-red-600 text-xs leading-5 text-white">&times;</button>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Foto baru diunggah saat Anda menekan Simpan. Foto pertama menjadi sampul bila belum ada sampul.</p>
                @endif
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" wire:loading.attr="disabled" wire:target="save" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">
                <span wire:loading.remove wire:target="save">Simpan</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
            <a href="{{ route('partner.portfolios.index') }}" class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium hover:bg-gray-50">Batal</a>
        </div>
    </form>
</div>
