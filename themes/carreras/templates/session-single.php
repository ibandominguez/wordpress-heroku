<?php global $wp_query; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row">
        <div class="col col-xs-12 col-sm-4">
          <h3>Sesión</h3>
          <ul class="list-group">
            <li class="list-group-item">Distancia recorrida: <b><?= get_post_meta($wp_query->post->ID, 'distance_km', true); ?></b> km</li>
            <li class="list-group-item">Distancia recorrida: <b><?= get_post_meta($wp_query->post->ID, 'duration_minutes', true); ?></b> minutos</li>
            <li class="list-group-item">Velocidad media: <b><?= get_post_meta($wp_query->post->ID, 'average_speed_kmh', true); ?></b> km/h</li>
          </ul>
        </div>
        <div class="col col-xs-12 col-sm-8">
          <div id="map" class="map"></div>
        </div>
      </div>
    </div>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= get_option('race_map_key'); ?>&callback=initMap&libraries=places,geometry"></script>
    <script type="text/javascript">
    function initMap() {
      var coordinates = <?= json_encode(array_map(function($coordinate) {
        return [
          'lat' => floatval($coordinate['latitude']),
          'lng' => floatval($coordinate['longitude'])
        ];
      }, get_post_meta($wp_query->post->ID, 'coordinates', true)) ?? []); ?>;

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

      polyline.setPath(coordinates);

      coordinates.map(function(coordinate) {
        bounds.extend(new google.maps.LatLng(
          parseFloat(coordinate.lat),
          parseFloat(coordinate.lng)
        ));
      });

      map.fitBounds(bounds);
    }
    </script>
  </body>
</html>
