<?php

add_action('init', function() {
  activate_plugin('rest-api-extensions');
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
    'hierarchical'       => true,
    'menu_position'      => null,
    'menu_icon'          => 'dashicons-location',
    'supports'           => array('title', 'thumbnail', 'custom-fields')
  ));

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
    $fieldKeys = array('date', 'time', 'description', 'route');

    if (
      // TODO: Add server side validation
      !wp_verify_nonce($nonce, 'race_meta_box') || // Verify nonce
      (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) // Check for autosave
    ) {
      return $postId;
    }

    if (isset($_POST['race_map_key'])):
      update_option('race_map_key', $_POST['race_map_key']);
    endif;

    foreach ($_POST as $key => $value):
      if (in_array($key, $fieldKeys)):
        update_post_meta($postId, $key, $value);
      endif;
    endforeach;
  });

  /**
   * Register rest metas
   */
  register_meta('post', 'date', array(
    'object_subtype' => 'race',
    'type' => 'string',
    'description' => 'Fecha de la carrera',
    'single' => true,
    'show_in_rest' => true
  ));

  register_meta('post', 'time', array(
    'object_subtype' => 'race',
    'type' => 'string',
    'description' => 'Hora de la carrera',
    'single' => true,
    'show_in_rest' => true
  ));

  register_meta('post', 'description', array(
    'object_subtype' => 'race',
    'type' => 'string',
    'description' => 'Descripción de la carrera',
    'single' => true,
    'show_in_rest' => true
  ));

  register_meta('post', 'route', array(
    'object_subtype' => 'race',
    'type' => 'array',
    'description' => 'Ruta de la carrera',
    'single' => true,
    'show_in_rest' => array(
      'schema' => array(
        'items' => array(
          'type' => 'object',
          'properties' => array(
            'latitude' => array('type' => 'number'),
            'longitude' => array('type' => 'number')
          ),
        )
      )
    )
  ));
});
