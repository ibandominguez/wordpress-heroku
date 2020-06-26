<?php

global $wp_query;

$postType = !empty($wp_query->query['post_type']) ? $wp_query->query['post_type'] : null;
$isSingle = false;

switch (true) {
  case $wp_query->is_single && @$wp_query->query['post_type'] === 'session':
    require_once __DIR__.'/templates/session-single.php';
    break;

  case $wp_query->is_page:
  case $wp_query->is_single:
  case $wp_query->is_page:
  default:
    header('Content-Type: application/json');
    die(json_encode($wp_query, JSON_PRETTY_PRINT));
}
