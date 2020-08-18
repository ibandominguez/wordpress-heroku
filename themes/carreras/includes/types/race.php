<?php

register_post_type('race', array(
  'label'              => 'Carreras',
  'public'             => true,
  'publicly_queryable' => true,
  'show_ui'            => true,
  'show_in_menu'       => true,
  'query_var'          => true,
  'rewrite'            => array('slug' => 'races'),
  'capability_type'    => 'post',
  'show_in_rest'       => true,
  'rest_base'          => 'races',
  'has_archive'        => true,
  'hierarchical'       => true,
  'menu_position'      => null,
  'menu_icon'          => 'dashicons-location',
  'supports'           => array('title', 'thumbnail'),
  'register_meta_box_cb' => function() {
    add_meta_box('race_meta_box', 'Campos de la carrera', function($post) {
      wp_nonce_field('race_meta_box', 'race_meta_box_nonce');
      include_once(get_template_directory().'/templates/race-meta.php');
    }, null, 'advanced', 'high');

    add_meta_box('race-gkey', 'Clave de Google maps', function($post) { ?>
      <div class="form-group">
        <label class="form-label" for="description">Clave Google maps api</label>
        <small class="hint">Necesitas especificar una clave de google para poder usar Google maps y crear las rutas</small>
        <input id="race_map_key" type="text" class="form-control" name="race_map_key" value="<?= get_option('race_map_key'); ?>">
      </div>
    <?php }, 'race', 'side', 'high');
  }
));

add_theme_support('post-thumbnails', [
  'race'
]);

add_filter('manage_race_posts_columns', function($columns) {
  $dateColumn = $columns['date'];
  unset($columns['date']);
  $columns['distance_km'] = 'Distancia (km)';
  $columns['duration_minutes'] = 'Duración (minutos)';
  $columns['date'] = $dateColumn;
  return $columns;
});

add_action('manage_race_posts_custom_column', function($column, $postId) {
  if (in_array($column, ['distance_km', 'duration_minutes'])):
    $value = get_post_meta($postId, $column, true);
    $style = 'font-size: 20px; padding: 10px; background: #fff; box-shadow: #555 0 0 1px 0px;';
    echo '<div style="'.$style.'">'.($value ? $value : 'n/a').'</div>';
  endif;
}, 10, 2);

add_action('save_post', function($postId) {
  $nonce = @$_POST['race_meta_box_nonce'];
  $fieldKeys = array(
    'race_date', 'race_time', 'description', 'distance_km', 'duration_minutes', 'coordinates',
    'start_datetime', 'end_datetime', 'price', 'category', 'organization_details', 'coupons'
  );

  // TODO: Sanitize input data

  if (
    // TODO: Add server side validation
    !wp_verify_nonce($nonce, 'race_meta_box') || // Verify nonce
    (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) // Check for autosave
  ) {
    return $postId;
  }

  if (!empty($_POST['race_map_key'])):
    update_option('race_map_key', $_POST['race_map_key']);
  endif;

  foreach ($_POST as $key => $value):
    if (in_array($key, $fieldKeys)):
      update_post_meta($postId, $key, $value);
    endif;
  endforeach;
});
