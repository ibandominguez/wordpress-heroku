<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
  </head>
  <body class="d-flex flex-column justify-content-center align-items-center vh-100">
    <?php if (function_exists('the_custom_logo') && has_custom_logo()): ?>
      <?php the_custom_logo(); ?>
    <?php endif; ?>

    <a class="d-block my-3" href="<?= esc_url(home_url('/')); ?>" rel="home" title="<?php bloginfo('name'); ?>">
      <?php bloginfo('name'); ?>
    </a>
    <p><?php bloginfo('description'); ?></p>
  </body>
</html>
