<?php

require_once __DIR__.'/includes/navwalker.php';

add_action('init', function() {
  register_nav_menus([
    'menu' => __('Menu')
  ]);
});

add_action('wp_enqueue_scripts', function() {
  wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css', false, null, 'all');
  wp_enqueue_script('popper-js', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js', ['jquery'], null, true);
  wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', ['jquery'], null, true);
});

add_action('after_setup_theme', function() {
  add_theme_support('custom-logo', [
    'height'      => 100,
    'width'       => 400,
    'flex-height' => true,
    'flex-width'  => true,
    'header-text' => ['site-title', 'site-description']
  ]);
});

add_action('admin_menu', function() {
  // TODO: Add theme options
});
