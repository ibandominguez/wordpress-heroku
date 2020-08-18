<?php

register_rest_field('user', 'profile_image_url', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_user_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    $image = get_user_meta($object['id'], $fieldName, true);
    return $image ? $image : $object['avatar_urls']['96'];
  }
));

register_rest_field('user', 'race_payments', array(
  'schema'          => null,
  'get_callback'    => function($object, $fieldName, $request) {
    return get_user_meta($object['id'], $fieldName, false);
  }
));
