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
  'delete_with_user'   => true
]);

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
