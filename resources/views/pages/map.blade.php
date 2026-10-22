<x-layouts.app title="Peta Properti">
<div class="mx-auto max-w-7xl px-4 py-6" x-data="mapPage()" x-init="init()">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Peta properti</h1>
            <p class="text-sm text-gray-500">Geser dan zoom peta; properti di area yang tampak dimuat otomatis.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select x-model="type" x-on:change="load()" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">Semua tipe</option>
                <option value="house">Rumah</option>
                <option value="shop">Ruko</option>
            </select>
            <input type="number" min="0" step="50000000" placeholder="Harga maks (Rp)" x-model.debounce.600ms="maxPrice" x-on:input.debounce.600ms="load()" class="w-48 rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <span class="text-sm text-gray-500" x-show="!failed"><span x-text="count"></span> properti</span>
        </div>
    </div>

    <div x-show="!failed" wire:ignore>
        <div x-ref="map" class="h-[70vh] w-full rounded-2xl bg-gray-100"></div>
    </div>
    <div x-show="failed" x-cloak class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
        Peta interaktif belum aktif karena Google Maps API key belum dikonfigurasi.
        <a href="{{ route('properties.index') }}" class="font-medium text-blue-700 hover:underline">Gunakan pencarian daftar</a> sebagai gantinya.
    </div>
</div>

<script>
    function mapPage() {
        // Objek Google Maps disimpan di closure (bukan di state Alpine) agar tidak dibungkus Proxy.
        var map = null, info = null, markers = [], timer = null;

        return {
            type: '', maxPrice: '', count: 0, failed: false,

            async init() {
                try { await loadGoogleMaps(); } catch (e) { this.failed = true; return; }
                map = new google.maps.Map(this.$refs.map, {
                    center: { lat: -6.2615, lng: 106.8106 }, zoom: 11,
                    mapTypeControl: false, streetViewControl: false
                });
                info = new google.maps.InfoWindow();
                map.addListener('idle', () => { clearTimeout(timer); timer = setTimeout(() => this.load(), 300); });
            },

            async load() {
                if (!map || !map.getBounds()) { return; }
                var b = map.getBounds(), ne = b.getNorthEast(), sw = b.getSouthWest();
                var p = new URLSearchParams({ south: sw.lat(), west: sw.lng(), north: ne.lat(), east: ne.lng(), limit: 200 });
                if (this.type) { p.set('property_type', this.type); }
                if (this.maxPrice) { p.set('max_price', this.maxPrice); }
                try {
                    var res = await fetch('/api/v1/properties/markers?' + p.toString(), { headers: { Accept: 'application/json' } });
                    var json = await res.json();
                    this.draw(json.data || []);
                } catch (e) { /* abaikan: peta tetap tampil */ }
            },

            draw(items) {
                markers.forEach(function (m) { m.setMap(null); });
                markers = [];
                items.forEach(function (it) {
                    var m = new google.maps.Marker({ position: { lat: it.latitude, lng: it.longitude }, map: map, title: it.title });
                    m.addListener('click', function () {
                        // DOM manual + textContent: judul berasal dari data penjual, jangan disisipkan sebagai HTML.
                        var box = document.createElement('div');
                        box.style.maxWidth = '220px';
                        var a = document.createElement('a');
                        a.href = '/properti/' + encodeURIComponent(it.slug);
                        a.textContent = it.title;
                        a.style.fontWeight = '600';
                        var price = document.createElement('div');
                        price.textContent = it.price_formatted;
                        price.style.color = '#1d4ed8';
                        box.appendChild(a); box.appendChild(price);
                        info.setContent(box);
                        info.open(map, m);
                    });
                    markers.push(m);
                });
                this.count = items.length;
            }
        };
    }
</script>
</x-layouts.app>
