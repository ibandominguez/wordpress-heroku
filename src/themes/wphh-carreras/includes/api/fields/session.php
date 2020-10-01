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
    $meta = get_post_meta($data['parent']);

    if (!empty($meta)):
      return [
        'id' => $data['parent'],
        'race_date' => $meta['race_date'][0],
        'race_time' => $meta['race_time'][0],
        'description' => $meta['description'][0],
        'distance_km' => floatval($meta['distance_km'][0]),
        'duration_minutes' => floatval($meta['duration_minutes'][0]),
        'coordinates' => unserialize($meta['coordinates'][0])
      ];
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
