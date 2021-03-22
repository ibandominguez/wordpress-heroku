<?php

// TODO: Add language support and translate theme

add_action('init', function () {
  require_once __DIR__.'/includes/question.php';
  require_once __DIR__.'/includes/stripe.php';
});

add_action('admin_enqueue_scripts', function () {
  // wp_enqueue_style('tailwind.css', 'https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css');
  wp_enqueue_script('alpine.js', 'https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.0/dist/alpine.min.js');
  wp_enqueue_script('alpine-ie11.js', 'https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.0/dist/alpine-ie11.min.js');
});

add_action('rest_api_init', function () {
  // ...
});

add_action('admin_menu', function() {
  !current_user_can('administrator') && remove_menu_page('upload.php');
  !current_user_can('administrator') && remove_menu_page('tools.php');
});

add_action('after_setup_theme', function() {
  add_theme_support('custom-logo');
});

add_action('wp_dashboard_setup', function() {
  global $wpdb;
  global $wp_meta_boxes;

  $wp_meta_boxes['dashboard'] = [];
  $wp_meta_boxes['side'] = [];

  remove_action('welcome_panel', 'wp_welcome_panel');

  wp_add_dashboard_widget('theme_questions', 'Gestión de preguntas', function () use ($wpdb) { ?>
    <h4>Total de preguntas publicadas: <b><?= wp_count_posts('question')->publish; ?></b></h4>
    <h4>Total grupos de venta: <b><?= count($wpdb->get_col("select distinct(meta_value) from {$wpdb->postmeta} where meta_key = 'group'")); ?></b></h4>
    <p>Todas tus preguntas se pueden agrupar en categorías y grupos. Las categorías sirven como filtros a la hora de crear tests y los grupos definen los diferentes paquetes de venta.</p>
  <?php });

  wp_add_dashboard_widget('theme_sales', 'Total ventas', function () use ($wpdb) { ?>
    <h4>En desarrollo</h4>
    <p>Actualmente en desarrollo.</p>
  <?php });
});
