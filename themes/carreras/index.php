<?php

global $wp_query;
global $wpdb;

header('Content-Type: application/json');

die(json_encode([
  'DISALLOW_FILE_EDIT' => DISALLOW_FILE_EDIT,
  'global $wp_query' => $wp_query,
  'global $wpdb' => $wpdb
], JSON_PRETTY_PRINT));
