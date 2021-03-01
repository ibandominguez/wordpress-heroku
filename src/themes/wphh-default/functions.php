<?php

require_once __DIR__.'/includes/Bootstrap_NavWalker.php';

add_action('init', function () {
  add_theme_support('wp-block-styles');
  add_theme_support('align-wide');
});

add_action('wp_enqueue_scripts', function() {
  wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css', false, null, 'all');
  wp_enqueue_style('style', get_stylesheet_uri());
  wp_enqueue_script('popper-js', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js', ['jquery'], null, true);
  wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', ['jquery'], null, true);
});

add_action('after_setup_theme', function() {
  register_nav_menu('primary', __('Primary Menu', 'wphh-default'));
  add_theme_support('custom-logo', ['height' => 75, 'width' => 75]);
});

add_action('admin_menu', function() {
  // TODO: Add theme options
});

add_action('wp_dashboard_setup', function() {
  // TODO: Custom welcome panel full width
  // remove_action('welcome_panel', 'wp_welcome_panel');
  // add_action('welcome_panel', <callable>);

  wp_add_dashboard_widget('theme_support', 'Theme Support', function() { ?>
    <h4>Theme support</h4>
    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
  <?php });
});
