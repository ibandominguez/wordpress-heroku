<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
  </head>
  <body>
    <div style="font-family: helvetica, sans-serif; height: 100vh; display: flex; justify-content: center; align-items: center;">
      <div style="text-align: center">
        <?php if (function_exists('the_custom_logo') && has_custom_logo()): ?>
          <?php the_custom_logo(); ?>
        <?php endif; ?>
        <h2><?php bloginfo('name'); ?></h2>
        <p><?php bloginfo('description'); ?></p>
      </div>
    </div>
  </body>
</html>
