<?php

register_post_type('session', array(
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
    add_meta_box('session_meta_box', 'Campos de la sesión', function($post) {
      wp_nonce_field('session_meta_box', 'session_meta_box_nonce');
      include_once(get_template_directory().'/templates/session-meta.php');
    }, null, 'advanced', 'high');

    add_meta_box('session-parent', 'Carrera', function($post) {
      return wp_dropdown_pages([
        'post_type' => 'race',
        'selected' => $post->post_parent,
        'name' => 'parent_id',
        'show_option_none' => __('(no parent)'),
        'sort_column' => 'menu_order, post_title',
        'echo' => true
      ]);
    }, 'session', 'side', 'high');
  }
));

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
    $style = 'font-size: 20px; padding: 10px; background: #fff; box-shadow: #555 0 0 1px 0px;';
    echo '<div style="'.$style.'">'.get_post_meta($postId, $column, true).'</div>';
  endif;

  if ($column === 'parent'):
    $parentId = get_post($postId)->post_parent;
    echo $parentId ? get_the_title($parentId) : 'n/a';
  endif;
}, 10, 2);

add_action('save_post', function($postId) {
  $nonce = @$_POST['session_meta_box_nonce'];
  // TODO: Sanitize input data

  if (
    // TODO: Add server side validation
    !wp_verify_nonce($nonce, 'session_meta_box') || // Verify nonce
    (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) // Check for autosave
  ) {
    return $postId;
  }

  foreach ($_POST as $key => $value):
    if (in_array($key, ['distance_km', 'duration_minutes', 'coordinates', 'average_speed_kmh'])):
      update_post_meta($postId, $key, $value);
    endif;
  endforeach;
});

add_filter('pre_get_posts', function($query) {
  global $current_user;

  if (@$query->query['post_type'] === 'session' && !current_user_can('edit_others_posts')):
    $query->set('author', $current_user->ID);
  endif;

  return $query;
});
