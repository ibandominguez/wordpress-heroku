<?php

/*
Plugin Name: Wordpress heroku hosting
Plugin URI: https://github.com/ibandominguez/wordpress-heroku/tree/master/plugins/wphh-core/
Description: Provide web hosting within the Heroku.
Author: Ibán Dominguez Noda
Author URI: https://github.com/ibandominguez
Version: 0.1.2
*/

require_once(ABSPATH.'wp-admin/includes/plugin.php');

define('WPHH_PLUGINS', [
  'wphh-core/wphh-core.php' => true,
  's3-uploads/s3-uploads.php' => defined('S3_UPLOADS_BUCKET')
]);

/**
 * Setup enviroments widgets
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/init
 */
add_action('init', function() {
  require_once(__DIR__.'/includes/api/meta-filters.php');
  require_once(__DIR__.'/includes/settings/smtp.php');
  require_once(__DIR__.'/includes/authbasic.php');
  require_once(__DIR__.'/includes/logo.php');
  // TODO: Check current site status
});

/**
 * Setup enviroments widgets
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/rest_init
 */
add_action('rest_api_init', function() {
  register_rest_route('wphh/v1', '/mails', [
    'methods' => ['POST'],
    'callback' => function (WP_REST_Request $request) {
      $smptSettings = get_option('smtp_settings');
      $params = $request->get_params();
      $attachments = [];

      if (empty($smptSettings['From'])):
        return new WP_REST_Response(['message' => 'Debe configurarse el mail para activar esta funcionalidad'], 400);
      endif;

      if (empty($params['name']) || empty($params['email']) || empty($params['subject']) || empty($params['message'])):
        return new WP_REST_Response(['message' => 'Los campos nombre, email, motivo y mensaje son requeridos'], 400);
      endif;

      foreach ($request->get_file_params() as $attachment):
        move_uploaded_file($attachment['tmp_name'], $attachment['tmp_name'].$attachment['name']);
        $attachments[] = $attachment['tmp_name'].$attachment['name'];
      endforeach;

      $mail = wp_mail(
        $smptSettings['From'], // to
        $params['subject'], // subject
        $params['message'], // body
        [
          "From: {$smptSettings['FromName']} <{$smptSettings['From']}>",
          "Reply-To: {$params['name']} <{$params['email']}>",
          'Content-Type: text/html; charset=UTF-8'
        ], // headers
        $attachments // attachments
      );

      return new WP_REST_Response(['sent' => $mail], $mail ? 201 : 400);
    }
  ]);
});

/**
 * Allow to create custom theme based on the host
 * @link https://developer.wordpress.org/reference/hooks/wp_prepare_themes_for_js/
 */
add_filter('wp_prepare_themes_for_js', function($themes) {
  foreach ($themes as $key => $value):
    if (
      strpos($key, 'wphh-') !== 0 &&
      strpos($key, $_SERVER['HTTP_HOST']) !== 0 &&
      strpos($key, '-') !== false
    ):
      unset($themes[$key]);
    endif;
  endforeach;
  return $themes;
});

/**
 * Setup enviroments widgets
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/wp_dashboard_setup
 */
add_action('wp_dashboard_setup', function() {
  /* TODO: Custom welcome panel full width
  remove_action('welcome_panel', 'wp_welcome_panel');
  add_action('welcome_panel', function() {
   require_once(__DIR__.'/views/welcome-panel.php');
  });
  */
});

/**
 * Make sure the required plugins are active
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/plugins_loaded
 */
add_action('plugins_loaded', function() {
  $plugins = defined('THEME_PLUGINS') ? array_merge(WPHH_PLUGINS, THEME_PLUGINS) : WPHH_PLUGINS;
  foreach ($plugins as $plugin => $activate):
    if ($activate):
      activate_plugin($plugin);
    endif;
  endforeach;
});

/**
 * Remove unseful deactive link, since plugins will be activated back
 * as soon as the page loads
 * @link https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
 */
add_action('admin_enqueue_scripts', function() {
  $plugins = defined('THEME_PLUGINS') ? array_merge(WPHH_PLUGINS, THEME_PLUGINS) : WPHH_PLUGINS;
  foreach ($plugins as $plugin => $activate):
    if ($activate):
      print("<style>[data-plugin='{$plugin}'] .deactivate { display: none; }</style>");
    endif;
  endforeach;
});

/**
 * @link https://developer.wordpress.org/reference/hooks/shutdown/
 */
add_action('shutdown', function() {
  global $wpdb;

  // $wpdb->queries
  // Stores all the queries perform on the request
});
