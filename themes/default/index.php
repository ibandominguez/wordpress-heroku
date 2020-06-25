<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
  </head>
  <body>
    <header class="d-flex align-items-center">
      <?php if (function_exists('the_custom_logo') && has_custom_logo()): ?>
        <?php the_custom_logo(); ?>
      <?php else: ?>
        <h2 class="p-2">
          <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" title="<?php bloginfo('name'); ?>">
            <?php bloginfo( 'name' ); ?>
          </a>
        </h2>
      <?php endif; ?>

      <?php wp_nav_menu([
        'menu' => 'menu',
        'depth' => 2,
        'container' => 'nav',
        'menu_class' => 'nav',
        'walker' => new wp_bootstrap_navwalker()
      ]); ?>
    </header>

    <?php if (have_posts()): while (have_posts()): the_post(); ?>
      <?php the_content(); ?>
    <?php endwhile; else: ?>
      <!-- TODO: Translated 404 -->
    <?php endif; ?>

    <?php wp_footer(); ?>
  </body>
</html>
