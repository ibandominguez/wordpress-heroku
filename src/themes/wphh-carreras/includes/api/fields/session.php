<?php

register_rest_field('session', 'average_speed_kmh', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    return floatval(get_post_meta($object['id'], $fieldName, true));
  }
));

register_rest_field('session', 'duration_minutes', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    return floatval(get_post_meta($object['id'], $fieldName, true));
  }
));

register_rest_field('session', 'distance_km', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    return floatval(get_post_meta($object['id'], $fieldName, true));
  }
));

register_rest_field('session', 'coordinates', array(
  'schema'          => array(
    'type' => 'array',
    'items' => array(
      'latitude' => 'number',
      'longitude' => 'number',
      'speed' => 'number',
      'timestamp' => 'number'
    )
  ),
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    $coordinates = get_post_meta($object['id'], $fieldName, true);

    if (!empty($coordinates)):
      foreach ($coordinates as &$coordinate):
        foreach ($coordinate as $key => $value):
          $coordinate[$key] = floatval($value);
        endforeach;
      endforeach;
    endif;

    return $coordinates ? $coordinates : [];
  }
));

register_rest_field('session', 'parent', array(
  'schema'          => null,
  'update_callback' => function($value, $object, $fieldName) {
    if (!empty($value)):
      wp_update_post([
        'ID' => $object->ID,
        'post_parent' => $value
      ]);
    endif;
  },
  'get_callback'    => function($data) {
    if (!empty($data['parent'])):
      $response = rest_do_request(new WP_REST_Request('GET', "/wp/v2/races/{$data['parent']}"));
      return !empty($response->data) ? $response->data : 0;
    endif;

    return 0;
  }
));

register_rest_field('session', 'modality', [
  'schema'          => null,
  'get_callback'    => function($object, $fieldName, $request) {
    return wp_get_object_terms($object['id'], $fieldName)[0];
  },
  'update_callback' => function($value, $object, $fieldName) {
    return wp_set_object_terms((int) $object->ID, (int) $value, 'modality');
  }
]);
