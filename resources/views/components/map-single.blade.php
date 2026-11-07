{{-- Jika menggunakan google maps --}}
{{-- @props(['lat', 'lng', 'title' => ''])
<div wire:ignore
     x-data="{ failed: false }"
     x-init="loadGoogleMaps().then(function () {
        var pos = { lat: {{ (float) $lat }}, lng: {{ (float) $lng }} };
        var map = new google.maps.Map($refs.map, { center: pos, zoom: 15, mapTypeControl: false, streetViewControl: false });
        new google.maps.Marker({ position: pos, map: map, title: @js($title) });
     }).catch(function () { failed = true; })">
    <div x-ref="map" x-show="!failed" class="h-72 w-full rounded-xl bg-gray-100"></div>
    <div x-show="failed" x-cloak class="flex h-40 items-center justify-center rounded-xl bg-gray-100 px-4 text-center text-sm text-gray-500">
        Peta interaktif belum tersedia. Gunakan tautan di bawah untuk membuka lokasi.
    </div>
    <a href="https://www.google.com/maps/search/?api=1&query={{ $lat }},{{ $lng }}" target="_blank" rel="noopener"
       class="mt-2 inline-block text-sm font-medium text-blue-700 hover:underline">Buka di Google Maps &rarr;</a>
</div> --}}


{{-- maps menggunakan openstreet map --}}
@props(['lat', 'lng', 'title' => ''])

{{-- Pemuatan CDN Leaflet Khusus Saat Komponen Peta Muncul --}}
@once
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
@endonce

<div wire:ignore
     x-data="mapSingle({{ (float) $lat }}, {{ (float) $lng }}, @js($title))"
     class="space-y-3">
    
    {{-- Container Peta Interaktif --}}
    <div x-show="!failed">
        <div x-ref="mapContainer" style="height: 320px; min-height: 320px;" class="w-full rounded-2xl bg-slate-100 z-0 border border-slate-200/80 shadow-inner"></div>
    </div>

    {{-- Fallback jika Koordinat Tidak Valid / Gagal --}}
    <div x-show="failed" x-cloak class="flex h-44 flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-4 text-center text-xs text-slate-500">
        <svg class="h-6 w-6 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <span>Peta interaktif belum dapat dimuat. Gunakan tautan navigasi di bawah untuk melihat titik lokasi.</span>
    </div>

    {{-- Tautan Navigasi Keluar ke Aplikasi Maps --}}
    <a href="https://www.google.com/maps/search/?api=1&query={{ (float) $lat }},{{ (float) $lng }}" 
       target="_blank" rel="noopener"
       class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
        <span>Buka di Google Maps &rarr;</span>
    </a>
</div>

@once
<script>
function mapSingle(lat, lng, title) {
    var map = null;

    return {
        failed: false,
        isReady: false,

        init() {
            if (this.isReady) return;
            this.isReady = true;

            // Validasi nilai koordinat
            if (!lat || !lng || isNaN(lat) || isNaN(lng)) {
                this.failed = true;
                return;
            }

            if (typeof L !== 'undefined') {
                this.$nextTick(() => this.mountMap());
                return;
            }

            let attempts = 0;
            const interval = setInterval(() => {
                attempts++;
                if (typeof L !== 'undefined') {
                    clearInterval(interval);
                    this.$nextTick(() => this.mountMap());
                } else if (attempts > 50) {
                    clearInterval(interval);
                    this.failed = true;
                }
            }, 100);
        },

        mountMap() {
            try {
                const el = this.$refs.mapContainer;
                if (!el) return;

                if (el._leaflet_id) {
                    delete el._leaflet_id;
                }
                if (map) {
                    map.remove();
                    map = null;
                }

                // Inisialisasi peta Leaflet terpusat di koordinat properti
                map = L.map(el, {
                    scrollWheelZoom: false,
                    zoomControl: true
                }).setView([lat, lng], 15);

                // Basemap OpenStreetMap
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);

                // Pin SVG Konsisten Bergaya Arsitektur
                var singlePinIcon = L.divIcon({
                    className: 'concierge-pin-single',
                    html: `
                        <div style="position: relative; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                            <div style="height: 32px; width: 32px; border-radius: 9999px; background-color: #0f172a; color: white; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2); display: flex; align-items: center; justify-content: center; border: 2px solid white;">
                                <svg style="height: 16px; width: 16px; color: #34d399;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <div style="position: absolute; bottom: -4px; height: 8px; width: 8px; transform: rotate(45deg); background-color: #0f172a;"></div>
                        </div>
                    `,
                    iconSize: [32, 36],
                    iconAnchor: [16, 36],
                    popupAnchor: [0, -36]
                });

                var marker = L.marker([lat, lng], { icon: singlePinIcon }).addTo(map);
                
                if (title) {
                    var safeTitle = document.createElement('div');
                    safeTitle.textContent = title;
                    safeTitle.style.fontWeight = '700';
                    safeTitle.style.fontSize = '12px';
                    safeTitle.style.padding = '2px 4px';
                    marker.bindPopup(safeTitle).openPopup();
                }

                // Kalkulasi ulang ukuran kontainer
                setTimeout(() => {
                    if (map) {
                        map.invalidateSize();
                    }
                }, 250);

            } catch (e) {
                console.error('Map single mount error:', e);
                this.failed = true;
            }
        }
    };
}
</script>
@endonce