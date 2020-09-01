<?php

/**
 * @link https://developer.wordpress.org/reference/functions/register_rest_route/
 * @since
 */
register_rest_route('wp/v2', '/payments', [
  'methods'  => ['POST'],
  'permission_callback' => function (WP_REST_Request $request) {
    global $current_user;
    return !empty($current_user->ID);
  },
  'callback' => function (WP_REST_Request $request) {
    global $current_user;

    $body = $request->get_json_params();
    $racePrice = floatval(get_post_meta($body['race_id'], 'price', true));
    $raceCoupons = preg_split("/\r\n|\n|\r/", get_post_meta($body['race_id'], 'coupons', true));
    $raceCouponsDiscount = floatval(get_post_meta($body['race_id'], 'coupons_discount', true));
    $stripeSettings = get_option('stripe_settings');

    if (empty($current_user->ID)):
      return new WP_REST_Response(['message' => 'Necesitas iniciar sesión para hacer un pago'], 400);
    endif;

    if (empty($body['token'])):
      return new WP_REST_Response(['message' => 'Un token de pago es requerido'], 400);
    endif;

    if (empty($racePrice)):
      return new WP_REST_Response(['message' => 'La carrera es inválida'], 400);
    endif;

    if (!empty($body['coupon']) && !in_array($body['coupon'], $raceCoupons)):
      return new WP_REST_Response(['message' => 'Cupón inválido'], 400);
    endif;

    if (in_array($body['race_id'], get_user_meta($current_user->ID, 'race_payments'))):
      return new WP_REST_Response(['message' => 'Ya has comprado esta subscripción'], 400);
    endif;

    if (empty($stripeSettings['STRIPE_PRIVATE_KEY'])):
      return new WP_REST_Response(['message' => 'El sistema de pagos no ha sido configurado'], 400);
    endif;

    $response = wp_remote_post('https://api.stripe.com/v1/charges', [
      'method' => 'POST',
      'headers' => ['Authorization' => "Bearer {$stripeSettings['STRIPE_PRIVATE_KEY']}"],
      'body'   => [
        'amount'   => intval((!empty($body['coupon']) ? $racePrice - $raceCouponsDiscount : $racePrice) * 100),
        'currency' => 'eur',
        'source'   => $body['token'],
        'description' => "Subscripción de {$current_user->data->display_name} a la carrera #{$body['race_id']}",
        'receipt_email' => $current_user->data->user_email
      ]
    ]);

    if (is_wp_error($response)):
      return new WP_REST_Response([
        'message' => $response->get_error_message(),
      ], 400);
    endif;

    if ($response['response']['code'] == 200):
      add_user_meta($current_user->ID, 'race_payments', $body['race_id']);
    endif;

    return new WP_REST_Response(
      json_decode($response['body']),
      $response['response']['code']
    );
  }
]);
