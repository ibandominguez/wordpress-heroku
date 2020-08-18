<?php

register_rest_route('wp/v2', '/rankings', [
  'methods' => ['GET'],
  'callback' => function (WP_REST_Request $request) {
    global $wpdb;

    $rankings = $wpdb->get_results("
      select
        users.display_name as name,
        truncate(avg(average_speed_kmh.meta_value), 4) as average_speed_kmh,
        truncate(sum(duration_minutes.meta_value), 2) as duration_minutes,
        truncate(sum(distance_km.meta_value), 2) as distance_km,
        count(*) as total_sessions
      from {$wpdb->posts} as sessions
      join {$wpdb->users} as users on sessions.post_author = users.ID
      join {$wpdb->postmeta} as average_speed_kmh on (average_speed_kmh.post_id = sessions.ID and average_speed_kmh.meta_key = 'average_speed_kmh')
      join {$wpdb->postmeta} as duration_minutes on (duration_minutes.post_id = sessions.ID and duration_minutes.meta_key = 'duration_minutes')
      join {$wpdb->postmeta} as distance_km on (distance_km.post_id = sessions.ID and distance_km.meta_key = 'distance_km')
      where sessions.post_type = 'session'
      and sessions.post_status = 'publish'
      group by users.ID
      order by average_speed_kmh desc
    ", ARRAY_A);

    return new WP_REST_Response($rankings, 200);
  }
]);
