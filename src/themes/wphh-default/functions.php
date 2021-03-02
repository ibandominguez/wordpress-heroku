<?php

require_once __DIR__.'/includes/Bootstrap_NavWalker.php';

add_action('wp_enqueue_scripts', function() {
  wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css', false, null, 'all');
  wp_enqueue_style('style', get_stylesheet_uri());
  wp_enqueue_script('popper-js', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js', ['jquery'], null, true);
  wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', ['jquery'], null, true);
});

add_action('after_setup_theme', function() {
  register_nav_menu('primary', __('Primary Menu', 'wphh-default'));
  add_theme_support('custom-logo', []);
  add_theme_support('wp-block-styles');
  add_theme_support('align-wide');
});

add_action('wp_dashboard_setup', function() {
  /* TODO: Custom welcome panel full width
  remove_action('welcome_panel', 'wp_welcome_panel');
  add_action('welcome_panel', <callable>);
  */

  /* TODO: Create theme widget
  wp_add_dashboard_widget('theme_support', 'Theme Support', function() { ?>
    <h4>Theme support</h4>
    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
  <?php });
  */
});

add_action('customize_register', function ($wp_customize) {
  $wp_customize->add_panel('wphh_theme_options', [
    'priority' => 160,
    'capability' => 'edit_theme_options',
    'theme_supports' => '', // Rarely needed
    'title' => __('Diseño general', 'wphh-default'),
    'description' => __('Configura el diseño de tu tema', 'wphh-default'),
  ]);

  $wp_customize->add_section('header', [
    'title' => __('Cabecera', 'wphh-default'),
    'description' => __('Diseña aquí tu cabecera', 'wphh-default'),
    'panel' => 'wphh_theme_options', // Not typically needed.
    'priority' => 160,
    'capability' => 'edit_theme_options',
    'theme_supports' => '', // Rarely needed.
  ]);

  $wp_customize->add_setting('header_bg_color', [
    'type' => 'theme_mod', // or 'option'
    'capability' => 'edit_theme_options',
    'theme_supports' => '', // Rarely needed.
    'default' => '#EEE', // Ej: #000000
    'transport' => 'refresh', // or postMessage
    'sanitize_callback' => 'sanitize_hex_color', // Ej: 'sanitize_hex_color'
    'sanitize_js_callback' => '', // Basically to_json.
  ]);

  $wp_customize->add_control(
    new WP_Customize_Color_Control($wp_customize, 'header_bg_color', [
      'label' => __('Color de fondo', 'wphh-default'),
      'section' => 'header' // Required, core or custom.
    ])
  );

  $wp_customize->add_setting('header_y_padding', [
    'type' => 'theme_mod', // or 'option'
    'capability' => 'edit_theme_options',
    'theme_supports' => '', // Rarely needed.
    'default' => '15', // Ej: #000000
    'transport' => 'refresh', // or postMessage
    'sanitize_callback' => '', // Ej: 'sanitize_hex_color'
    'sanitize_js_callback' => '', // Basically to_json.
  ]);

  $wp_customize->add_control('header_y_padding', [
    'type' => 'range',
    'section' => 'header',
    'label' => __('Espaciado vertical', 'wphh-default'),
    'input_attrs' => ['min' => 0, 'max' => 50, 'step' => 1]
  ]);

  $wp_customize->add_setting('header_text_color', [
    'type' => 'theme_mod', // or 'option'
    'capability' => 'edit_theme_options',
    'theme_supports' => '', // Rarely needed.
    'default' => '#222', // Ej: #000000
    'transport' => 'refresh', // or postMessage
    'sanitize_callback' => 'sanitize_hex_color', // Ej: 'sanitize_hex_color'
    'sanitize_js_callback' => '', // Basically to_json.
  ]);

  $wp_customize->add_control(
    new WP_Customize_Color_Control($wp_customize, 'header_text_color', [
      'label' => __('Color del texto', 'wphh-default'),
      'section' => 'header' // Required, core or custom.
    ])
  );

  $wp_customize->add_setting('header_text_color_hover', [
    'type' => 'theme_mod', // or 'option'
    'capability' => 'edit_theme_options',
    'theme_supports' => '', // Rarely needed.
    'default' => '#000', // Ej: #000000
    'transport' => 'refresh', // or postMessage
    'sanitize_callback' => 'sanitize_hex_color', // Ej: 'sanitize_hex_color'
    'sanitize_js_callback' => '', // Basically to_json.
  ]);

  $wp_customize->add_control(
    new WP_Customize_Color_Control($wp_customize, 'header_text_color_hover', [
      'label' => __('Color del texto (activo o hover)', 'wphh-default'),
      'section' => 'header' // Required, core or custom.
    ])
  );

  // TODO: Add more options to be configured
  // such as typography, section spacings, cdn importing, main colors, etc ...
  // Optial sidebar, widgets ...
});
