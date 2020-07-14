<?php
$mapKey = get_option('race_map_key');
$coordinates = get_post_meta($post->ID, 'coordinates', true);
?>

<style media="screen">
@import url("https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css");
.form-label { display: block; margin-bottom: 5px; }
.form-control { width: 100%; }
.row { display: flex; }
.flex-full { flex: 1; }
.flex-half { flex: 0.5; }
#map-coordinates { height: 500px; }
#points-coordinates { flex: 0.5; padding: 15px; background: #eee; }
.hint { display: block; padding-bottom: 5px; }
small { color: #aaa; }
</style>

<div class="form-group">
  <label class="form-label" for="duration_minutes">Velocidad media (kmh)</label>
  <input readonly placeholder="importa tu gpx" id="average_speed_kmh" class="form-control" type="number" step="0.01" name="average_speed_kmh" value="<?= get_post_meta($post->ID, 'average_speed_kmh', true); ?>">
</div><hr>

<div class="form-group">
  <label class="form-label" for="duration_minutes">Duración en minutos</label>
  <input readonly placeholder="importa tu gpx" id="duration_minutes" class="form-control" type="number" step="0.01" name="duration_minutes" value="<?= get_post_meta($post->ID, 'duration_minutes', true); ?>">
</div><hr>

<div class="form-group">
  <label class="form-label" for="distance_km">Distancia en kilómetros</label>
  <input readonly placeholder="importa tu gpx" id="distance_km" class="form-control" type="number" step="0.01" name="distance_km" value="<?= get_post_meta($post->ID, 'distance_km', true); ?>">
</div><hr>

<div class="form-group">
  <label class="form-label" for="description">Importar archivo .gpx</label>
  <small class="hint">Necesitas especificar una clave de google para poder usar Google maps y crear las rutas</small>
  <input id="gpx" type="file" accept=".gpx">
</div><hr>

<div class="form-group">
  <div id="map" style="height: 500px"></div>
  <div id="inputs"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery-xml2json@0.0.8/src/xml2json.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/geocomplete/1.7.0/jquery.geocomplete.min.js"></script>
<script type="text/javascript">
function initMap() {
  var coordinates = <?= json_encode(!empty($coordinates) ? $coordinates : []); ?>;
  var gpxFileInput = document.getElementById('gpx');
  var $inputs = jQuery('#inputs');
  var $distanceKmInput = jQuery('[name=distance_km]');
  var $durationMinutesInput = jQuery('[name=duration_minutes]');
  var $averageSpeedInput = jQuery('[name=average_speed_kmh]');

  var map = new google.maps.Map(document.getElementById('map'), {
    center: new google.maps.LatLng(28.3898237, -15.2242274),
    zoom: 7
  });

  var polyline = new google.maps.Polyline({
    path: [],
    geodesic: true,
    strokeColor: '#FF0000',
    strokeOpacity: 1.0,
    strokeWeight: 2
  });

  var drawCoordinates = function() {
    var bounds = new google.maps.LatLngBounds();
    var totalDistanceInMeters = 0;

    $inputs.empty();

    coordinates = coordinates.filter(function(coordinate) {
      return Boolean(coordinate.latitude && coordinate.longitude)
    }).map(function(coordinate) {
      return ['latitude', 'longitude', 'timestamp', 'altitude'].reduce(function(all, key) {
        all[key] = parseFloat(coordinate[key]) || 0.00;
        return all;
      }, {});
    });

    polyline.setPath(coordinates.map(function(coordinate) {
      return { lat: coordinate.latitude, lng: coordinate.longitude };
    }));

    polyline.setMap(map);

    coordinates.map(function(coordinate, index) {
      $inputs.append(['latitude', 'longitude', 'timestamp', 'altitude'].map(function(key) {
        return '<input type="hidden" name="coordinates[' + index + '][' + key + ']" value="' + coordinate[key] + '">'
      }).join(''));

      bounds.extend(new google.maps.LatLng(
        coordinate.latitude,
        coordinate.longitude
      ));

      if (coordinates[index + 1]) {
        totalDistanceInMeters += google.maps.geometry.spherical.computeDistanceBetween(
          new google.maps.LatLng(coordinate.latitude, coordinate.longitude),
          new google.maps.LatLng(coordinates[index+1].latitude, coordinates[index+1].longitude)
        );
      }
    });

    map.fitBounds(bounds);

    // Fill inputs
    $averageSpeedInput.val((
      (totalDistanceInMeters / 1000) / // km
      ((coordinates[coordinates.length-1].timestamp - coordinates[0].timestamp) / 1000 / 60 / 60) // hours
    ).toFixed(2));
    $distanceKmInput.val((totalDistanceInMeters/1000).toFixed(2));
    $durationMinutesInput.val(((coordinates[coordinates.length-1].timestamp - coordinates[0].timestamp) / 1000 / 60).toFixed(2));
  }

  gpxFileInput.addEventListener('change', function(event) {
    var file = event.target.files[0];
    var ext = file ? file.name.substr(file.name.length - 4) : null;
    var reader = new FileReader();

    if (ext !== '.gpx') {
      return alert('La extensión debe ser .gpx');
    }

    reader.addEventListener('load', function(event) {
      var gpxObject = jQuery.xml2json(reader.result);

      if (
        typeof gpxObject === 'object' && gpxObject.gpx &&
        gpxObject.gpx.trk && gpxObject.gpx.trk.trkseg && gpxObject.gpx.trk.trkseg.trkpt &&
        typeof gpxObject.gpx.trk.trkseg.trkpt === 'object'
      ) {
        coordinates = Object.keys(gpxObject.gpx.trk.trkseg.trkpt).map(function(item, index) {
          var point = gpxObject.gpx.trk.trkseg.trkpt[item];
          var coordinate = point.$;
          return { altitude: parseFloat(point.ele), timestamp: moment(point.time).valueOf(), latitude: parseFloat(coordinate.lat), longitude: parseFloat(coordinate.lon) }
        }).filter(function(coordinate) {
          return Boolean(
            coordinate.latitude &&
            coordinate.longitude
          )
        });

        drawCoordinates();
      }
    });

    reader.readAsText(file);
  });

  coordinates.length && drawCoordinates();
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= @$mapKey; ?>&callback=initMap&libraries=places,geometry"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>
