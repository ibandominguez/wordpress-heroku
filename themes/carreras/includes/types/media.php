<?php

add_filter('pre_get_posts', function($query) {
  global $current_user;

  if (
    in_array($query->query['post_type'], ['session', 'attachment']) &&
    !current_user_can('edit_others_posts')
  ):
    $query->set('author', $current_user->ID);
  endif;

  return $query;
});
