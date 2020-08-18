<?php

add_action('after_setup_theme', function() {
  add_theme_support('custom-logo');
});

add_action('login_enqueue_scripts', function() {
  if (has_custom_logo()):
    $logoId = get_theme_mod('custom_logo');
    $logoData = wp_get_attachment_image_src($logoId, 'full');

    print("
      <script>
      window.onload = function() {
        document.getElementById('backtoblog').remove();
        document.querySelector('body.login div#login h1 a').href = window.location.href;
      }
      </script>
      <style>
      body.login div#login h1 a { background-image: url({$logoData[0]}); }
      </style>
    ");
  endif;
});
