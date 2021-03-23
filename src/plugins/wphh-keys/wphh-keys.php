<?php

/*
Plugin Name: Multiple purporse keys
Plugin URI: https://github.com/ibandominguez/wordpress-heroku/tree/master/plugins/wphh-keys/
Description: Multiple purporse management keys.
Author: Ibán Dominguez Noda
Author URI: https://github.com/ibandominguez
Version: 0.1.0
*/

add_action('init', function () {
  /**
   * @link https://developer.wordpress.org/reference/functions/register_post_type/
   */
  register_post_type('key', [
    'label'              => 'Claves',
    'public'             => false,
    'publicly_queryable' => false,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'menu_icon'          => 'dashicons-lock',
    'capability_type'    => 'post',
    'show_in_rest'       => false,
    'has_archive'        => false,
    'supports'           => ['title'],
    'register_meta_box_cb' => function() {
      add_meta_box('expiry_meta_box', 'Fecha de expiración', function($post) { ?>
        <?php wp_nonce_field('key_meta', 'key_meta_nonce'); ?>
        <?php $expiresAt = get_post_meta($post->ID, 'expires_at', true); ?>
        <?php $key = get_post_meta($post->ID, 'key', true); ?>

        <div style="margin-bottom: 10px">
          <label for="expires_at" style="display: block">Introduce un fecha</label>
          <input id="expires_at" name="expires_at" type="date" style="width: 100%" type="text" value="<?= !empty($expiresAt) ? $expiresAt : date('Y-m-d', strtotime('+1 year')); ?>" />
        </div>
        <div>
          <label for="key" style="display: block">Clave (sólo lectura)</label>
          <input id="key" name="key" style="width: 100%" type='text' readonly value="<?= !empty($key) ? $key : md5(time()).uniqid(); ?>" />
        </div>
      <?php }, null, 'advanced', 'high');
    }
  ]);
});

/**
 * @link https://developer.wordpress.org/reference/hooks/save_post/
 */
add_action('save_post', function($postId) {
  $nonce  = @$_POST['key_meta_nonce'];
  $fields = ['key', 'expires_at'];
  $formatedExpiresAt = DateTime::createFromFormat('Y-m-d', $_POST['expires_at']);

  if (!wp_verify_nonce($nonce, 'key_meta') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)):
    return $postId;
  endif;

  if (!$formatedExpiresAt || $formatedExpiresAt->format('Y-m-d') !== $_POST['expires_at']):
    return wp_die('El formato de la fecha es inválida, debe ser Año-mes-día: 2020-01-31', 400);
  endif;

  foreach ($_POST as $key => $value):
    if (in_array($key, $fields)):
      update_post_meta($postId, $key, $value);
    endif;
  endforeach;
});

/**
 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_posts_columns
 */
add_filter('manage_key_posts_columns', function($columns) {
  $dateColumn = $columns['date'];
  unset($columns['date']);
  $columns['key'] = 'Clave';
  $columns['expires_at'] = 'Válida hasta';
  $columns['date'] = $dateColumn;
  return $columns;
});

/**
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/manage_posts_custom_column
 */
add_action('manage_key_posts_custom_column', function($column, $postId) {
  if (in_array($column, ['expires_at', 'key'])):
    $value = get_post_meta($postId, $column, true);
    print($value ? $value : 'n/a');
  endif;
}, 10, 2);

/**
 * @link https://developer.wordpress.org/reference/hooks/rest_api_init/
 */
add_action('rest_api_init', function (WP_REST_Server $wp_rest_server) {
  /**
   * @link https://developer.wordpress.org/reference/functions/register_rest_route/
   */
  register_rest_route('wphh/v1', '/keys/(?P<key>[a-zA-Z0-9-]+)', [
    'methods' => ['GET'],
    'auth_callback' => '__return_true',
    'callback' => function (WP_REST_Request $request) {
      global $wpdb;
      $params = $request->get_params();

      $postMetaKey = $wpdb->get_row($wpdb->prepare("
        select
          {$wpdb->posts}.ID as id,
          {$wpdb->posts}.post_title as title,
          key_meta.meta_value as `key`,
          date_meta.meta_value as `expires_at`,
          {$wpdb->posts}.post_date as `created_at`
        from {$wpdb->postmeta} as key_meta
        join {$wpdb->posts} on key_meta.post_id = {$wpdb->posts}.ID
        join {$wpdb->postmeta} as date_meta on key_meta.post_id = date_meta.post_id and date_meta.meta_key = 'expires_at'
        where key_meta.meta_value = '{$params['key']}'
      "));

      if (empty($postMetaKey)):
        return new WP_REST_Response(['message' => 'La clave no es válida'], 400);
      endif;

      $postMetaKey->active = date('Y-m-d') <= $postMetaKey->expires_at;
      $postMetaKey->days_remaining = round((strtotime($postMetaKey->expires_at) - strtotime(date('Y-m-d'))) / (60 * 60 * 24));

      return new WP_REST_Response($postMetaKey, 200);
    }
  ]);
});
