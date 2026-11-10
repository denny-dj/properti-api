@php
    $inputClass = 'mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all';
    $labelClass = 'block text-xs font-bold uppercase tracking-wider text-slate-600';
@endphp

{{-- Aset Leaflet CDN via Cloudflare --}}
@once
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
@endonce

<div class="space-y-8 pb-16">
    
    {{-- Header Halaman & Status --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-center">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('partner.properties.index') }}" class="hover:text-blue-600 transition-colors">Daftar Properti</a>
                <span>/</span>
                <span class="text-slate-700">{{ $property ? 'Ubah Data Unit' : 'Registrasi Listing Baru' }}</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                {{ $property ? 'Ubah Properti' : 'Tambah Properti Baru' }}
            </h1>
            <p class="mt-1 text-xs text-slate-500">Lengkapi spesifikasi, legalitas, dan titik lokasi akurat unit properti Anda.</p>
        </div>

        @if ($property)
            <div class="flex items-center gap-2">
                <x-status-badge :status="$property->status" context="property" />
            </div>
        @endif
    </div>

    {{-- Alert Penolakan Review --}}
    @if ($property && $property->status->value === 'rejected' && $property->rejection_reason)
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-xs text-rose-800 shadow-sm flex items-start gap-3">
            <svg class="h-5 w-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <strong class="font-bold">Pengajuan Ditolak Administrator:</strong> {{ $property->rejection_reason }}.
                <p class="mt-1 text-rose-700">Silakan perbaiki data yang diminta di bawah, simpan, lalu ajukan verifikasi ulang.</p>
            </div>
        </div>
    @endif

    {{-- Notifikasi Sukses / Galat Global --}}
    @if ($notice)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800 shadow-sm flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ $notice }}</span>
        </div>
    @endif

    @error('form')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700 shadow-sm">
            {{ $message }}
        </div>
    @enderror

    <form wire:submit="save" class="space-y-8">
        
        {{-- SECTION 1: INFORMASI DASAR --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-7 shadow-sm space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">1. Informasi Dasar</h2>
                <p class="text-xs text-slate-400 mt-0.5">Judul, model penawaran, dan harga jual unit.</p>
            </div>

            <div>
                <label class="{{ $labelClass }}">Judul Iklan Properti</label>
                <input type="text" wire:model="title" class="{{ $inputClass }}" placeholder="mis. Rumah Minimalis Scandinavian 2 Lantai Dekat Stasiun Depok">
                @error('title')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <label class="{{ $labelClass }}">Tipe Properti</label>
                    <select wire:model="property_type" class="{{ $inputClass }} cursor-pointer">
                        <option value="house">Rumah Tapak</option>
                        <option value="shop">Ruko / Komersial</option>
                    </select>
                    @error('property_type')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">Model Transaksi</label>
                    <select wire:model="listing_type" class="{{ $inputClass }} cursor-pointer">
                        <option value="sale">Dijual (Kepemilikan)</option>
                        <option value="rent">Disewa (Sewa Berkala)</option>
                    </select>
                    @error('listing_type')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">Harga (Rp)</label>
                    <div class="relative">
                        <input type="number" min="1" step="any" wire:model.live.debounce.300ms="price" class="{{ $inputClass }}" placeholder="mis. 1250000000">
                    </div>
                    @if ($price && is_numeric($price))
                        <div class="mt-1.5 flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 border border-emerald-100">
                            <svg class="h-3.5 w-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Terbaca: <strong>Rp {{ number_format((float) $price, 0, ',', '.') }}</strong></span>
                        </div>
                    @endif
                    @error('price')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="{{ $labelClass }}">Deskripsi Lengkap & Keunggulan</label>
                <textarea rows="5" wire:model="description" class="{{ $inputClass }}" placeholder="Jelaskan spesifikasi material, aksesibilitas, fasilitas sekitar, dan legalitas surat kepemilikan..."></textarea>
                @error('description')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
            </div>
        </section>

        {{-- SECTION 2: SPESIFIKASI FISIK & LEGALITAS --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-7 shadow-sm space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">2. Spesifikasi Fisik & Legalitas</h2>
                <p class="text-xs text-slate-400 mt-0.5">Dimensi bangunan, jumlah ruang, dan dokumen kepemilikan.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                @foreach ([
                    ['bedrooms', 'Kamar Tidur', '0'],
                    ['bathrooms', 'Kamar Mandi', '0'],
                    ['floors', 'Jumlah Lantai', '1'],
                    ['land_area', 'Luas Tanah (m²)', '1'],
                    ['building_area', 'Luas Bangunan (m²)', '1'],
                ] as [$field, $label, $min])
                    <div>
                        <label class="{{ $labelClass }}">{{ $label }}</label>
                        <input type="number" min="{{ $min }}" step="any" wire:model="{{ $field }}" class="{{ $inputClass }}">
                        @error($field)<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                @endforeach

                <div>
                    <label class="{{ $labelClass }}">Sertifikat Kepemilikan</label>
                    <select wire:model="certificate" class="{{ $inputClass }} cursor-pointer">
                        <option value="">Pilih Jenis Sertifikat</option>
                        @foreach (['SHM', 'HGB', 'HP', 'Girik', 'Lainnya'] as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                    @error('certificate')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- SECTION 3: LOKASI & PETA LEAFLET INTERAKTIF --}}
        <section class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-7 shadow-sm space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">3. Lokasi & Titik Koordinat Peta</h2>
                <p class="text-xs text-slate-400 mt-0.5">Tentukan alamat dan titik presisi unit untuk ditampilkan pada peta interaktif.</p>
            </div>

            <div>
                <label class="{{ $labelClass }}">Alamat Jalan / Cluster</label>
                <input type="text" wire:model="address" class="{{ $inputClass }}" placeholder="Nama jalan, nomor rumah, RT/RW, nama cluster">
                @error('address')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ([['district', 'Kecamatan'], ['city', 'Kota / Kabupaten'], ['province', 'Provinsi'], ['postal_code', 'Kode Pos']] as [$field, $label])
                    <div>
                        <label class="{{ $labelClass }}">{{ $label }}</label>
                        <input type="text" wire:model="{{ $field }}" class="{{ $inputClass }}">
                        @error($field)<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>

            {{-- Tombol Geocoding Otomatis --}}
            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="button" wire:click="locate" wire:loading.attr="disabled" wire:target="locate" 
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-400 transition-all cursor-pointer">
                    <svg wire:loading.remove wire:target="locate" class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <svg wire:loading wire:target="locate" class="h-4 w-4 animate-spin text-slate-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span wire:loading.remove wire:target="locate">Cari koordinat dari teks alamat</span>
                    <span wire:loading wire:target="locate">Mencari titik koordinat...</span>
                </button>
                <span class="text-[11px] text-slate-400">Atau klik langsung pada peta di bawah untuk menandai lokasi.</span>
                @error('geo')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
            </div>

            {{-- Komponen Peta Leaflet Picker Menggunakan leafletPicker() --}}
            <div class="space-y-2">
                <div wire:ignore x-data="leafletPicker($wire)">
                    <div x-ref="mapContainer" style="height: 320px; min-height: 320px;" class="w-full rounded-2xl bg-slate-100 border border-slate-200 z-0 shadow-inner"></div>
                </div>
                <div class="flex items-center gap-2 text-[11px] text-slate-500">
                    <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span><strong>Tips Peta:</strong> Seret pin hitam atau klik di mana saja pada peta untuk memperbarui koordinat secara instan.</span>
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 pt-2">
                <div>
                    <label class="{{ $labelClass }}">Latitude</label>
                    <input type="text" wire:model="latitude" class="{{ $inputClass }}" placeholder="-6.2615000">
                    @error('latitude')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $labelClass }}">Longitude</label>
                    <input type="text" wire:model="longitude" class="{{ $inputClass }}" placeholder="106.8106000">
                    @error('longitude')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- SECTION 4: FASILITAS PROPERTI --}}
        @if ($facilities->isNotEmpty())
            <section class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-7 shadow-sm space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">4. Fasilitas Tersedia</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Pilih kelengkapan fasilitas yang menyertai unit properti ini.</p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($facilities as $facility)
                        <label class="flex items-center gap-2.5 rounded-xl border border-slate-200/80 p-3 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer select-none">
                            <input type="checkbox" wire:model="facility_ids" value="{{ $facility->id }}" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900/20">
                            <span>{{ $facility->name }}</span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Tombol Simpan Form --}}
        <div class="flex flex-wrap items-center gap-4 pt-2">
            <button type="submit" wire:loading.attr="disabled" wire:target="save" 
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-7 py-3 text-xs font-bold text-white shadow-md hover:bg-slate-800 transition-all cursor-pointer">
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span wire:loading.remove wire:target="save">{{ $property ? 'Simpan Perubahan Unit' : 'Simpan sebagai Draft' }}</span>
                <span wire:loading wire:target="save">Menyimpan data...</span>
            </button>
            <a href="{{ route('partner.properties.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                Kembali ke Daftar Properti
            </a>
        </div>
    </form>

    {{-- SECTION 5: MANAJEMEN FOTO --}}
    @if ($property)
        <section class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-7 shadow-sm space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">5. Galeri Foto Properti ({{ $images->count() }}/10)</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Unggah foto fasad, interior, dan lingkungan sekitar unit beresolusi tinggi.</p>
                </div>
                <span class="rounded-xl bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">Maks. 10 Berkas</span>
            </div>

            @if ($images->isNotEmpty())
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($images as $image)
                        <div wire:key="img-{{ $image->id }}" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md">
                            <div class="relative aspect-[4/3] bg-slate-100 overflow-hidden">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}" alt="" class="h-full w-full object-cover transition-transform group-hover:scale-105" onerror="this.remove()">
                                @if ($image->is_cover)
                                    <span class="absolute left-2.5 top-2.5 rounded-lg bg-slate-900/90 px-2 py-0.5 text-[10px] font-bold text-white backdrop-blur shadow">
                                        Foto Sampul
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between p-3 text-xs bg-white border-t border-slate-100">
                                @if (! $image->is_cover)
                                    <button type="button" wire:click="makeCover({{ $image->id }})" class="font-semibold text-blue-600 hover:text-blue-700 transition-colors">Jadikan Sampul</button>
                                @else
                                    <span class="text-[11px] font-bold text-emerald-600">✓ Utama</span>
                                @endif
                                <button type="button" wire:click="deleteImage({{ $image->id }})" wire:confirm="Hapus foto ini?" class="font-semibold text-rose-500 hover:text-rose-700 transition-colors">Hapus</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/50 p-6">
                <input type="file" wire:model="photos" multiple accept="image/jpeg,image/png,image/webp" 
                       class="block w-full text-xs text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-slate-800 transition-all cursor-pointer">
                
                <div wire:loading wire:target="photos" class="mt-2 text-xs font-medium text-slate-500 flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5 animate-spin text-slate-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Mengunggah berkas sementara...</span>
                </div>

                @error('photos')<p class="mt-2 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                @error('photos.*')<p class="mt-2 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror

                @if (count($photos) > 0)
                    <div class="mt-4 flex flex-wrap gap-3 pt-3 border-t border-slate-200">
                        @foreach ($photos as $photo)
                            @if ($photo->isPreviewable())
                                <img src="{{ $photo->temporaryUrl() }}" alt="" class="h-16 w-24 rounded-xl object-cover border border-slate-200 shadow-sm">
                            @endif
                        @endforeach
                    </div>
                    <button type="button" wire:click="uploadPhotos" wire:loading.attr="disabled" wire:target="uploadPhotos" 
                            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow hover:bg-blue-700 transition-all cursor-pointer">
                        <span wire:loading.remove wire:target="uploadPhotos">Unggah {{ count($photos) }} Foto Sekarang</span>
                        <span wire:loading wire:target="uploadPhotos">Memproses unggahan...</span>
                    </button>
                @endif
                <p class="mt-2 text-[11px] text-slate-400">Format yang didukung: JPG, PNG, atau WEBP (maksimal 4 MB per berkas foto). Foto pertama otomatis dijadikan foto sampul iklan.</p>
            </div>
        </section>

        {{-- SECTION 6: PENGAJUAN VERIFIKASI KE ADMIN --}}
        @if (in_array($property->status->value, ['draft', 'rejected'], true))
            <section class="rounded-3xl border border-slate-200/80 bg-slate-900 p-6 sm:p-8 text-white shadow-xl">
                <span class="inline-flex items-center rounded-md bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                    Tahap Publikasi
                </span>
                <h3 class="mt-3 text-base sm:text-lg font-bold text-white">Siap Menayangkan Listing Ini ke Publik?</h3>
                <p class="mt-1 text-xs text-slate-300 leading-relaxed max-w-2xl">
                    Ajukan unit ini ke tim kurasi administrator. Pastikan data legalitas, spesifikasi, dan foto sudah lengkap dan tersimpan.
                </p>
                <button type="button" wire:click="submit" wire:loading.attr="disabled" 
                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-6 py-2.5 text-xs font-bold text-slate-900 shadow-md hover:bg-emerald-400 transition-all cursor-pointer">
                    <span>Ajukan untuk Diverifikasi Sekarang</span> &rarr;
                </button>
            </section>
        @endif
    @endif

</div>

{{-- Skrip Alpine Leaflet Map Picker Mandiri --}}
@once
<script>
function leafletPicker($wire) {
    let map = null;
    let marker = null;

    return {
        isReady: false,

        init() {
            if (this.isReady) return;
            this.isReady = true;

            // Pastikan modul Leaflet CDN siap sebelum peta dipasang
            if (typeof L !== 'undefined') {
                this.$nextTick(() => this.mountMap());
            } else {
                let attempts = 0;
                const interval = setInterval(() => {
                    attempts++;
                    if (typeof L !== 'undefined') {
                        clearInterval(interval);
                        this.$nextTick(() => this.mountMap());
                    } else if (attempts > 50) {
                        clearInterval(interval);
                        console.error('Leaflet CDN gagal dimuat di property form.');
                    }
                }, 100);
            }

            // Sinkronisasi saat Livewire mengubah latitude/longitude
            this.$watch('$wire.latitude', () => this.syncFromWire());
            this.$watch('$wire.longitude', () => this.syncFromWire());
        },

        mountMap() {
            const el = this.$refs.mapContainer;
            if (!el) return;

            if (el._leaflet_id) {
                delete el._leaflet_id;
            }
            if (map) {
                map.remove();
                map = null;
            }

            let rawLat = parseFloat($wire.get('latitude'));
            let rawLng = parseFloat($wire.get('longitude'));

            let hasValidCoord = !isNaN(rawLat) && !isNaN(rawLng) && rawLat !== 0 && rawLng !== 0;
            let startLat = hasValidCoord ? rawLat : -6.2615;
            let startLng = hasValidCoord ? rawLng : 106.8106;
            let startZoom = hasValidCoord ? 16 : 11;

            map = L.map(el, {
                scrollWheelZoom: true,
                zoomControl: false
            }).setView([startLat, startLng], startZoom);

            L.control.zoom({ position: 'bottomright' }).addTo(map);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            const pickerIcon = L.divIcon({
                className: 'picker-pin',
                html: `
                    <div style="position: relative; display: flex; align-items: center; justify-content: center; cursor: grab;">
                        <div style="height: 36px; width: 36px; border-radius: 9999px; background-color: #0f172a; color: white; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3); display: flex; align-items: center; justify-content: center; border: 2px solid white;">
                            <svg style="height: 18px; width: 18px; color: #34d399;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div style="position: absolute; bottom: -5px; height: 9px; width: 9px; transform: rotate(45deg); background-color: #0f172a;"></div>
                    </div>
                `,
                iconSize: [36, 42],
                iconAnchor: [18, 42],
                popupAnchor: [0, -42]
            });

            marker = L.marker([startLat, startLng], {
                draggable: true,
                icon: pickerIcon
            }).addTo(map);

            marker.on('dragend', () => {
                const pos = marker.getLatLng();
                $wire.set('latitude', pos.lat.toFixed(7));
                $wire.set('longitude', pos.lng.toFixed(7));
            });

            map.on('click', (e) => {
                marker.setLatLng(e.latlng);
                $wire.set('latitude', e.latlng.lat.toFixed(7));
                $wire.set('longitude', e.latlng.lng.toFixed(7));
            });

            setTimeout(() => {
                if (map) {
                    map.invalidateSize();
                }
            }, 300);
        },

        syncFromWire() {
            if (!map || !marker) return;
            let lat = parseFloat($wire.get('latitude'));
            let lng = parseFloat($wire.get('longitude'));

            if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                const cur = marker.getLatLng();
                if (cur.lat.toFixed(6) !== lat.toFixed(6) || cur.lng.toFixed(6) !== lng.toFixed(6)) {
                    marker.setLatLng([lat, lng]);
                    map.flyTo([lat, lng], 16, { duration: 1.2 });
                }
            }
        }
    };
}
</script>
@endonce