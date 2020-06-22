<style media="screen">
.form-group .h2 { padding-left: 0 !important; }
.form-group .map { height: 500px; width: 100%; }
</style>

<div class="form-group">
  <h2 class="h2">Distancia recorrida: <b><?= get_post_meta($post->ID, 'distance_km', true); ?></b> km</h2><hr>
  <h2 class="h2">Duración sesión: <b><?= get_post_meta($post->ID, 'duration_minutes', true); ?></b> minutos</h2><hr>
  <h2 class="h2">Velocidad media: <b><?= get_post_meta($post->ID, 'average_speed_kmh', true); ?></b> km/h</h2><hr>
  <div id="map" class="map"></div>
</div>

<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= get_option('race_map_key'); ?>&callback=initMap&libraries=places,geometry"></script>
<script type="text/javascript">
function initMap() {
  var coordinates = <?= json_encode(get_post_meta($post->ID, 'coordinates', true)); ?>;
  var bounds = new google.maps.LatLngBounds();

  var map = new google.maps.Map(document.getElementById("map"), {
    center: { lat: -34.397, lng: 150.644 },
    zoom: 8
  });

  var polyline = new google.maps.Polyline({
    path: [],
    geodesic: true,
    strokeColor: '#FF0000',
    strokeOpacity: 1.0,
    strokeWeight: 2
  });

  polyline.setMap(map);

  polyline.setPath(coordinates.map(function(coordinate) {
    return { lat: parseFloat(coordinate.latitude), lng: parseFloat(coordinate.longitude) };
  }));

  coordinates.map(function(coordinate) {
    bounds.extend(new google.maps.LatLng(
      parseFloat(coordinate.latitude),
      parseFloat(coordinate.longitude)
    ));
  });

  map.fitBounds(bounds);
}
</script>
