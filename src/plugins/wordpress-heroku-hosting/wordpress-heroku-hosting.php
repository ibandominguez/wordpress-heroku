<?php

/*
Plugin Name: Wordpress heroku hosting
Plugin URI: https://github.com/ibandominguez/wordpress-heroku/tree/master/plugins/wordpress-heroku-hosting/
Description: Provide web hosting within the Heroku.
Author: Ibán Dominguez Noda
Author URI: https://github.com/ibandominguez
Version: 0.1.2
*/

require_once(ABSPATH.'wp-admin/includes/plugin.php');

define('WPHH_PLUGINS', [
  'wordpress-heroku-hosting/wordpress-heroku-hosting.php' => true,
  's3-uploads/s3-uploads.php' => defined('S3_UPLOADS_BUCKET')
]);

/**
 * Setup enviroments widgets
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/init
 */
add_action('init', function() {
  require_once(__DIR__.'/includes/api/meta-filters.php');
  require_once(__DIR__.'/includes/smtp-settings.php');
  // TODO: Check current site status
});

/**
 * Setup enviroments widgets
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/wp_dashboard_setup
 */
add_action('wp_dashboard_setup', function() {
  // TODO: Custom welcome panel full width
  // remove_action('welcome_panel', 'wp_welcome_panel');
  // add_action('welcome_panel', <MY_CALLABLE>);

  wp_add_dashboard_widget('plugins', 'Plugins', function() {
    $activePlugins = get_option('active_plugins');
    $plugins = get_plugins();
    require_once(__DIR__.'/views/plugins.widget.php');
  });

  wp_add_dashboard_widget('themes', 'Themes', function() {
    $themes = get_themes();
    require_once(__DIR__.'/views/themes.widget.php');
  });
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
