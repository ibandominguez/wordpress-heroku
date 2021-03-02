<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
  </head>
  <body>
    <!-- Header -->
    <header class="navbar navbar-expand-lg navbar-light bg-light py-4">
      <nav class="container">
        <?php if (function_exists('the_custom_logo') && has_custom_logo()): ?>
          <?php the_custom_logo(); ?>
        <?php else: ?>
          <a href="<?= esc_url(home_url('/')); ?>" rel="home" title="<?php bloginfo('name'); ?>" class="navbar-brand">
            <?php bloginfo('name'); ?>
          </a>
        <?php endif; ?>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#wphh-default-navbar" aria-controls="wphh-default-navbar" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div id="wphh-default-navbar" class="collapse navbar-collapse">
          <?php wp_nav_menu([
      			'theme_location' => 'primary',
      			'menu_id'        => 'primary-menu',
      			'container'      => false,
      			'depth'          => 2,
      			'menu_class'     => 'navbar-nav ml-auto',
      			'walker'         => new Bootstrap_NavWalker(),
      			'fallback_cb'    => 'Bootstrap_NavWalker::fallback',
    		  ]); ?>
        </div>
      </nav>
    </header>
    <!-- /Header -->

    <!-- Content -->
    <div class="container">
      <?php /* Post listing */ ?>
      <?php if (have_posts()): while (have_posts()): the_post(); ?>
        <div class="entry-content">
          <?php if (is_singular()): ?>
            <?php the_content(); ?>
          <?php else: ?>
            <h2><a href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
            <small><?php the_time('F jS, Y'); ?> by <?php the_author_posts_link(); ?></small>
            <div class="entry-excerpt"><?php the_excerpt(); ?></div>
            <hr>
          <?php endif; ?>
        </div>
      <?php endwhile; else: ?>
        <h4><?= __('Not found', 'wphh-default'); ?></h4>
        <p><?= __('Content not found', 'wphh-default'); ?></p>
      <?php endif; ?>

      <?php /* Pagination links */ ?>
      <?php if (!is_singular()): ?>
        <div class="text-center py-3">
          <?= paginate_links(); ?>
        </div>
      <?php endif; ?>
    </div>
    <!-- /Content -->

    <?php wp_footer(); ?>
  </body>
</html>
