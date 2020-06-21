<?php

require_once(__DIR__.'/includes/SetUpAuthBasic.php');
require_once(__DIR__.'/includes/ModifyRestUsersRoutes.php');
require_once(__DIR__.'/includes/SetUpRankingRestRoutes.php');

include_once(ABSPATH.'wp-admin/includes/plugin.php');

SetUpAuthBasic::boot();
ModifyRestUsersRoutes::boot();
SetUpRankingRestRoutes::boot();

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
    'rewrite'            => 'races',
    'capability_type'    => 'post',
    'show_in_rest'       => true,
    'rest_base'          => 'races',
    'has_archive'        => true,
    'hierarchical'       => false,
    'menu_position'      => null,
    'menu_icon'          => 'dashicons-location',
    'supports'           => array('title', 'thumbnail')
  ));

  /**
   * Races post type
   * Registering and api activation
   */
  register_post_type('session', array(
    'label'              => 'Sesiones',
    'public'             => true,
    'publicly_queryable' => true,
    'show_in_menu'       => false,
    'query_var'          => true,
    'rewrite'            => 'sessions',
    'show_in_rest'       => true,
    'rest_base'          => 'sessions',
    'has_archive'        => false,
    'hierarchical'       => true,
    'menu_position'      => null,
    'menu_icon'          => 'dashicons-randomize',
    'supports'           => array('page-attributes'),
    'capability_type'    => array('session', 'sessions'),
    'map_meta_cap'       => true,
    'delete_with_user'   => true
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

  $role = get_role('runner');
  $role->add_cap('read_session');
  $role->add_cap('edit_session');
  $role->add_cap('edit_sessions');
  $role->add_cap('edit_published_sessions');
  $role->add_cap('publish_sessions');
  $role->add_cap('delete_published_sessions');
  $role->add_cap('delete_session');
  $role->add_cap('delete_sessions');

  /**
   * Add post thumbnail support
   */
  add_theme_support('post-thumbnails', array('race'));

  /**
   * Add meta boxes
   */
  add_action('add_meta_boxes', function($currentPostType) {
    remove_meta_box('postcustom', 'race', 'normal');

    if ($currentPostType === 'race'):
      add_meta_box('race_meta_box', 'Campos de la carrera', function($post) {
        wp_nonce_field('race_meta_box', 'race_meta_box_nonce');
        include_once(__DIR__.'/templates/race-meta.php');
      }, null, 'advanced', 'high');
    endif;
  });

  /**
   * Handle meta saving
   */
  add_action('save_post', function($postId) {
    $nonce = @$_POST['race_meta_box_nonce'];
    $fieldKeys = array('race_date', 'race_time', 'description', 'distance_km', 'duration_minutes', 'coordinates');

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
   * Register race rest metas
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

  foreach (array('description', 'race_date', 'race_time') as $key):
    register_rest_field(array('race'), $key, array(
      'schema'          => null,
      'update_callback' => function ($value, $object, $fieldName) {
        return update_post_meta($object['id'], $fieldName, $value);
      },
      'get_callback'    => function($object, $fieldName, $request) {
        return get_post_meta($object['id'], $fieldName, true);
      }
    ));
  endforeach;

  foreach (array('average_speed_kmh') as $key):
    register_rest_field(array('session'), $key, array(
      'schema'          => array('type' => 'number'),
      'update_callback' => function ($value, $object, $fieldName) {
        return update_post_meta($object['id'], $fieldName, $value);
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
        return update_post_meta($object['id'], $fieldName, $value);
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
        return update_post_meta($object['id'], $fieldName, $value);
      },
      'get_callback'    => function($object, $fieldName, $request) {
        return get_post_meta($object['id'], $fieldName, true);
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
          'distance_km' => floatval($meta['distance_km'][0], 2),
          'duration_minutes' => floatval($meta['duration_minutes'][0], 2),
          'coordinates' => unserialize($meta['coordinates'][0])
        ];
      endif;

      return 0;
    }
  ));
});
