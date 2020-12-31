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
          left join {$wpdb->usermeta} as race_payment on (users.ID = race_payment.user_id and race_payment.meta_key = 'race_payments' and race_payment.meta_value = '{$object['id']}')
          join {$wpdb->posts} as race on sessions.post_parent = race.ID
          join {$wpdb->postmeta} as session_average_speed_kmh on (session_average_speed_kmh.post_id = sessions.ID and session_average_speed_kmh.meta_key = 'average_speed_kmh')
          join {$wpdb->postmeta} as session_duration_minutes on (session_duration_minutes.post_id = sessions.ID and session_duration_minutes.meta_key = 'duration_minutes')
          join {$wpdb->postmeta} as session_distance_km on (session_distance_km.post_id = sessions.ID and session_distance_km.meta_key = 'distance_km')
          join {$wpdb->postmeta} as race_distance_km on (race_distance_km.post_id = race.ID and race_distance_km.meta_key = 'distance_km')
          join {$wpdb->postmeta} as race_duration_minutes on (race_duration_minutes.post_id = race.ID and race_duration_minutes.meta_key = 'duration_minutes')
          left join {$wpdb->postmeta} as race_oid on (race_oid.post_id = race.ID and race_oid.meta_key = 'oid')
          join {$wpdb->term_relationships} as term_relationships on (sessions.ID = term_relationships.object_id)
          join {$wpdb->term_taxonomy} as term_taxonomies on (term_relationships.term_taxonomy_id = term_taxonomies.term_taxonomy_id)
          where sessions.post_type = 'session'
          and sessions.post_status = 'publish'
          and sessions.post_parent = {$object['id']}
          and term_taxonomies.term_id = {$modality->term_id}
          and (isnull(race_distance_km.meta_value) or session_distance_km.meta_value >= race_distance_km.meta_value)
          and (isnull(if(race_oid.meta_value, race_oid.meta_value, null)) or race_payment.meta_value is not null)
          and session_average_speed_kmh.meta_value > 0
          and session_duration_minutes.meta_value > 0
          and session_distance_km.meta_value > 0
          group by users.ID
          order by duration_minutes asc
        "),
        ARRAY_A
      );
    endforeach;

    return $rankings;
  }
));

register_rest_field('race', 'auth_ranking', array(
  'update_callback' => null,
  'schema'          => null,
  'get_callback'    => function($object, $fieldName, $request) {
    global $current_user;

    if (!empty($current_user->ID) && !empty($object['rankings'])):
      for ($i = 0; $i < count($object['rankings']); $i++):
        if ($object['rankings'][$i]['id'] == $current_user->ID):
          return $i + 1;
        endif;
      endfor;
    endif;

    return '- -';
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

register_rest_field('race', 'oid', array(
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

register_rest_field('race', 'duration_time', array(
  'schema'          => null,
  'get_callback'    => function($object, $fieldName, $request) {
    if (!empty($object['duration_minutes'])):
      $initialSeconds = floatval($object['duration_minutes']) * 60;
      $hours = floor($initialSeconds / 3600);
      $mins = floor($initialSeconds / 60 % 60);
      $seconds = floor($initialSeconds % 60);
      return sprintf('%02d:%02d:%02d', $hours, $mins, $seconds);
    endif;

    return '00:00:00';
  }
));

register_rest_field('race', 'specification', array(
  'update_callback' => null,
  'schema'          => null,
  'get_callback'    => function($object, $fieldName, $request) {
    $specification = ['title' => '', 'type' => '', 'message' => ''];

    if (empty($object['oid']) && !empty($object['duration_minutes'])):
      $specification['title'] = "Carrera gratuita por duración: {$object['duration_time']}";
      $specification['type'] = 'offcharge-duration';
      $specification['message'] = 'Esta carrera es gratuita podrás participar cuando quieras y puntuar en el ranking.';
      $specification['message'] .= ' Será por duración y puntuará aquel corredor/a que alcanze la mayor distancia.';
    elseif (empty($object['oid']) && !empty($object['distance_km']) && empty($object['coordinates'])):
      $specification['title'] = "Carrera gratuita por distancia: {$object['distance_km']}km";
      $specification['type'] = 'offcharge-distance';
      $specification['message'] = 'Esta carrera es gratuita podrás participar cuando quieras y puntuar en el ranking.';
      $specification['message'] .= ' Será por distancia y puntuará aquel corredor/a que alcanze la meta en la menor duración.';
    elseif (empty($object['oid']) && !empty($object['distance_km']) && !empty($object['coordinates'])):
      $specification['title'] = "Carrera gratuita por recorrido: {$object['distance_km']}km";
      $specification['type'] = 'offcharge-route';
      $specification['message'] = 'Esta carrera es gratuita podrás participar cuando quieras y puntuar en el ranking.';
      $specification['message'] .= ' Será por recorrido y puntuará aquel corredor/a que alcanze la meta (siguiendo la ruta) en la menor duración.';
    elseif (empty($object['oid']) && empty($object['duration_minutes']) && empty($object['distance_km']) && empty($object['coordinates'])):
      $specification['title'] = 'Carrera gratuita libre';
      $specification['type'] = 'offcharge-free';
      $specification['message'] = 'Esta carrera es gratuita podrás participar cuando quieras y puntuar en el ranking.';
      $specification['message'] .= ' Será libre y no puntuará en el ranking.';
    elseif (!empty($object['oid']) && !empty($object['duration_minutes'])):
      $specification['title'] = "Carrera de pago {$object['price']}€ por por duración: {$object['duration_time']}";
      $specification['type'] = 'oncharge-duration';
      $specification['message'] = 'Esta carrera es de pago. Podrás entrenar gratuitamente hasta la fecha de inicio';
      $specification['message'] .= ' Será por duración y puntuará aquel corredor/a que alcanze la mayor distancia.';
    elseif (!empty($object['oid']) && !empty($object['distance_km']) && empty($object['coordinates'])):
      $specification['title'] = "Carrera de pago {$object['price']}€ por distancia: {$object['distance_km']}km";
      $specification['type'] = 'oncharge-distance';
      $specification['message'] = 'Esta carrera es de pago. Podrás entrenar gratuitamente hasta la fecha de inicio';
      $specification['message'] .= ' Será por distancia y puntuará aquel corredor/a que alcanze la meta en la menor duración.';
    elseif (!empty($object['oid']) && !empty($object['distance_km']) && !empty($object['coordinates'])):
      $specification['title'] = "Carrera de pago {$object['price']}€ por recorrido: {$object['distance_km']}km";
      $specification['type'] = 'oncharge-route';
      $specification['message'] = 'Esta carrera es de pago. Podrás entrenar gratuitamente hasta la fecha de inicio';
      $specification['message'] .= ' Será por recorrido y puntuará aquel corredor/a que alcanze la meta (siguiendo la ruta) en la menor duración.';
    elseif (!empty($object['oid']) && empty($object['duration_minutes']) && empty($object['distance_km']) && empty($object['coordinates'])):
      $specification['title'] = 'Carrera de pago libre';
      $specification['type'] = 'oncharge-free';
      $specification['message'] .= ' Será libre y no puntuará en el ranking.';
    endif;

    return $specification;
  }
));
