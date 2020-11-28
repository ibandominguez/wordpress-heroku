<?php

register_rest_route('wp/v2', '/users/me/stadistics', [
  'methods' => ['GET'],
  'permission_callback' => function (WP_REST_Request $request) {
    global $current_user;
    return !empty($current_user->ID);
  },
  'callback' => function (WP_REST_Request $request) {
    global $wpdb;
    global $current_user;

    $sessions = $wpdb->get_row(
      $wpdb->prepare("
        select
          count(*) as total,
          sum(if(sessions.post_parent, 0, 1)) as free,
          sum(if(sessions.post_parent, 1, 0)) as race
        from {$wpdb->posts} as sessions
        where sessions.post_type = 'session'
        and sessions.post_status = 'publish'
        and sessions.post_author = %d
      ", $current_user->ID),
      ARRAY_A
    );

    $goals = $wpdb->get_row(
      $wpdb->prepare("
        select
          sum(if(true, 1, 0)) as total_goals,
          sum(if(session_distance_km.meta_value >= race_distance_km.meta_value, 1, 0)) as completed_goals
        from {$wpdb->posts} as sessions
        join {$wpdb->posts} as races on (sessions.post_parent = races.ID and races.post_type = 'race')
        join {$wpdb->postmeta} as session_distance_km on (session_distance_km.post_id = sessions.ID and session_distance_km.meta_key = 'distance_km')
        join {$wpdb->postmeta} as race_distance_km on (race_distance_km.post_id = races.ID and race_distance_km.meta_key = 'distance_km')
        where sessions.post_type = 'session'
        and sessions.post_status = 'publish'
        and sessions.post_author = %d
        group by races.ID
      ", $current_user->ID),
      ARRAY_A
    );

    $stadistics = $wpdb->get_row(
      $wpdb->prepare("
        select
          truncate(avg(average_speed_kmh.meta_value), 4) as average_speed_kmh,
          truncate(sum(duration_minutes.meta_value), 2) as duration_minutes,
          truncate(sum(distance_km.meta_value), 2) as distance_km,
          count(*) as total_sessions
        from {$wpdb->posts} as sessions
        join {$wpdb->postmeta} as average_speed_kmh on (average_speed_kmh.post_id = sessions.ID and average_speed_kmh.meta_key = 'average_speed_kmh')
        join {$wpdb->postmeta} as duration_minutes on (duration_minutes.post_id = sessions.ID and duration_minutes.meta_key = 'duration_minutes')
        join {$wpdb->postmeta} as distance_km on (distance_km.post_id = sessions.ID and distance_km.meta_key = 'distance_km')
        where sessions.post_type = 'session'
        and sessions.post_status = 'publish'
        and sessions.post_author = %d
        group by sessions.post_author
        order by average_speed_kmh desc
      ", $current_user->ID),
      ARRAY_A
    );

    return new WP_REST_Response([
      'total_sessions' => !empty($sessions) ? $sessions['total'] : '0',
      'free_sessions' => !empty($sessions) ? $sessions['free'] : '0',
      'race_sessions' => !empty($sessions) ? $sessions['race'] : '0',
      'average_speed_kmh' => !empty($stadistics) ? $stadistics['average_speed_kmh'] : '0',
      'duration_minutes' => !empty($stadistics) ? $stadistics['duration_minutes'] : '0',
      'distance_km' => !empty($stadistics) ? $stadistics['distance_km'] : '0',
      'total_goals' => !empty($goals) ? $goals['total_goals'] : '0',
      'completed_goals' => !empty($goals) ? $goals['completed_goals'] : '0'
    ], 200);
  }
]);

register_rest_route('wp/v2', '/users', [
  'methods' => WP_REST_Server::CREATABLE,
  'args' => (new WP_REST_Users_Controller())->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
  'callback' => function ($request) {
    $response = (new WP_REST_Users_Controller())->create_item($request);

    if (!is_wp_error($response)):
      $responseData = $response->get_data();
      $responseData['basic_authorization_header'] = 'Basic ' . base64_encode("{$request['email']}:{$request['password']}");
      $response->set_data($responseData);
    endif;

    return $response;
  },
  'permission_callback' => function ($request) {
    if (!current_user_can('create_users') && ($request['roles'] && $request['roles'] !== array('runner'))):
      return new WP_Error(
        'rest_cannot_create_user',
        __('Sorry, you are only allowed to create new users with the runner role.'),
        array('status' => rest_authorization_required_code())
      );
    else:
      $request->set_param('roles', array('runner'));
    endif;

    return true;
  }
]);

register_rest_route('wp/v2', '/users/me', [
  'methods' => WP_REST_Server::DELETABLE,
  'args' => (new WP_REST_Users_Controller())->get_endpoint_args_for_item_schema(WP_REST_Server::DELETABLE),
  'permission_callback' => function (WP_REST_Request $request) {
    global $current_user;
    return !empty($current_user->ID);
  },
  'callback' => function ($request) {
    global $current_user;

    require_once(ABSPATH.'wp-admin/includes/user.php');

    $deleted = wp_delete_user($current_user->ID);

    return new WP_REST_Response(array(
      'status' => 204,
      'response' => ['success' => $deleted]
    ));
  }
]);

// TODO: To be deleted
// Just added to prevent app alert
// on previous versions
register_rest_route('wp/v2', '/rankings', [
  'methods' => ['GET'],
  'permission_callback' => '__return_true',
  'callback' => function ($request) {
    return new WP_REST_Response([], 200);
  }
]);
