<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <?php if (function_exists('the_custom_logo') && has_custom_logo()): ?>
        <?php the_custom_logo(); ?>
      <?php else: ?>
        <h2 class="p-2">
          <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" title="<?php bloginfo('name'); ?>">
            <?php bloginfo( 'name' ); ?>
          </a>
        </h2>
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
    			'menu_class'     => 'nav navbar-nav ml-auto',
    			'walker'         => new Bootstrap_NavWalker(),
    			'fallback_cb'    => 'Bootstrap_NavWalker::fallback',
  		  ]); ?>
      </div>
    </nav>

    <div class="container-fluid py-3">
      <?php /* Post listing */ ?>
      <?php if (have_posts()): while (have_posts()): the_post(); ?>
        <h2><a href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
        <small><?php the_time('F jS, Y'); ?> by <?php the_author_posts_link(); ?></small>
        <?php if (is_singular()): ?>
          <div class="content"><?php the_content(); ?></div>
        <?php else: ?>
          <div class="excerpt"><?php the_excerpt(); ?></div>
          <hr>
        <?php endif; ?>
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

    <?php wp_footer(); ?>
  </body>
</html>
