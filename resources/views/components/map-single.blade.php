@props(['lat', 'lng', 'title' => ''])
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
</div>
