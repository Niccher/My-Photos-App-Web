<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: calc(100vh - 120px);
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
</style>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="h4 mb-0">Explore</h2>
        <p class="text-muted small mb-0">Discover your photos on a map</p>
    </div>
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-secondary active" id="btnMarkers">Markers</button>
        <button type="button" class="btn btn-outline-secondary" id="btnHeatmap">Heatmap</button>
    </div>
</div>

<div id="map"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const locations = <?= json_encode($locations) ?>;
        
        const map = L.map('map').setView([0, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const markerGroup = L.layerGroup().addTo(map);
        const heatPoints = [];
        let bounds = L.latLngBounds();

        locations.forEach(loc => {
            const lat = parseFloat(loc.latitude);
            const lng = parseFloat(loc.longitude);
            const point = [lat, lng];
            
            heatPoints.push([lat, lng, 0.5]); // intensity 0.5
            bounds.extend(point);

            const marker = L.marker(point);
            marker.bindPopup(`
                <div style="width: 150px">
                    <img src="<?= base_url() ?>${loc.thumbnail_path}" style="width: 100%; border-radius: 4px; margin-bottom: 8px;">
                    <strong>${loc.filename}</strong><br>
                    <small class="text-muted">${loc.taken_at}</small>
                </div>
            `);
            markerGroup.addLayer(marker);
        });

        if (locations.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }

        const heatLayer = L.heatLayer(heatPoints, {radius: 25, blur: 15});

        $('#btnMarkers').on('click', function() {
            $(this).addClass('active');
            $('#btnHeatmap').removeClass('active');
            map.removeLayer(heatLayer);
            map.addLayer(markerGroup);
        });

        $('#btnHeatmap').on('click', function() {
            $(this).addClass('active');
            $('#btnMarkers').removeClass('active');
            map.removeLayer(markerGroup);
            map.addLayer(heatLayer);
        });
    });
</script>
<?= $this->endSection() ?>
