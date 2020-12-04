<?php

/**
 * @link https://developer.wordpress.org/reference/functions/register_rest_route/
 * @since
 */
register_rest_route('wp/v2', '/inscriptions', [
  'methods'  => ['POST'],
  'permission_callback' => '__return_true',
  'callback' => function (WP_REST_Request $request) {
    global $wpdb;
    $body = $request->get_json_params();

    if (empty($body['email']) || empty($body['oid']) || empty($body['user_id'])):
      return new WP_REST_Response([
        'message' => 'El usuario y email son requeridos'
      ], 201);
    endif;

    $raceId = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s LIMIT 1" , 'oid'));

    if (empty($raceId)):
      return new WP_REST_Response(['message' => 'El oid es inválido'], 201);
    endif;

    $response = wp_remote_get(
      "https://srvrst28.trackingsport.com/api/inscripcion/checkemail?oid={$body['oid']}&email={$body['email']}",
      ['method' => 'GET']
    );

    $responseBody = json_decode($response['body']);

    if (empty($responseBody->inscripcion)):
      return new WP_REST_Response(
        ['message' => 'La inscripción no se encuentra en el sistema'],
        400
      );
    endif;

    add_user_meta($body['user_id'], 'race_payments', strval($raceId));

    return new WP_REST_Response(
      ['message' => 'Estás inscrito correctamante'],
      201
    );
  }
]);
