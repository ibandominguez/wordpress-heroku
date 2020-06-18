<?php

class UserMetasRoutes {

  static public function boot()
  {
    return new self();
  }

  public function __construct()
  {
    add_action('rest_api_init', array($this, 'registerRoutes'));
  }

  public function registerRoutes()
  {
    register_rest_route('wp/v2', '/users/me/(?P<meta_key>[\w]+)', array(
      array(
        'methods' => array('GET'),
        'callback' => array($this, 'listMetasCallback'),
        'permission_callback' => array($this, 'loggedInCallback')
      ),
      array(
        'methods' => array('POST'),
        'callback' => array($this, 'createMetasCallback'),
        'permission_callback' => array($this, 'loggedInCallback')
      )
    ));

    register_rest_route('wp/v2', '/users/me/(?P<meta_key>[\w]+)/(?P<meta_id>\d+)', array(
      array(
        'methods' => array('GET'),
        'callback' => array($this, 'showMetaCallback'),
        'permission_callback' => array($this, 'loggedInCallback')
      ),
      array(
        'methods' => array('PUT'),
        'callback' => array($this, 'updateMetaCallback'),
        'permission_callback' => array($this, 'loggedInCallback')
      ),
      array(
        'methods' => array('DELETE'),
        'callback' => array($this, 'deleteMetaCallback'),
        'permission_callback' => array($this, 'loggedInCallback')
      )
    ));
  }

  public function showMetaCallback($request)
  {
    global $current_user;
    global $wpdb;

    $meta = $wpdb->get_row($wpdb->prepare("
      select * from $wpdb->usermeta
      where user_id = {$current_user->ID} and meta_key = %s and umeta_id = %d
    ", $request['meta_key'], $request['meta_id']), ARRAY_A);

    if (empty($meta)):
      return new WP_REST_Response(array(
        'status' => 404,
        'message' => 'No results'
      ), 400);
    endif;

    $meta = array_merge($meta, unserialize($meta['meta_value']));
    $meta['umeta_id'] = intval($meta['umeta_id']);
    $meta['user_id'] = intval($meta['user_id']);
    unset($meta['meta_value']);
    unset($meta['meta_key']);

    return new WP_REST_Response($meta, 200);
  }

  public function updateMetaCallback($request)
  {
    global $current_user;
    global $wpdb;

    $value = $request->get_json_params();

    $result = $wpdb->get_row(
      $wpdb->prepare("
        select * from $wpdb->usermeta
        where umeta_id = %d and user_id = %d and meta_key = %s
      ", $request['meta_id'], $current_user->ID, $request['meta_key']
    ), ARRAY_A);

    if (empty($result)):
      return new WP_REST_Response(array(
        'status' => 404,
        'message' => 'No results'
      ), 400);
    endif;

    $wpdb->get_row(
      $wpdb->prepare("
        update $wpdb->usermeta
        set meta_value = %s
        where user_id = %d and meta_key = %s
      ", serialize($value), $current_user->ID, $request['meta_key']
    ), ARRAY_A);

    return new WP_REST_Response(array_merge(array(
      'umeta_id' => intval($request['meta_id']),
      'user_id' => $current_user->ID
    ), $value), 200);
  }

  public function deleteMetaCallback($request)
  {
    global $current_user;
    global $wpdb;

    $result = $wpdb->get_row(
      $wpdb->prepare("
        delete from $wpdb->usermeta
        where umeta_id = %d and user_id = %d and meta_key = %s
      ", $request['meta_id'], $current_user->ID, $request['meta_key']
    ), ARRAY_A);

    return new WP_REST_Response(null, 204);
  }

  public function listMetasCallback($request)
  {
    global $current_user;
    global $wpdb;

    $metas = get_user_meta($current_user->ID, $request['meta_key']);

    $metas = $wpdb->get_results($wpdb->prepare("
      select * from $wpdb->usermeta
      where user_id = {$current_user->ID} and meta_key = %s
      order by umeta_id desc
    ", $request['meta_key']), ARRAY_A);

    foreach ($metas as &$meta):
      $meta = array_merge($meta, unserialize($meta['meta_value']));
      $meta['umeta_id'] = intval($meta['umeta_id']);
      $meta['user_id'] = intval($meta['user_id']);
      unset($meta['meta_value']);
      unset($meta['meta_key']);
    endforeach;

    return new WP_REST_Response($metas, 200);
  }

  public function createMetasCallback($request)
  {
    global $current_user;

    $key = $request->get_param('meta_key');
    $value = $request->get_json_params();

    if (empty($key) || empty($value)):
      return new WP_REST_Response(array(
        'status' => 400,
        'message' => 'Meta key and meta value are required'
      ), 400);
    endif;

    $metaId = add_user_meta($current_user->ID, $key, $value);

    return new WP_REST_Response(array_merge(
      array('umeta_id' => $metaId, 'user_id' => $current_user->ID),
      $value
    ), 201);
  }

  public function loggedInCallback($request) {
    global $current_user;

    if (empty($current_user)):
      return new WP_REST_Response(array(
        'status' => 400,
        'message' => 'You must be signed in to manage your meta data'
      ), 400);
    endif;

    return true;
  }

}
