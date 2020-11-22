<?php

register_rest_field('race', 'featured_image_url', array(
  'update_callback' => null,
  'schema'          => null,
  'get_callback'    => function($object, $fieldName, $request) {
    if ($object['featured_media']):
      $image = wp_get_attachment_image_src($object['featured_media'], 'app-thumb');
      return $image[0];
    endif;
    return false;
  }
));

register_rest_field('race', 'auth_sessions', array(
  'update_callback' => null,
  'schema'          => null,
  'get_callback'    => function($object, $fieldName, $request) {
    global $wpdb;
    global $current_user;

    if (empty($current_user->ID)):
      return [];
    endif;

    $sessions = $wpdb->get_results($wpdb->prepare("
      select
        truncate(session_average_speed_kmh.meta_value, 2) as average_speed_kmh,
        truncate(session_duration_minutes.meta_value, 2) as duration_minutes,
        truncate(session_distance_km.meta_value, 2) as distance_km
      from {$wpdb->posts} as sessions
      join {$wpdb->postmeta} as session_average_speed_kmh on (session_average_speed_kmh.post_id = sessions.ID and session_average_speed_kmh.meta_key = 'average_speed_kmh')
      join {$wpdb->postmeta} as session_duration_minutes on (session_duration_minutes.post_id = sessions.ID and session_duration_minutes.meta_key = 'duration_minutes')
      join {$wpdb->postmeta} as session_distance_km on (session_distance_km.post_id = sessions.ID and session_distance_km.meta_key = 'distance_km')
      where sessions.post_type = 'session'
      and sessions.post_status = 'publish'
      and sessions.post_parent = %d
      and sessions.post_author = %d
    ", $object['id'], $current_user->ID), ARRAY_A);

    return empty($sessions) ? [] : $sessions;
  }
));

register_rest_field('race', 'runners_count', array(
  'update_callback' => null,
  'schema'          => null,
  'get_callback'    => function($object, $fieldName, $request) {
    global $wpdb;
    $result = $wpdb->get_row($wpdb->prepare("
      select count(distinct(post_author)) as count
      from {$wpdb->posts}
      where post_type = 'session'
      and post_status = 'publish'
      and post_parent = %d
    ", $object['id']), ARRAY_A);
    return !empty($result) ? intval($result['count']) : 0;
  }
));

register_rest_field('race', 'modalities', array(
  'get_callback'    => function($object, $fieldName, $request) {
    return wp_get_object_terms($object['id'], 'modality');
  }
));

register_rest_field('race', 'rankings', array(
  'schema' => null,
  'update_callback' => null,
  'get_callback' => function($object, $fieldName, $request) {
    global $wpdb;
    $rankings = [];

    foreach ($object['modalities'] as $modality):
      // TODO: Fix filters (See sql query todos)
      // Rankings should be base upon the specific situation
      $rankings[$modality->slug] = $wpdb->get_results(
        $wpdb->prepare("
          select
            users.ID as id,
            users.display_name as name,
            truncate(session_average_speed_kmh.meta_value, 2) as average_speed_kmh,
            truncate(session_duration_minutes.meta_value, 2) as duration_minutes,
            truncate(session_distance_km.meta_value, 2) as distance_km
          from {$wpdb->posts} as sessions
          join {$wpdb->users} as users on sessions.post_author = users.ID
          join {$wpdb->posts} as race on sessions.post_parent = race.ID
          join {$wpdb->postmeta} as session_average_speed_kmh on (session_average_speed_kmh.post_id = sessions.ID and session_average_speed_kmh.meta_key = 'average_speed_kmh')
          join {$wpdb->postmeta} as session_duration_minutes on (session_duration_minutes.post_id = sessions.ID and session_duration_minutes.meta_key = 'duration_minutes')
          join {$wpdb->postmeta} as session_distance_km on (session_distance_km.post_id = sessions.ID and session_distance_km.meta_key = 'distance_km')
          join {$wpdb->postmeta} as race_distance_km on (race_distance_km.post_id = race.ID and race_distance_km.meta_key = 'distance_km')
          where sessions.post_type = 'session'
          and sessions.post_status = 'publish'
          and sessions.post_parent = %d
          # DEBUG: and session_distance_km.meta_value >= race_distance_km.meta_value
          # TODO 1: and session.date >= race.start_datetime
          # TODO 2: and UserHasPaidInscription
          # TODO 3: Filter by modality
          order by session_average_speed_kmh.meta_value desc
        ", $object['id']),
        ARRAY_A
      );
    endforeach;

    return $rankings;
  }
));

register_rest_field('race', 'price', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    $value = get_post_meta($object['id'], $fieldName, true);
    return !empty($value) ? floatval($value) : null;
  }
));

register_rest_field('race', 'description', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    $value = get_post_meta($object['id'], $fieldName, true);
    return !empty($value) ? $value : null;
  }
));

register_rest_field('race', 'share_link', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    $value = get_post_meta($object['id'], $fieldName, true);
    return !empty($value) ? $value : null;
  }
));

register_rest_field('race', 'start_datetime', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    $value = get_post_meta($object['id'], $fieldName, true);
    return !empty($value) ? $value : null;
  }
));

register_rest_field('race', 'end_datetime', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    $value = get_post_meta($object['id'], $fieldName, true);
    return !empty($value) ? $value : null;
  }
));

register_rest_field('race', 'category', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    $value = get_post_meta($object['id'], $fieldName, true);
    return !empty($value) ? $value : null;
  }
));

register_rest_field('race', 'organization_details', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    $value = get_post_meta($object['id'], $fieldName, true);
    return !empty($value) ? $value : null;
  }
));

register_rest_field('race', 'duration_minutes', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    return floatval(get_post_meta($object['id'], $fieldName, true));
  }
));

register_rest_field('race', 'distance_km', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    return floatval(get_post_meta($object['id'], $fieldName, true));
  }
));

register_rest_field('race', 'stripe_product', array(
  'schema'          => null,
  'update_callback' => function ($value, $object, $fieldName) {
    $object = (array) $object;
    return update_post_meta($object['ID'], $fieldName, $value);
  },
  'get_callback'    => function($object, $fieldName, $request) {
    $stripeProuct = get_post_meta($object['id'], $fieldName, true);
    return !empty($stripeProuct) ? $stripeProuct : null;
  }
));

register_rest_field('race', 'coordinates', array(
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

register_rest_field('race', 'prices', array(
  'schema'          => null,
  'get_callback'    => function($object, $fieldName, $request) {
    if (!empty($request['id']) && !empty($object['stripe_product'])):
      $stripeSettings = get_option('stripe_settings');

      $response = wp_remote_get("https://api.stripe.com/v1/prices?product={$object['stripe_product']}", [
        'method' => 'GET',
        'headers' => ['Authorization' => "Bearer {$stripeSettings['STRIPE_PRIVATE_KEY']}"]
      ]);

      $responseBody = json_decode($response['body']);

      return !empty($responseBody) && !empty($responseBody->data) ? $responseBody->data : [];
    endif;

    return [];
  }
));

register_rest_field('race', 'specification', array(
  'update_callback' => null,
  'schema'          => null,
  'get_callback'    => function($object, $fieldName, $request) {
    $specification = ['type' => '', 'message' => ''];

    if (empty($object['stripe_product']) && !empty($object['duration_minutes'])):
      $specification['type'] = 'offcharge-duration';
      $specification['message'] = '...';
    elseif (empty($object['stripe_product']) && !empty($object['distance_km']) && empty($object['coordinates'])):
      $specification['type'] = 'offcharge-distance';
      $specification['message'] = '...';
    elseif (empty($object['stripe_product']) && !empty($object['distance_km']) && !empty($object['coordinates'])):
      $specification['type'] = 'offcharge-route';
      $specification['message'] = '...';
    elseif (empty($object['stripe_product']) && empty($object['duration_minutes']) && empty($object['distance_km']) && empty($object['coordinates'])):
      $specification['type'] = 'offcharge-free';
      $specification['message'] = '...';
    elseif (!empty($object['stripe_product']) && !empty($object['duration_minutes'])):
      $specification['type'] = 'oncharge-duration';
      $specification['message'] = '...';
    elseif (!empty($object['stripe_product']) && !empty($object['distance_km']) && empty($object['coordinates'])):
      $specification['type'] = 'oncharge-distance';
      $specification['message'] = '...';
    elseif (!empty($object['stripe_product']) && !empty($object['distance_km']) && !empty($object['coordinates'])):
      $specification['type'] = 'oncharge-route';
      $specification['message'] = '...';
    elseif (!empty($object['stripe_product']) && empty($object['duration_minutes']) && empty($object['distance_km']) && empty($object['coordinates'])):
      $specification['type'] = 'oncharge-free';
      $specification['message'] = '...';
    endif;

    return $specification;
  }
));
