<?php

register_post_type('session', [
  'label'              => 'Sesiones',
  'public'             => true,
  'publicly_queryable' => true,
  'show_in_menu'       => true,
  'query_var'          => true,
  'rewrite'            => array('slug' => 'sessions'),
  'show_in_rest'       => true,
  'rest_base'          => 'sessions',
  'has_archive'        => false,
  'hierarchical'       => false,
  'menu_position'      => null,
  'menu_icon'          => 'dashicons-randomize',
  'supports'           => array('title', 'author'),
  'capability_type'    => array('session', 'sessions'),
  'map_meta_cap'       => true,
  'delete_with_user'   => true,
  'register_meta_box_cb' => function() {
    add_meta_box('session_meta_box', 'Campos de la session', function($post) { ?>
      <?php wp_nonce_field('session_meta_box', 'session_meta_box_nonce'); ?>
      <style media="screen">#authordiv { display: none; }</style>
      <style>.input { border: 1px solid #eee; outline: 0; display: block; width: 100%; margin: 7.5px 0; padding: 15px; height: 50px; }</style>
      <select required name="post_author_override" class="input">
        <option value="">Selecciona un corredor</option>
        <?php foreach (get_users() as $runner): ?>
          <option <?= $post->post_author == $runner->ID ? 'selected' : ''; ?> value="<?= $runner->ID; ?>"><?= $runner->display_name; ?></option>
        <?php endforeach; ?>
      </select>
      <select required name="parent_id" class="input">
        <option value="">Selecciona una carrera</option>
        <?php foreach (get_posts(['post_type' => 'race', 'post_status' => 'publish']) as $race): ?>
          <option <?= $post->post_parent == $race->ID ? 'selected' : ''; ?> value="<?= $race->ID; ?>"><?= $race->post_title; ?></option>
        <?php endforeach; ?>
      </select>
      <input required placeholder="Duración en minutos, formato numérico. Ej: 1 minuto y medio 1.5" class="input" step="0.01" name="duration_minutes" value="<?= get_post_meta($post->ID, 'duration_minutes', true); ?>">
      <input required placeholder="Distancia en kilómetros" class="input" step="0.01" name="distance_km" value="<?= get_post_meta($post->ID, 'distance_km', true); ?>">
      <input required placeholder="Velocidad media en Km/h" class="input" step="0.01" name="average_speed_kmh" value="<?= get_post_meta($post->ID, 'average_speed_kmh', true); ?>">
    <?php }, null, 'advanced', 'high');
  }
]);

/**
 * @link https://developer.wordpress.org/reference/hooks/save_post/
 */
add_action('save_post', function($postId) {
  $nonce  = @$_POST['session_meta_box_nonce'];

  if (!wp_verify_nonce($nonce, 'session_meta_box') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)):
    return $postId;
  endif;

  add_user_meta($_POST['post_author_override'], 'race_payments', $_POST['parent_id']);

  foreach ($_POST as $key => $value):
    if (in_array($key, ['distance_km', 'duration_minutes', 'average_speed_kmh'])):
      update_post_meta($postId, $key, $value);
    endif;
  endforeach;
});

add_filter('manage_session_posts_columns', function($columns) {
  $dateColumn = $columns['date'];
  unset($columns['date']);
  $columns['parent'] = 'Carrera';
  $columns['average_speed_kmh'] = 'Velocidad media (kmh)';
  $columns['distance_km'] = 'Distancia (km)';
  $columns['duration_minutes'] = 'Duración (minutos)';
  $columns['date'] = $dateColumn;
  return $columns;
});

add_action('manage_session_posts_custom_column', function($column, $postId) {
  if (in_array($column, ['average_speed_kmh', 'distance_km', 'duration_minutes'])):
    $value = get_post_meta($postId, $column, true);
    print($value ? $value : 'n/a');
  endif;

  if ($column === 'parent'):
    $parentId = get_post($postId)->post_parent;
    print($parentId ? get_the_title($parentId) : 'n/a');
  endif;
}, 10, 2);

add_filter('pre_get_posts', function($query) {
  global $current_user;

  if (@$query->query['post_type'] === 'session' && !current_user_can('edit_others_posts')):
    $query->set('author', $current_user->ID);
  endif;

  return $query;
});
