@php
    $input = 'mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none';
@endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ $property ? 'Ubah properti' : 'Tambah properti' }}</h1>
        @if ($property)
            <x-status-badge :status="$property->status" context="property" />
        @endif
    </div>

    @if ($property && $property->status->value === 'rejected' && $property->rejection_reason)
        <p class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">Ditolak administrator: {{ $property->rejection_reason }}. Perbaiki lalu ajukan kembali.</p>
    @endif
    @if ($notice)
        <p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>
    @endif
    @error('form')
        <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
    @enderror

    <form wire:submit="save" class="space-y-6">
        {{-- Informasi dasar --}}
        <section class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold">Informasi dasar</h2>
            <div>
                <label class="block text-sm font-medium">Judul iklan</label>
                <input type="text" wire:model="title" class="{{ $input }}" placeholder="mis. Rumah Minimalis 2 Lantai di Depok">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium">Tipe</label>
                    <select wire:model="property_type" class="{{ $input }}"><option value="house">Rumah</option><option value="shop">Ruko</option></select>
                    @error('property_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium">Dijual / disewa</label>
                    <select wire:model="listing_type" class="{{ $input }}"><option value="sale">Dijual</option><option value="rent">Disewa</option></select>
                    @error('listing_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium">Harga (Rp)</label>
                    <input type="number" min="1" step="any" wire:model="price" class="{{ $input }}">
                    @error('price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">Deskripsi</label>
                <textarea rows="5" wire:model="description" class="{{ $input }}"></textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </section>

        {{-- Spesifikasi --}}
        <section class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold">Spesifikasi</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['bedrooms', 'Kamar tidur', '0'],
                    ['bathrooms', 'Kamar mandi', '0'],
                    ['floors', 'Jumlah lantai', '1'],
                    ['land_area', 'Luas tanah (m²)', '1'],
                    ['building_area', 'Luas bangunan (m²)', '1'],
                ] as [$field, $label, $min])
                    <div>
                        <label class="block text-sm font-medium">{{ $label }}</label>
                        <input type="number" min="{{ $min }}" step="any" wire:model="{{ $field }}" class="{{ $input }}">
                        @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <div>
                    <label class="block text-sm font-medium">Sertifikat</label>
                    <select wire:model="certificate" class="{{ $input }}">
                        <option value="">-</option>
                        @foreach (['SHM', 'HGB', 'HP', 'Girik', 'Lainnya'] as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                    </select>
                    @error('certificate')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- Lokasi --}}
        <section class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold">Lokasi</h2>
            <div>
                <label class="block text-sm font-medium">Alamat</label>
                <input type="text" wire:model="address" class="{{ $input }}">
                @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([['district', 'Kecamatan'], ['city', 'Kota / Kabupaten'], ['province', 'Provinsi'], ['postal_code', 'Kode pos']] as [$field, $label])
                    <div>
                        <label class="block text-sm font-medium">{{ $label }}</label>
                        <input type="text" wire:model="{{ $field }}" class="{{ $input }}">
                        @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" wire:click="locate" wire:loading.attr="disabled" wire:target="locate" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                    <span wire:loading.remove wire:target="locate">Cari koordinat dari alamat</span>
                    <span wire:loading wire:target="locate">Mencari...</span>
                </button>
                @error('geo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div wire:ignore x-data="mapPicker($wire)" x-init="init()">
                <div x-show="!failed" x-ref="map" class="h-72 w-full rounded-xl bg-gray-100"></div>
                <p x-show="failed" x-cloak class="rounded-xl bg-gray-100 p-4 text-sm text-gray-500">Peta belum aktif (Google Maps API key belum dikonfigurasi). Isi latitude dan longitude secara manual di bawah.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium">Latitude</label>
                    <input type="text" wire:model="latitude" class="{{ $input }}" placeholder="-6.2615000">
                    @error('latitude')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium">Longitude</label>
                    <input type="text" wire:model="longitude" class="{{ $input }}" placeholder="106.8106000">
                    @error('longitude')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- Fasilitas --}}
        @if ($facilities->isNotEmpty())
            <section class="rounded-2xl border border-gray-200 bg-white p-5">
                <h2 class="font-semibold">Fasilitas</h2>
                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ($facilities as $facility)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" wire:model="facility_ids" value="{{ $facility->id }}" class="rounded border-gray-300"> {{ $facility->name }}
                        </label>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" wire:loading.attr="disabled" wire:target="save" class="rounded-lg bg-blue-600 px-6 py-2.5 font-medium text-white hover:bg-blue-700">
                <span wire:loading.remove wire:target="save">{{ $property ? 'Simpan perubahan' : 'Simpan sebagai draft' }}</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
            <a href="{{ route('partner.properties.index') }}" class="text-sm font-medium text-gray-600 hover:underline">Kembali ke daftar</a>
        </div>
    </form>

    {{-- Foto (hanya setelah properti tersimpan) --}}
    @if ($property)
        <section class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold">Foto ({{ $images->count() }}/10)</h2>

            @if ($images->isNotEmpty())
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($images as $image)
                        <div wire:key="img-{{ $image->id }}" class="overflow-hidden rounded-xl border border-gray-200">
                            <div class="relative aspect-[4/3] bg-gray-100">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}" alt="" class="h-full w-full object-cover" onerror="this.remove()">
                                @if ($image->is_cover)
                                    <span class="absolute left-2 top-2 rounded-full bg-blue-600 px-2 py-0.5 text-xs font-medium text-white">Sampul</span>
                                @endif
                            </div>
                            <div class="flex justify-between gap-2 p-2 text-xs">
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
                @error('photos')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                @error('photos.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

                @if (count($photos) > 0)
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($photos as $photo)
                            @if ($photo->isPreviewable())
                                <img src="{{ $photo->temporaryUrl() }}" alt="" class="h-16 w-24 rounded-lg object-cover">
                            @endif
                        @endforeach
                    </div>
                    <button type="button" wire:click="uploadPhotos" wire:loading.attr="disabled" wire:target="uploadPhotos" class="mt-3 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <span wire:loading.remove wire:target="uploadPhotos">Unggah {{ count($photos) }} foto</span>
                        <span wire:loading wire:target="uploadPhotos">Memproses...</span>
                    </button>
                @endif
                <p class="mt-2 text-xs text-gray-400">JPG, PNG, atau WEBP, maksimal 4 MB per foto. Foto pertama otomatis menjadi sampul.</p>
            </div>
        </section>

        @if (in_array($property->status->value, ['draft', 'rejected'], true))
            <section class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <h2 class="font-semibold text-blue-900">Siap tayang?</h2>
                <p class="mt-1 text-sm text-blue-800">Ajukan ke administrator untuk diverifikasi. Pastikan data dan foto sudah benar, serta simpan perubahan terlebih dahulu.</p>
                <button type="button" wire:click="submit" wire:loading.attr="disabled" class="mt-3 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">Ajukan untuk diverifikasi</button>
            </section>
        @endif
    @endif
</div>
