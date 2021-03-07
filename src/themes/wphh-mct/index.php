<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
  </head>
  <body>
    <!-- Main -->
    <div style="font-family: helvetica, sans-serif; height: 100vh; display: flex; justify-content: center; align-items: center;">
      <div style="text-align: center">
        <?php if (function_exists('the_custom_logo') && has_custom_logo()): ?>
          <?php the_custom_logo(); ?>
        <?php else: ?>
          <h2><?php bloginfo('name'); ?></h2>
        <?php endif; ?>
        <p><?php bloginfo('description'); ?></p>
      </div>
    </div>
    <!-- /Main -->

    <?php wp_footer(); ?>
  </body>
</html>
