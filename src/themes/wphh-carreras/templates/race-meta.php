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
  <label class="form-label" for="price">Precio (opcional)</label>
  <input id="price" class="form-control" type="number" step="0.01" name="price" value="<?= get_post_meta($post->ID, 'price', true); ?>">
  <small>
    Este campo define si la carrera es de pago o libre.
    <br>* Si dejas este campo vacío la carrera podrá correrse libremenete.
    <br>* Si rellenas este campo la carrera podrá correrse libremente antes de la fecha de inicio y requeríra del pago tras la fecha de inicio.
    <br>* Los corredores que realizen el pago de la carrera se listarán en el ranking.
  </small>
</div><hr>

<div class="form-group">
  <div class="row">
    <div class="flex-full" style="padding-right: 10px">
      <label class="form-label" for="coupons">Cupones de descuento (opcional)</label>
      <input type="file" accept=".txt" onchange="getCoupons(this.files ? this.files[0] : null)">
      <textarea id="coupons" class="form-control" name="coupons"><?= get_post_meta($post->ID, 'coupons', true); ?></textarea>
      <small>
        Añade aquí todos los cupones.
        <br>* Los cupones deberán estar en un archivo .txt y separados por líneas.
        <br>* El wizard de importación te informará del número de códigos para que confirmes si es correcta la importación.
        <br>* También puedes añadir los códigos manualmente si lo deseas pero es importante que los códigos estén separados entre líneas, sin dejar ninguna en blanco.
        <br>* Los cupones solo se usarán si la carrera tiene precio.
      </small>
    </div>
    <div class="flex-half">
      <label class="form-label" for="coupons_discount">Descuento cupón (requerido)</label>
      <input id="coupons_discount" class="form-control" type="number" step="0.01" name="coupons_discount" value="<?= get_post_meta($post->ID, 'coupons_discount', true); ?>">
      <small>
        Este campo debería rellenarse si hay cupones
        <br>* Este es el descuento que se le restará al precio de la carrera para subscribiese.
        <br>* Si no añades este campo no tendrá sentido tener cupones.
      </small>
    </div>
  </div>
</div><hr>

<div class="form-group">
  <label class="form-label" for="description">Descripción</label>
  <textarea id="description" class="form-control" name="description"><?= get_post_meta($post->ID, 'description', true); ?></textarea>
</div><hr>

<div class="form-group">
  <label class="form-label" for="start_datetime">Fecha y hora de comienzo</label>
  <input id="start_datetime" class="form-control datetimepicker" autocomplete="off" name="start_datetime" type="text" value="<?= get_post_meta($post->ID, 'start_datetime', true); ?>">
  <small>La carrera podrá realizarse desde esta fecha.<br>* Si dejas este campo vacío la carrera podrá realizarse en cualquier momento.</small>
</div><hr>

<div class="form-group">
  <label class="form-label" for="end_datetime">Fecha y hora de finalización</label>
  <input id="end_datetime" class="form-control datetimepicker" autocomplete="off" name="end_datetime" type="text" value="<?= get_post_meta($post->ID, 'end_datetime', true); ?>">
  <small>La carrera podrá realizarse hasta esta fecha.<br>* Si dejas este campo vacío la carrera podrá realizarse hasta cualquier momento.</small>
</div><hr>

<div class="form-group">
  <label class="form-label" for="duration_minutes">Duración en minutos</label>
  <input id="duration_minutes" class="form-control" type="number" step="0.01" name="duration_minutes" value="<?= get_post_meta($post->ID, 'duration_minutes', true); ?>">
  <small>La carrera finalizará cuando se alcance dicho tiempo.<br>* Si dejas este campo vacío la carrera se podrá finalizar cuando quiera el usuario.</small>
</div><hr>

<div class="form-group">
  <label class="form-label" for="distance_km">Distancia en kilómetros</label>
  <input id="distance_km" class="form-control" type="number" step="0.01" name="distance_km" value="<?= get_post_meta($post->ID, 'distance_km', true); ?>">
  <small>
    La carrera finalizará cuando se alcance dicha distancia.<br>
    * Si dejas este campo vacío la carrera podrá alcanzar cualquier distancia.<br>
    * En el caso de existir ruta, la carrera finalizará cuando se alcance dicha distancia y se esté cerca del último punto.
  </small>
</div><hr>

<div class="form-group">
  <label class="form-label" for="organization_details">Detalles del organizador</label>
  <textarea id="organization_details" class="form-control" name="organization_details"><?= get_post_meta($post->ID, 'organization_details', true); ?></textarea>
  <small>
    Añade separados por líneas los detalles de el organizador.<br>
    * Generalmente deberías incluir al menos email, nombre y teléfono.
  </small>
</div><hr>

<div class="form-group">
  <label class="form-label" for="share_link">Enlace para compartir</label>
  <input id="share_link" type="url" class="form-control" name="share_link" value="<?= get_post_meta($post->ID, 'share_link', true); ?>">
  <small>
    Añade este enlace si quieres que se muestre el botón de compartir en la app
  </small>
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
  var coordinates = <?= json_encode(!empty($coordinates) ? $coordinates : []); ?>;
  var gpxFileInput = document.getElementById('gpx');
  var $inputs = jQuery('#inputs');
  var $distance = jQuery('#distance');
  var $distanceKmInput = jQuery('[name=distance_km]');

  // Prevent map from rendering
  if (!coordinates.length) {
    return
  }

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
    $distanceKmInput.val((totalDistanceInMeters/1000).toFixed(2));
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

function getCoupons(file) {
  if (!file || (file && file.name.indexOf('.txt') === -1)) {
    return alert('El archivo es inválido, debe ser un .txt');
  }

  var reader = new FileReader();

  reader.onload = function() {
    if (!reader.result || reader.result.length === 0) {
      return alert('El archivo está vacío');
    }
    if (confirm('Confirmación requerida\nNúmeros de cupones:' + reader.result.trim().split('\n').length)) {
      jQuery('[name=coupons]').val(reader.result.trim());
    }
  }

  reader.readAsText(file);
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= @$mapKey; ?>&callback=initMap&libraries=places,geometry"></script>
<script type="text/javascript">
jQuery(document).ready(function() {
  var $startsAt = jQuery('.datetimepicker').datetimepicker({
    lang:'es',
    format: 'Y-m-d H:i:s'
  });
});
</script>
