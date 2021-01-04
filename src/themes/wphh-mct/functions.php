<?php

// TODO: Add language support and translate theme

add_action('init', function () {
  require_once __DIR__.'/includes/logo.php';
  require_once __DIR__.'/includes/question.php';
  require_once __DIR__.'/includes/stripe.php';
});

add_action('admin_enqueue_scripts', function () {
  // wp_enqueue_style('tailwind.css', 'https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css');
  wp_enqueue_script('alpine.js', 'https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.0/dist/alpine.min.js');
});

add_action('rest_api_init', function () {
  // ...
});
