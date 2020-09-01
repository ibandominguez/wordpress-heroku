<?php

/**
 * @link https://developer.wordpress.org/reference/functions/register_post_type/
 */
register_post_type('race', [
  'label'              => 'Carreras',
  'public'             => true,
  'publicly_queryable' => true,
  'show_ui'            => true,
  'show_in_menu'       => true,
  'query_var'          => true,
  'rewrite'            => ['slug' => 'races'],
  'capability_type'    => 'post',
  'show_in_rest'       => true,
  'rest_base'          => 'races',
  'has_archive'        => true,
  'hierarchical'       => true,
  'menu_position'      => null,
  'menu_icon'          => 'dashicons-location',
  'supports'           => ['title', 'thumbnail'],
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
]);

/**
 * @link https://developer.wordpress.org/reference/functions/add_theme_support/
 */
add_theme_support('post-thumbnails', [
  'race'
]);

/**
 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_posts_columns
 */
add_filter('manage_race_posts_columns', function($columns) {
  $dateColumn = $columns['date'];
  unset($columns['date']);
  $columns['distance_km'] = 'Distancia (km)';
  $columns['duration_minutes'] = 'Duración (minutos)';
  $columns['date'] = $dateColumn;
  return $columns;
});

/**
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/manage_posts_custom_column
 */
add_action('manage_race_posts_custom_column', function($column, $postId) {
  if (in_array($column, ['distance_km', 'duration_minutes'])):
    $value = get_post_meta($postId, $column, true);
    print($value ? $value : 'n/a');
  endif;
}, 10, 2);

/**
 * @link https://developer.wordpress.org/reference/hooks/save_post/
 */
add_action('save_post', function($postId) {
  $nonce  = @$_POST['race_meta_box_nonce'];

  $fields = [
    'description', 'distance_km', 'duration_minutes', 'coordinates', 'start_datetime', 'end_datetime',
    'price', 'organization_details', 'coupons', 'coupons_discount', 'share_link'
  ];

  if (!wp_verify_nonce($nonce, 'race_meta_box') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)):
    return $postId;
  endif;

  // TODO: Validate all cases
  if (!empty($_POST['price']) && !is_numeric($_POST['price'])):
    return wp_die('El precio de la carrera debe ser un valor númerico');
  elseif (!empty($_POST['coupons_discount']) && !is_numeric($_POST['coupons_discount'])):
    return wp_die('El descuento de los cupones debe ser un valor númerico');
  elseif (!empty($_POST['price']) && !empty($_POST['coupons']) && empty($_POST['coupons_discount'])):
    return wp_die('Debes añadir un descuento si la carrera tiene precio y cupones');
  endif;

  if (!empty($_POST['race_map_key'])):
    update_option('race_map_key', $_POST['race_map_key']);
  endif;

  foreach ($_POST as $key => $value):
    if (in_array($key, $fields)):
      update_post_meta($postId, $key, $value);
    endif;
  endforeach;
});

/**
 * @link https://developer.wordpress.org/reference/functions/register_taxonomy/
 */
register_taxonomy('modality', ['race', 'session'], [
  'hierarchical' => !strpos($_SERVER['REQUEST_URI'], 'edit-tags.php'),
  'label' => 'Modalidades',
  'public' => true,
  'show_ui' => true,
  'show_in_menu' => strpos($_SERVER['REQUEST_URI'], 'post_type=race'),
  'show_admin_column' => true,
  'show_in_nav_menus' => true,
  'query_var' => true,
  'show_in_rest' => true,
  'rest_base' => 'modalities',
  'default_term' => ['name' => 'General', 'slug' => 'general']
]);

add_filter('post_edit_category_parent_dropdown_args', function($args) {
  print('<style>#newmodality_parent { display: none; }</style>');
  return $args;
});
