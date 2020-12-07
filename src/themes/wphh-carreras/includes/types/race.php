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
    'price', 'organization_details', 'coupons', 'coupons_discount', 'share_link', 'stripe_product', 'oid',
    'subscription_link'
  ];

  if (!wp_verify_nonce($nonce, 'race_meta_box') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)):
    return $postId;
  endif;

  // TODO: Validate all cases
  if (
    (!empty($_POST['start_datetime']) && empty($_POST['end_datetime'])) ||
    (empty($_POST['start_datetime']) && !empty($_POST['end_datetime']))
  ):
    wp_die('
      <h2>Error de validación</h2>
      <p>Si añades fehca de inicio/fin deberías añadir la otra fecha también.</p>
      <a href="#" onclick="history.back()">Volver a el formulario</a>
    ');
  elseif (!empty($_POST['stripe_product']) && (empty($_POST['start_datetime']) || empty($_POST['end_datetime']))):
    wp_die('
      <h2>Error de validación</h2>
      <p>Si añades una referencia a un producto. Deberás añadir también fechas de inicio y fin. Ya que estas servirán
      para marcar los plazos de entrenamiento gratuito.</p>
      <a href="#" onclick="history.back()">Volver a el formulario</a>
    ');
  elseif (!empty($_POST['duration_minutes']) && !empty($_POST['distance_km'])):
    wp_die('
      <h2>Error de validación</h2>
      <p>La carrera solo puede tener uno de los dos (duración o distancia).</p>
      <a href="#" onclick="history.back()">Volver a el formulario</a>
    ');
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
  'show_in_menu' => true,
  'show_admin_column' => true,
  'show_in_nav_menus' => true,
  'query_var' => true,
  'show_in_rest' => true,
  'rest_base' => 'modalities',
  'default_term' => ['name' => 'General', 'slug' => 'general']
]);

/**
 * @link https://developer.wordpress.org/reference/hooks/post_edit_category_parent_dropdown_args/
 */
add_filter('post_edit_category_parent_dropdown_args', function($args) {
  print('<style>#modality-add-toggle { display: none; }</style>');
  return $args;
});

/**
 * @link https://developer.wordpress.org/reference/hooks/taxonomy_edit_form_fields/
 */
add_action('modality_edit_form_fields', function($term) { ?>
  <?php wp_nonce_field('modality_termmeta', 'modality_termmeta_nonce'); ?>
  <table class="form-table">
    <tbody>
      <tr class="form-field term-name-wrap">
    		<th scope="row"><label for="from_age">Edad (desde)</label></th>
    		<td><input name="from_age" id="from_age" type="number" value="<?= get_term_meta($term->term_id, 'from_age', true); ?>">
    		<p class="description">La edad mínima para participar.</p></td>
    	</tr>
      <tr class="form-field term-name-wrap">
    		<th scope="row"><label for="until_age">Edad (hasta)</label></th>
    		<td><input name="until_age" id="until_age" type="number" value="<?= get_term_meta($term->term_id, 'until_age', true); ?>">
    		<p class="description">La edad máxima para participar.</p></td>
    	</tr>
      <tr class="form-field term-name-wrap">
    		<th scope="row"><label for="from_year">Año (desde)</label></th>
    		<td><input name="from_year" id="from_year" type="number" value="<?= get_term_meta($term->term_id, 'from_year', true); ?>">
    		<p class="description">El año mínimo para participar.</p></td>
    	</tr>
      <tr class="form-field term-name-wrap">
    		<th scope="row"><label for="until_year">Año (hasta)</label></th>
    		<td><input name="until_year" id="until_year" type="number" value="<?= get_term_meta($term->term_id, 'until_year', true); ?>">
    		<p class="description">El año máximo para participar.</p></td>
    	</tr>
    </tbody>
  </table>
<?php });

/**
 * @link https://developer.wordpress.org/reference/hooks/taxonomy_add_form_fields/
 */
add_action('modality_add_form_fields', function($term) { ?>
  <?php wp_nonce_field('modality_termmeta', 'modality_termmeta_nonce'); ?>
  <div class="form-field term-description-wrap">
  	<label for="from_age">Edad (desde)</label>
  	<input name="from_age" id="from_age" type="number" value="<?= get_term_meta($term->term_id, 'from_age', true); ?>">
  	<p>La edad mínima para participar.</p>
  </div>
  <div class="form-field term-description-wrap">
  	<label for="until_age">Edad (hasta)</label>
  	<input name="until_age" id="until_age" type="number" value="<?= get_term_meta($term->term_id, 'until_age', true); ?>">
  	<p>La edad máxima para participar.</p>
  </div>
  <div class="form-field term-description-wrap">
  	<label for="from_year">Año (desde)</label>
  	<input name="from_year" id="from_year" type="number" value="<?= get_term_meta($term->term_id, 'from_year', true); ?>">
  	<p>El año mínimo para participar.</p>
  </div>
  <div class="form-field term-description-wrap">
  	<label for="until_year">Año (hasta)</label>
  	<input name="until_year" id="until_year" type="number" value="<?= get_term_meta($term->term_id, 'until_year', true); ?>">
  	<p>El año máximo para participar.</p>
  </div>
<?php });

add_action('edit_modality', function($term_id) {
  if (!wp_verify_nonce(@$_POST['modality_termmeta_nonce'], 'modality_termmeta')):
    return $term_id;
  endif;

  foreach (['from_age', 'until_age', 'from_year', 'until_year'] as $meta):
    update_term_meta($term_id, $meta, sanitize_text_field($_POST[$meta]));
  endforeach;
});

add_action('create_modality', function($term_id) {
  if (!wp_verify_nonce(@$_POST['modality_termmeta_nonce'], 'modality_termmeta')):
    return $term_id;
  endif;

  foreach (['from_age', 'until_age', 'from_year', 'until_year'] as $meta):
    update_term_meta($term_id, $meta, sanitize_text_field($_POST[$meta]));
  endforeach;
});
