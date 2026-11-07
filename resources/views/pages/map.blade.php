<x-layouts.app title="Peta properti">

{{-- Leaflet Assets CDN --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<div class="min-h-screen bg-slate-50/50 pb-16" x-data="mapPage()">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        
        {{-- Header Editorial & Filter Bar --}}
        <div class="mb-6 flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 lg:flex-row lg:items-end">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                    <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
                    <span>/</span>
                    <span class="text-slate-700">Peta properti</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Peta properti</h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">Geser atau zoom area peta untuk memuat unit properti terverifikasi secara langsung.</p>
            </div>

            {{-- Filter Bar --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <select x-model="type" x-on:change="load()" 
                            class="rounded-xl border border-slate-200 bg-white py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="">Semua Tipe Properti</option>
                        <option value="house">Rumah Tapak</option>
                        <option value="shop">Ruko / Komersial</option>
                    </select>
                </div>

                <div class="relative">
                    <input type="number" min="0" step="50000000" placeholder="Harga maks (Rp)" 
                           x-model.debounce.600ms="maxPrice" x-on:input.debounce.600ms="load()" 
                           class="w-44 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                </div>

                <div class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-600 shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span><strong class="font-bold text-slate-900" x-text="count">0</strong> unit di layar</span>
                </div>
            </div>
        </div>

        {{-- Container Peta --}}
        <div class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-2 shadow-md">
            <div x-show="!failed" wire:ignore>
                <div x-ref="mapContainer" style="height: 70vh; min-height: 540px;" class="w-full rounded-2xl bg-slate-100 z-0"></div>
            </div>

            <div x-show="failed" x-cloak class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center" style="height: 70vh; min-height: 540px;">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 mb-3">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Peta Tidak Dapat Dimuat</h3>
                <p class="mt-1 text-xs text-rose-500 font-medium max-w-md" x-text="errorMessage || 'Periksa koneksi internet Anda untuk memuat peta interaktif.'"></p>
                <a href="{{ route('properties.index') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white">
                    Buka Katalog Properti &rarr;
                </a>
            </div>
        </div>

    </div>
</div>

<script>
    function mapPage() {
        var map = null, markersLayer = null, timer = null;

        return {
            type: '', 
            maxPrice: '', 
            count: 0, 
            failed: false,
            errorMessage: '',
            isReady: false,

            init() {
                if (this.isReady) return;
                this.isReady = true;

                this.$nextTick(() => {
                    this.mountMap();
                });
            },

            mountMap() {
                try {
                    const el = this.$refs.mapContainer;
                    if (!el) return;

                    if (map) {
                        map.remove();
                        map = null;
                    } else if (el._leaflet_id) {
                        delete el._leaflet_id;
                    }

                    map = L.map(el, {
                        scrollWheelZoom: true,
                        zoomControl: false
                    }).setView([-6.2615, 106.8106], 11);

                    L.control.zoom({ position: 'bottomright' }).addTo(map);

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(map);

                    markersLayer = L.layerGroup().addTo(map);

                    map.on('moveend', () => {
                        clearTimeout(timer);
                        timer = setTimeout(() => this.load(), 300);
                    });

                    setTimeout(() => {
                        if (map) {
                            map.invalidateSize();
                            this.load();
                        }
                    }, 250);

                } catch (e) {
                    console.error('Leaflet Mount Error:', e);
                    this.failed = true;
                    this.errorMessage = e.message;
                }
            },

            async load() {
                if (!map) return;
                var bounds = map.getBounds();
                
                var p = new URLSearchParams({
                    south: bounds.getSouth(),
                    west: bounds.getWest(),
                    north: bounds.getNorth(),
                    east: bounds.getEast(),
                    limit: 200
                });

                if (this.type) { p.set('property_type', this.type); }
                if (this.maxPrice) { p.set('max_price', this.maxPrice); }

                try {
                    var res = await fetch('/api/v1/properties/markers?' + p.toString(), {
                        headers: { Accept: 'application/json' }
                    });
                    var json = await res.json();
                    this.draw(json.data || []);
                } catch (e) {
                    // Penanganan galat fetch sesaat
                }
            },

            draw(items) {
                if (!markersLayer) return;
                markersLayer.clearLayers();

                var propertyPinIcon = L.divIcon({
                    className: 'concierge-pin',
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

                items.forEach(function (it) {
                    var marker = L.marker([it.latitude, it.longitude], {
                        icon: propertyPinIcon,
                        title: it.title
                    });

                    var box = document.createElement('div');
                    box.style.minWidth = '200px';
                    box.style.maxWidth = '240px';
                    box.style.padding = '4px';

                    var a = document.createElement('a');
                    a.href = '/properti/' + encodeURIComponent(it.slug);
                    a.textContent = it.title;
                    a.style.display = 'block';
                    a.style.fontWeight = '700';
                    a.style.fontSize = '13px';
                    a.style.color = '#0f172a';
                    a.style.lineHeight = '1.3';
                    a.style.textDecoration = 'none';

                    var price = document.createElement('div');
                    price.textContent = it.price_formatted;
                    price.style.fontWeight = '800';
                    price.style.color = '#1d4ed8';
                    price.style.fontSize = '13px';
                    price.style.marginTop = '4px';

                    var link = document.createElement('a');
                    link.href = '/properti/' + encodeURIComponent(it.slug);
                    link.textContent = 'Lihat Detail Unit →';
                    link.style.display = 'inline-block';
                    link.style.fontSize = '11px';
                    link.style.fontWeight = '600';
                    link.style.color = '#64748b';
                    link.style.marginTop = '6px';
                    link.style.textDecoration = 'none';

                    box.appendChild(a);
                    box.appendChild(price);
                    box.appendChild(link);

                    marker.bindPopup(box);
                    markersLayer.addLayer(marker);
                });

                this.count = items.length;
            }
        };
    }
</script>
</x-layouts.app>