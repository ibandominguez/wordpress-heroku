<?php

// Initial values
$date = get_post_meta($post->ID, 'race_date', true);
$time = get_post_meta($post->ID, 'race_time', true);
$description = get_post_meta($post->ID, 'description', true);
$coordinates = get_post_meta($post->ID, 'coordinates', true);
$key = get_option('race_map_key');
?>

<style media="screen">
.form-label { display: block; margin-bottom: 5px; }
.form-control { width: 100%; }
.row { display: flex; }
.flex-full { flex: 1; }
.flex-half { flex: 0.5; }
#map-coordinates { height: 500px; }
#points-coordinates { flex: 0.5; padding: 15px; background: #eee; }
.hint { display: block; padding-bottom: 5px; }
</style>

<div class="form-group">
  <label class="form-label" for="race_date">Fecha</label>
  <input id="race_date" class="form-control" type="date" name="race_date" value="<?= get_post_meta($post->ID, 'race_date', true); ?>" required>
</div><hr>

<div class="form-group">
  <label class="form-label" for="race_time">Hora</label>
  <input id="race_time" class="form-control" type="time" name="race_time" value="<?= get_post_meta($post->ID, 'race_time', true); ?>" required>
</div><hr>

<div class="form-group">
  <label class="form-label" for="duration_minutes">Duración en minutos</label>
  <input id="duration_minutes" class="form-control" type="number" step="0.01" name="duration_minutes" value="<?= get_post_meta($post->ID, 'duration_minutes', true); ?>" required>
</div><hr>

<div class="form-group">
  <label class="form-label" for="description">Descripción</label>
  <textarea id="description" class="form-control" name="description"><?= get_post_meta($post->ID, 'description', true); ?></textarea>
</div><hr>

<div class="form-group">
  <label class="form-label" for="description">Clave Google maps api</label>
  <small class="hint">Necesitas especificar una clave de google para poder usar Google maps y crear las rutas</small>
  <input id="race_map_key" type="text" class="form-control" name="race_map_key" value="<?= get_option('race_map_key'); ?>">
</div><hr>

<div class="form-group">
  <label class="form-label" for="description">Importar archivo .gpx</label>
  <small class="hint">Necesitas especificar una clave de google para poder usar Google maps y crear las rutas</small>
  <input id="gpx" type="file" accept=".gpx">
</div><hr>

<div class="form-group">
  <label id="distance"></label>
  <small class="hint">Distancia de la ruta</small>
  <div id="inputs"></div>
</div><hr>

<div class="form-group">
  <div id="map" style="height: 500px"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery-xml2json@0.0.8/src/xml2json.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/geocomplete/1.7.0/jquery.geocomplete.min.js"></script>
<script type="text/javascript">
function initMap() {
  var coordinates = <?= json_encode(get_post_meta($post->ID, 'coordinates', true) ?? []); ?>;
  var gpxFileInput = document.getElementById('gpx');
  var $inputs = jQuery('#inputs');
  var $distance = jQuery('#distance');

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
      return { latitude: parseFloat(coordinate.latitude), longitude: parseFloat(coordinate.longitude) };
    });

    polyline.setPath(coordinates.map(function(coordinate) {
      return { lat: coordinate.latitude, lng: coordinate.longitude };
    }));

    polyline.setMap(map);

    coordinates.map(function(coordinate, index) {
      $inputs.append([
        '<input type="hidden" name="coordinates[' + index + '][latitude]" value="' + coordinate.latitude + '">',
        '<input type="hidden" name="coordinates[' + index + '][longitude]" value="' + coordinate.longitude + '">'
      ]);

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

    $distance.empty().text((totalDistanceInMeters / 1000).toFixed(2) + 'km');
    $distance.append('<input type="hidden" name="distance_km" value="' + (totalDistanceInMeters/1000).toFixed(2) + '">');
    map.fitBounds(bounds);
  }

  gpxFileInput.addEventListener('change', function(event) {
    var file = event.target.files[0];
    var ext = file ? file.name.substr(file.name.length - 4) : null;
    var reader = new FileReader();

    if (ext !== '.gpx') {
      return alert('La extensión debe ser .gpx');
    }

    reader.addEventListener('load', function(event) {
      var gpxObject = jQuery.xml2json(reader.result)

      if (
        typeof gpxObject === 'object' && gpxObject.gpx &&
        gpxObject.gpx.trk && gpxObject.gpx.trk.trkseg && gpxObject.gpx.trk.trkseg.trkpt &&
        typeof gpxObject.gpx.trk.trkseg.trkpt === 'object'
      ) {
        coordinates = Object.keys(gpxObject.gpx.trk.trkseg.trkpt).map(function(item, index) {
          var coordinate = gpxObject.gpx.trk.trkseg.trkpt[item].$;
          return { latitude: parseFloat(coordinate.lat), longitude: parseFloat(coordinate.lon) }
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

  drawCoordinates();
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= @$key; ?>&callback=initMap&libraries=places,geometry"></script>
