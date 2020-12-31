<?php

add_role('runner', 'Runner', [
  'read' => true,
  'edit_posts' => false,
  'delete_posts' => false,
  'publish_posts' => false,
  'upload_files' => false,
  'publish_posts' => false,
  'create_posts' => false
]);

foreach (['administrator', 'editor', 'runner'] as $role):
  $role = get_role($role);

  if ($role):
    $role->add_cap('read_session');
    $role->remove_cap('edit_session');
    $role->add_cap('edit_sessions');
    $role->remove_cap('edit_published_sessions');
    $role->add_cap('publish_sessions');
    $role->remove_cap('delete_published_sessions');
    $role->add_cap('delete_session');
    $role->add_cap('delete_sessions');
    $role->add_cap('upload_files');
  endif;
endforeach;

foreach (['administrator', 'editor'] as $role):
  $role = get_role($role);

  if ($role):
    $role->add_cap('delete_others_sessions');
    $role->add_cap('edit_others_sessions');
    $role->add_cap('delete_private_sessions');
    $role->add_cap('delete_published_sessions');
    $role->add_cap('edit_published_sessions');
  endif;
endforeach;
