<?php

/*
Plugin Name: Wordpress heroku hosting
Plugin URI: https://github.com/ibandominguez/wordpress-heroku/tree/master/plugins/wordpress-heroku-hosting/
Description: Set ups heroku for multisite web hosting. Add s3 uploads support
Author: Ibán Dominguez Noda
Author URI: https://github.com/ibandominguez
Version: 0.1.0
*/

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
