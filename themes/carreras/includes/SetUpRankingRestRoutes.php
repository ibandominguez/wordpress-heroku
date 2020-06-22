<?php

class SetUpRankingRestRoutes
{
  static public function boot()
  {
    return new self();
  }

  public function __construct()
  {
    add_action('rest_api_init', array($this, 'registerRankingRoutes'));
  }

  public function registerRankingRoutes()
  {
    register_rest_route('wp/v2', '/rankings', array(
      array(
        'methods' => array('GET'),
        'callback' => array($this, 'callback')
      )
    ));

    register_rest_route('wp/v2', '/users/me/stadistics', array(
      array(
        'methods' => array('GET'),
        'callback' => array($this, 'stadisticsCallback'),
        'permission_callback' => array($this, 'loggedInCallback')
      )
    ));
  }

  public function callback($request)
  {
    global $wpdb;

    $rankings = $wpdb->get_results("
      select
        {$wpdb->users}.display_name as name,
        truncate(avg(average_speed_kmh.meta_value), 4) as average_speed_kmh,
        truncate(sum(duration_minutes.meta_value), 2) as duration_minutes,
        truncate(sum(distance_km.meta_value), 2) as distance_km,
        count(*) as total_sessions
      from {$wpdb->posts}
      join {$wpdb->users} on {$wpdb->posts}.post_author = {$wpdb->users}.ID
      join {$wpdb->postmeta} as average_speed_kmh on (average_speed_kmh.post_id = {$wpdb->posts}.ID and average_speed_kmh.meta_key = 'average_speed_kmh')
      join {$wpdb->postmeta} as duration_minutes on (duration_minutes.post_id = {$wpdb->posts}.ID and duration_minutes.meta_key = 'duration_minutes')
      join {$wpdb->postmeta} as distance_km on (distance_km.post_id = {$wpdb->posts}.ID and distance_km.meta_key = 'distance_km')
      where {$wpdb->posts}.post_type = 'session'
      and {$wpdb->posts}.post_status = 'publish'
      group by {$wpdb->users}.ID
      order by average_speed_kmh desc
    ", ARRAY_A);

    return new WP_REST_Response($rankings, 200);
  }

  public function stadisticsCallback()
  {
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
        from {$wpdb->posts}
        join {$wpdb->postmeta} as average_speed_kmh on (average_speed_kmh.post_id = {$wpdb->posts}.ID and average_speed_kmh.meta_key = 'average_speed_kmh')
        join {$wpdb->postmeta} as duration_minutes on (duration_minutes.post_id = {$wpdb->posts}.ID and duration_minutes.meta_key = 'duration_minutes')
        join {$wpdb->postmeta} as distance_km on (distance_km.post_id = {$wpdb->posts}.ID and distance_km.meta_key = 'distance_km')
        where {$wpdb->posts}.post_type = 'session'
        and {$wpdb->posts}.post_status = 'publish'
        and {$wpdb->posts}.post_author = %d
        group by {$wpdb->posts}.post_author
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

  public function loggedInCallback($request) {
    global $current_user;

    if (empty($current_user)):
      return new WP_Error(
        'rest_auth_required',
        __('Sorry, you must be signed in to get your stats.'),
        array('status' => rest_authorization_required_code())
      );
    endif;

    return true;
  }
}
