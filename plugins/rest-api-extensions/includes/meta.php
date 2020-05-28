<?php

function isJson($string) {
  json_decode($string);
  return (json_last_error() == JSON_ERROR_NONE);
}

function retrievePostMeta() {
  global $wp_post_types;

  register_rest_field(array_keys($wp_post_types), 'meta', array(
    'get_callback' => function ($data) {
      $metas = array();

      foreach (get_post_meta($data['id']) as $key => $value):
        if (substr($key, 0, 1) !== '_'):
          if (is_array($value) && count($value) === 1):
            $metas[$key] = isJson($value[0]) ? json_decode($value[0]) : $value[0];
          else:
            $metas[$key] = array_map(function($item) {
              return isJson($value[0]) ? json_decode($value[0]) : $value[0];
            }, $value);
          endif;
        endif;
      endforeach;

      return $metas;
    }
  ));
}
