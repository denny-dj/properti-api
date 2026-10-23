<script>
    // Pemilih lokasi: klik peta atau geser pin; latitude/longitude di formulir ikut berubah.
    function mapPicker($wire) {
        var map = null, marker = null;

        return {
            failed: false,

            async init() {
                var self = this;

                try { await loadGoogleMaps(); } catch (e) { this.failed = true; return; }

                var lat = parseFloat($wire.latitude), lng = parseFloat($wire.longitude);
                var has = !isNaN(lat) && !isNaN(lng);
                var center = has ? { lat: lat, lng: lng } : { lat: -6.2615, lng: 106.8106 };

                map = new google.maps.Map(this.$refs.map, {
                    center: center, zoom: has ? 16 : 11, mapTypeControl: false, streetViewControl: false
                });
                marker = new google.maps.Marker({ position: center, map: has ? map : null, draggable: true });

                function place(latLng) {
                    marker.setMap(map);
                    marker.setPosition(latLng);
                    $wire.latitude = latLng.lat().toFixed(7);
                    $wire.longitude = latLng.lng().toFixed(7);
                }

                map.addListener('click', function (e) { place(e.latLng); });
                marker.addListener('dragend', function () { place(marker.getPosition()); });

                // Saat server mengisi koordinat (tombol "Cari dari alamat"), pindahkan pin.
                $wire.$watch('latitude', function () { self.sync(); });
                $wire.$watch('longitude', function () { self.sync(); });
            },

            sync() {
                var lat = parseFloat($wire.latitude), lng = parseFloat($wire.longitude);
                if (!map || isNaN(lat) || isNaN(lng)) { return; }
                var pos = { lat: lat, lng: lng };
                marker.setMap(map);
                marker.setPosition(pos);
                map.setCenter(pos);
            }
        };
    }
</script>
