<?php

global $wp_query;

header('Content-Type: application/json');

die(json_encode(
  $wp_query,
  JSON_PRETTY_PRINT
));
