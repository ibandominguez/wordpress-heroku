<?php

require_once(__DIR__.'/includes/SetUpAuthBasic.php');
require_once(__DIR__.'/includes/ModifyRestUsersRoutes.php');
require_once(__DIR__.'/includes/SetUpRankingRestRoutes.php');

include_once(ABSPATH.'wp-admin/includes/plugin.php');

SetUpAuthBasic::boot();
ModifyRestUsersRoutes::boot();
SetUpRankingRestRoutes::boot();

/**
 * On init
 */

add_action('init', function() {
  activate_plugin('wp-rest-filter');

  /**
   * Races post type
   * Registering and api activation
   */
  register_post_type('race', array(
    'labels' => array(
      'name'                  => 'Carreras',
      'singular_name'         => 'Carrera',
      'menu_name'             => 'Carreras',
      'name_admin_bar'        => 'Carrera',
      'add_new'               => 'Nueva carrera',
      'add_new_item'          => 'Nueva carrera',
      'new_item'              => 'Nueva carrera',
      'edit_item'             => 'Editar carrera',
      'view_item'             => 'Ver carrera',
      'all_items'             => 'Todas las carreras',
      'search_items'          => 'Buscar carreras',
      'parent_item_colon'     => 'Tipos parentales',
      'not_found'             => 'No encontrada',
      'not_found_in_trash'    => 'No encontrada en la papelera',
      'featured_image'        => 'Imagen destacada de la carrera',
      'set_featured_image'    => 'Elegir imagen destacada',
      'remove_featured_image' => 'Quitar imagen destacada',
      'use_featured_image'    => 'Usar imagen destacada',
      'archives'              => 'Archivo de carreras',
      'insert_into_item'      => 'Insertar en carrera',
      'uploaded_to_this_item' => 'Subir a carrera',
      'filter_items_list'     => 'Filtrar carreras',
      'items_list_navigation' => 'Navegación de carreras',
      'items_list'            => 'Listado de carreras',
    ),
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
        include_once(__DIR__.'/templates/race-meta.php');
      }, null, 'advanced', 'high');
    }
  ));

  add_theme_support('post-thumbnails', array('race'));

  /**
   * Races post type
   * Registering and api activation
   */
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
    'supports'           => array('title'),
    'capability_type'    => array('session', 'sessions'),
    'map_meta_cap'       => true,
    'delete_with_user'   => true,
    'register_meta_box_cb' => function() {
      add_meta_box('session_meta_box', 'Campos de la sesión', function($post) {
        wp_nonce_field('session_meta_box', 'session_meta_box_nonce');
        include_once(__DIR__.'/templates/session-meta.php');
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

  add_role('runner', 'Runner', array(
    'read' => true,
    'edit_posts' => false,
    'delete_posts' => false,
    'publish_posts' => false,
    'upload_files' => false,
    'publish_posts' => false,
    'create_posts' => false
  ));

  foreach (array('administrator', 'editor', 'runner') as $role):
    $role = get_role($role);
    $role->add_cap('read_session');
    $role->add_cap('edit_session');
    $role->add_cap('edit_sessions');
    $role->add_cap('edit_published_sessions');
    $role->add_cap('publish_sessions');
    $role->add_cap('delete_published_sessions');
    $role->add_cap('delete_session');
    $role->add_cap('delete_sessions');
  endforeach;

  foreach (array('administrator', 'editor') as $role):
    $role = get_role($role);
    $role->add_cap('delete_others_sessions');
    $role->add_cap('edit_others_sessions');
    $role->add_cap('delete_private_sessions');
  endforeach;

  /**
   * Handle meta saving
   */
  add_action('save_post', function($postId) {
    $nonce = @$_POST['race_meta_box_nonce'];
    $fieldKeys = array('race_date', 'race_time', 'description', 'distance_km', 'duration_minutes', 'coordinates');

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

  /**
   * Get only user sessions
   */
  add_filter('pre_get_posts', function($query) {
    global $current_user;

    if (
      @$query->query['post_type'] === 'session' &&
      !current_user_can('edit_others_posts')
    ):
      $query->set('author', $current_user->ID);
    endif;

    return $query;
  });
});

/**
 * On rest api
 */

add_action('rest_api_init', function() {
  /**
   * Rest api fields
   */
  register_rest_field('race', 'featured_image_url', array(
    'update_callback' => null,
    'schema'          => null,
    'get_callback'    => function($object, $field_name, $request) {
      if ($object['featured_media']):
        $image = wp_get_attachment_image_src($object['featured_media'], 'app-thumb');
        return $image[0];
      endif;
      return false;
    }
  ));

  register_rest_field('race', 'auth_sessions', array(
    'update_callback' => null,
    'schema'          => null,
    'get_callback'    => function($object, $field_name, $request) {
      global $wpdb;
      global $current_user;

      if (empty($current_user->ID)):
        return [];
      endif;

      $sessions = $wpdb->get_results($wpdb->prepare("
        select
          truncate(session_average_speed_kmh.meta_value, 2) as average_speed_kmh,
          truncate(session_duration_minutes.meta_value, 2) as duration_minutes,
          truncate(session_distance_km.meta_value, 2) as distance_km
        from {$wpdb->posts} as sessions
        join {$wpdb->postmeta} as session_average_speed_kmh on (session_average_speed_kmh.post_id = sessions.ID and session_average_speed_kmh.meta_key = 'average_speed_kmh')
        join {$wpdb->postmeta} as session_duration_minutes on (session_duration_minutes.post_id = sessions.ID and session_duration_minutes.meta_key = 'duration_minutes')
        join {$wpdb->postmeta} as session_distance_km on (session_distance_km.post_id = sessions.ID and session_distance_km.meta_key = 'distance_km')
        where sessions.post_type = 'session'
        and sessions.post_status = 'publish'
        and sessions.post_parent = %d
        and sessions.post_author = %d
      ", $object['id'], $current_user->ID), ARRAY_A);

      return empty($sessions) ? [] : $sessions;
    }
  ));

  register_rest_field('race', 'runners_count', array(
    'update_callback' => null,
    'schema'          => null,
    'get_callback'    => function($object, $field_name, $request) {
      global $wpdb;
      $result = $wpdb->get_row($wpdb->prepare("
        select count(distinct(post_author)) as count
        from {$wpdb->posts}
        where post_type = 'session'
        and post_parent = %d
      ", $object['id']), ARRAY_A);
      return !empty($result) ? intval($result['count']) : 0;
    }
  ));

  register_rest_field('race', 'rankings', array(
    'schema' => null,
    'update_callback' => null,
    'get_callback' => function($object, $field_name, $request) {
      global $wpdb;
      return $wpdb->get_results(
        $wpdb->prepare("
          select
            users.ID as id,
            users.display_name as name,
            truncate(session_average_speed_kmh.meta_value, 2) as average_speed_kmh,
            truncate(session_duration_minutes.meta_value, 2) as duration_minutes,
            truncate(session_distance_km.meta_value, 2) as distance_km
          from {$wpdb->posts} as sessions
          join {$wpdb->users} as users on sessions.post_author = users.ID
          join {$wpdb->posts} as race on (sessions.post_parent = race.ID and race.ID = %d)
          join {$wpdb->postmeta} as session_average_speed_kmh on (session_average_speed_kmh.post_id = sessions.ID and session_average_speed_kmh.meta_key = 'average_speed_kmh')
          join {$wpdb->postmeta} as session_duration_minutes on (session_duration_minutes.post_id = sessions.ID and session_duration_minutes.meta_key = 'duration_minutes')
          join {$wpdb->postmeta} as session_distance_km on (session_distance_km.post_id = sessions.ID and session_distance_km.meta_key = 'distance_km')
          join {$wpdb->postmeta} as race_distance_km on (race_distance_km.post_id = race.ID and race_distance_km.meta_key = 'distance_km')
          where sessions.post_type = 'session'
          and sessions.post_status = 'publish'
          # and session_distance_km.meta_value >= race_distance_km.meta_value
          order by session_average_speed_kmh.meta_value desc
        ", $object['id']),
        ARRAY_A
      );
    }
  ));

  foreach (array('description', 'race_date', 'race_time') as $key):
    register_rest_field(array('race'), $key, array(
      'schema'          => null,
      'update_callback' => function ($value, $object, $fieldName) {
        $object = (array) $object;
        return update_post_meta($object['ID'], $fieldName, $value);
      },
      'get_callback'    => function($object, $fieldName, $request) {
        return get_post_meta($object['id'], $fieldName, true);
      }
    ));
  endforeach;

  foreach (array('average_speed_kmh') as $key):
    register_rest_field(array('session'), $key, array(
      'schema'          => null,
      'update_callback' => function ($value, $object, $fieldName) {
        $object = (array) $object;
        return update_post_meta($object['ID'], $fieldName, $value);
      },
      'get_callback'    => function($object, $fieldName, $request) {
        return floatval(get_post_meta($object['id'], $fieldName, true));
      }
    ));
  endforeach;

  foreach (array('duration_minutes', 'distance_km') as $key):
    register_rest_field(array('race', 'session'), $key, array(
      'schema'          => null,
      'update_callback' => function ($value, $object, $fieldName) {
        $object = (array) $object;
        return update_post_meta($object['ID'], $fieldName, $value);
      },
      'get_callback'    => function($object, $fieldName, $request) {
        return floatval(get_post_meta($object['id'], $fieldName, true));
      }
    ));
  endforeach;

  foreach (array('coordinates') as $key):
    register_rest_field(array('race', 'session'), $key, array(
      'schema'          => array(
        'type' => 'array',
        'items' => array(
          'latitude' => 'number',
          'longitude' => 'number',
          'speed' => 'number',
          'timestamp' => 'number'
        )
      ),
      'update_callback' => function ($value, $object, $fieldName) {
        $object = (array) $object;
        return update_post_meta($object['ID'], $fieldName, $value);
      },
      'get_callback'    => function($object, $fieldName, $request) {
        $coordinates = get_post_meta($object['id'], $fieldName, true);

        if (!empty($coordinates)):
          foreach ($coordinates as &$coordinate):
            foreach ($coordinate as $key => $value):
              $coordinate[$key] = floatval($value);
            endforeach;
          endforeach;
        endif;

        return $coordinates ? $coordinates : [];
      }
    ));
  endforeach;

  register_rest_field('session', 'parent', array(
    'update_callback' => null,
    'schema'          => null,
    'get_callback'    => function($data) {
      $meta = get_post_meta($data['parent']);

      if (!empty($meta)):
        return [
          'id' => $data['parent'],
          'race_date' => $meta['race_date'][0],
          'race_time' => $meta['race_time'][0],
          'description' => $meta['description'][0],
          'distance_km' => floatval($meta['distance_km'][0]),
          'duration_minutes' => floatval($meta['duration_minutes'][0]),
          'coordinates' => unserialize($meta['coordinates'][0])
        ];
      endif;

      return 0;
    }
  ));
});
