<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <title><?php wp_title(); ?></title>
  </head>
  <body>
    <div class="container py-5">
      <?php if (have_posts()): while (have_posts()) : the_post(); ?>
        <?php if (is_page() || is_single()): ?>
          <h2><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
          <?php if (has_post_thumbnail()): the_post_thumbnail('full', ['class' => 'd-block w-100 my-3']); endif; ?>
          <div class="the-content"><?php the_content(); ?></div>
          <pre><?php get_post_meta(get_the_id()); ?></pre>
        <?php else: ?>
          <h4><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h4>
          <?php if (has_post_thumbnail()): the_post_thumbnail('full', ['class' => 'd-block w-100 my-3']); endif; ?>
          <div class="the-excerpt"><?php the_excerpt(); ?></div>
          <div class="d-flex flex-column justify-content-center align-items-center">
            <?= the_posts_pagination(); ?>
          </div>
        <?php endif; ?>
      <?php endwhile; else: ?>
        <div class="vh-100 d-flex flex-column justify-content-center align-items-center">
          <h2>404</h2>
          <p class="lead">El contenido que buscas no existe</p>
        </div>
      <?php endif; ?>
    </div>
  </body>
</html>
