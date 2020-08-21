<?php

/*
Plugin Name: Wordpress heroku hosting
Plugin URI: https://github.com/ibandominguez/wordpress-heroku/tree/master/plugins/wordpress-heroku-hosting/
Description: Set ups heroku for multisite web hosting. Add s3 uploads support
Author: Ibán Dominguez Noda
Author URI: https://github.com/ibandominguez
Version: 0.1.1
*/

require_once(ABSPATH.'wp-admin/includes/plugin.php');

define('WPHH_PLUGINS', [
  'wordpress-heroku-hosting/wordpress-heroku-hosting.php' => true,
  's3-uploads/s3-uploads.php' => defined('S3_UPLOADS_BUCKET')
]);

add_action('init', function() {
  // TODO: Check current site status
});

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
  foreach (WPHH_PLUGINS as $plugin => $activate):
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
  foreach (WPHH_PLUGINS as $plugin => $activate):
    if ($activate):
      print("<style>[data-plugin='{$plugin}'] .deactivate { display: none; }</style>");
    endif;
  endforeach;
});
