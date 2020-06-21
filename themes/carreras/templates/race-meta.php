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

<?php if (!empty($key)): ?>
  <div class="form-group">
    <label class="form-label" for="coordinates">Ruta</label>
    <div class="row">
      <div class="flex-full">
        <input id="search-coordinates" type="text" class="form-control" style="border-radius: 0">
        <div id="results-coordinates"></div>
        <div id="map-coordinates" class="form-control"></div>
      </div>
      <div id="points-coordinates" class="flex-full">
        <h2>Haz click en el mapa para fijar un punto</h2>
        <p>Usa el campo de búsqueda para encontrar la zona que buscas</p>
        <p>Para hacer zoom usar los signos <b>+</b> y <b>-</b></p>
        <p>Puedes arrastrar los puntos para modificar su ubicación</p>
      </div>
    </div>
  </div>
<?php endif; ?>

<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= @$key; ?>&callback=initMap&libraries=places"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/geocomplete/1.7.0/jquery.geocomplete.min.js"></script>
<script type="text/javascript">
function initMap() {
  var $points = jQuery('#points-coordinates');
  var $input = jQuery('#input-coordinates');
  var $search = jQuery('#search-coordinates').geocomplete({
    location: 'Islas canarias',
    map: '#map-coordinates',
    mapOptions: {
      mapTypeId: 'satellite'
    }
  });

  var map = $search.geocomplete('map');
  var markers = [];
  var polyline = new google.maps.Polyline({
    path: [],
    geodesic: true,
    strokeColor: '#FF0000',
    strokeOpacity: 1.0,
    strokeWeight: 2
  });

  polyline.setMap(map);

  map.addListener('click', function (event) {
    createMarker(event);
    handlePoints();
  });

  window.deleteMarker = function (index) {
    markers[index].setMap(null);
    markers.splice(index, 1);
    handlePoints();
  }

  // create marker on map
  function createMarker(data) {
    var marker = new google.maps.Marker({
      position: data.latLng,
      draggable: true,
      animation: google.maps.Animation.DROP,
      label: markers.length + 1 + '',
      icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
      map: map
    });

    marker.addListener('dragend', function (event) {
      handlePoints();
    });

    markers.push(marker);
  }

  function toRad(v) {
    return v * Math.PI / 180;
  }

  function haversine(l1, l2) {
    var R = 6371; // km
    var x1 = l2.latitude-l1.latitude;
    var dLat = toRad(x1);
    var x2 = l2.longitude-l1.longitude;
    var dLon = toRad(x2);
    var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(toRad(l1.latitude)) * Math.cos(toRad(l2.latitude)) *
    Math.sin(dLon/2) * Math.sin(dLon/2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    var d = R * c;
    return d;
  }

  // Draw markers on screen and add to hidden input
  function handlePoints () {
    $points.empty();
    var totalDistanceInKM = 0;

    markers.map(function(marker, index) {
      var lat = marker.position.lat();
      var lng = marker.position.lng();

      if (markers[index + 1]) {
        totalDistanceInKM += haversine({
          latitude: lat,
          longitude: lng
        }, {
          latitude: markers[index + 1].position.lat(),
          longitude: markers[index + 1].position.lng(),
        });
      }

      $points.append([
        '<div style="padding: 15px">',
          '<input type="hidden" name="coordinates[' + index + '][latitude]" value="' + lat + '">',
          '<input type="hidden" name="coordinates[' + index + '][longitude]" value="' + lng + '">',
          '<span style="cursor: pointer" class="dashicons dashicons-trash" onclick="deleteMarker(' + index + ')"></span>',
          '<b>Punto ' + (index + 1) + '</b><br>',
           'Lat: '+ lat + '. Lng:' + lng,
        '</div>'
      ].join(''));
    });

    $points.append('<div>Distancia total: <b>' + totalDistanceInKM.toFixed(2) + 'km<b></div>');
    $points.append('<input type="hidden" name="distance_km" value="' + totalDistanceInKM.toFixed(2) + '">');

    polyline.setPath(markers.map(function(marker) {
      return { lat: marker.position.lat(), lng: marker.position.lng() };
    }));
  }

  <?php if (!empty($coordinates)): ?>
    var oldMarkers = <?= json_encode($coordinates); ?>;

    oldMarkers.map(function (marker, index) {
      createMarker({
        latLng: new google.maps.LatLng(marker.latitude, marker.longitude)
      });
    });

    handlePoints();

    setTimeout(function() {
      map.setCenter(new google.maps.LatLng(oldMarkers[0].latitude, oldMarkers[0].longitude));
      map.setZoom(14);
    }, 1000);
  <?php endif; ?>
}
</script>
