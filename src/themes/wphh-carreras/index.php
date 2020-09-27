<?php

global $wp_query;

switch (true) {
  case $wp_query->is_single && @$wp_query->query['post_type'] === 'session':
    require_once __DIR__.'/templates/session-single.php';
    break;

  case $wp_query->is_single && @$wp_query->query['post_type'] === 'race':
    require_once __DIR__.'/templates/race-single.php';
    break;

  case $wp_query->is_page:
  case $wp_query->is_single:
  case $wp_query->is_page:
  case $wp_query->is_404:
  
  default:
    require_once __DIR__.'/templates/default.php';
}
