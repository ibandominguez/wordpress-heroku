<?php

/**
 * @link https://developer.wordpress.org/reference/functions/register_rest_route/
 * @since
 */
register_rest_route('wp/v2', '/payment_intents', [
  'methods'  => ['POST'],
  'permission_callback' => function (WP_REST_Request $request) {
    return true;
  },
  'callback' => function (WP_REST_Request $request) {
    $body = $request->get_json_params();
    $racePrice = floatval(get_post_meta($body['race_id'], 'price', true));
    $raceCoupons = preg_split("/\r\n|\n|\r/", get_post_meta($body['race_id'], 'coupons', true));
    $raceCouponsDiscount = floatval(get_post_meta($body['race_id'], 'coupons_discount', true));
    $stripeSettings = get_option('stripe_settings');
    $current_user = get_user_by('ID', @$body['user_id']);

    if (empty($body['user_id']) || empty($current_user)):
      return new WP_REST_Response(['message' => 'Necesitas iniciar sesión para hacer un pago'], 400);
    endif;

    if (empty($racePrice)):
      return new WP_REST_Response(['message' => 'La carrera es inválida'], 400);
    endif;

    if (!empty($body['coupon']) && !in_array($body['coupon'], $raceCoupons)):
      return new WP_REST_Response(['message' => 'Cupón inválido'], 400);
    endif;

    if (in_array($body['race_id'], get_user_meta($body['user_id'], 'race_payments'))):
      return new WP_REST_Response(['message' => 'Ya has comprado esta subscripción'], 400);
    endif;

    if (empty($stripeSettings['STRIPE_PRIVATE_KEY'])):
      return new WP_REST_Response(['message' => 'El sistema de pagos no ha sido configurado'], 400);
    endif;

    $response = wp_remote_post('https://api.stripe.com/v1/payment_intents', [
      'method' => 'POST',
      'headers' => ['Authorization' => "Bearer {$stripeSettings['STRIPE_PRIVATE_KEY']}"],
      'body'   => [
        'amount'   => intval((!empty($body['coupon']) ? $racePrice - $raceCouponsDiscount : $racePrice) * 100),
        'currency' => 'eur',
        'payment_method_types' => ['card'],
        'receipt_email' => null, // TODO: store user email
        'metadata' => [
          'user_id' => $body['user_id'],
          'race_id' => $body['race_id']
        ]
      ]
    ]);

    return new WP_REST_Response(
      json_decode($response['body']),
      $response['response']['code']
    );
  }
]);

/**
 * @link https://developer.wordpress.org/reference/functions/register_rest_route/
 * @since
 */
register_rest_route('wp/v2', '/payment_intents/(?P<id>.+)', [
  'methods'  => ['GET'],
  'permission_callback' => function (WP_REST_Request $request) {
    return true;
  },
  'callback' => function (WP_REST_Request $request) {
    $id = (string) $request['id'];
    $stripeSettings = get_option('stripe_settings');

    $response = wp_remote_get("https://api.stripe.com/v1/payment_intents/{$id}", [
      'method' => 'GET',
      'headers' => ['Authorization' => "Bearer {$stripeSettings['STRIPE_PRIVATE_KEY']}"]
    ]);

    $responseBody = json_decode($response['body']);

    if (!empty($responseBody->status) && $responseBody->status === 'succeeded'):
      add_user_meta($responseBody->metadata->user_id, 'race_payments', $responseBody->metadata->race_id);
    endif;

    return new WP_REST_Response(
      $responseBody,
      $response['response']['code']
    );
  }
]);

/**
 * @link https://developer.wordpress.org/reference/functions/register_rest_route/
 * @since
 */
register_rest_route('wp/v2', '/products/(?P<id>.+)/prices', [
  'methods'  => ['GET'],
  'permission_callback' => function (WP_REST_Request $request) {
    return true;
  },
  'callback' => function (WP_REST_Request $request) {
    $id = (string) $request['id'];
    $stripeSettings = get_option('stripe_settings');

    $response = wp_remote_get("https://api.stripe.com/v1/prices?product={$id}&active=true&type=one_time", [
      'method' => 'GET',
      'headers' => ['Authorization' => "Bearer {$stripeSettings['STRIPE_PRIVATE_KEY']}"]
    ]);

    $responseBody = json_decode($response['body']);

    return new WP_REST_Response(
      !empty($responseBody) && !empty($responseBody->data) ? $responseBody->data : [],
      200
    );
  }
]);

/**
 * @link https://developer.wordpress.org/reference/functions/register_rest_route/
 * @since
 */
register_rest_route('wp/v2', '/products/(?P<id>.+)', [
  'methods'  => ['GET'],
  'permission_callback' => function (WP_REST_Request $request) {
    return true;
  },
  'callback' => function (WP_REST_Request $request) {
    $id = (string) $request['id'];
    $stripeSettings = get_option('stripe_settings');

    $response = wp_remote_get("https://api.stripe.com/v1/products/{$id}", [
      'method' => 'GET',
      'headers' => ['Authorization' => "Bearer {$stripeSettings['STRIPE_PRIVATE_KEY']}"]
    ]);

    $responseBody = json_decode($response['body']);

    return new WP_REST_Response(
      !empty($responseBody) && !empty($responseBody->data) ? $responseBody->data : [],
      $response['response']['code']
    );
  }
]);

/**
 * @link https://developer.wordpress.org/reference/functions/register_rest_route/
 * @since
 */
register_rest_route('wp/v2', '/webhooks', [
  'methods'  => ['POST'],
  'permission_callback' => function (WP_REST_Request $request) {
    return true;
  },
  'callback' => function (WP_REST_Request $request) {
    $body = $request->get_json_params();

    if (
      $body['type'] === 'checkout.session.completed' &&
      !empty($body['data']['object']['metadata']) &&
      !empty($body['data']['object']['metadata'])
    ):
      $meta = (array) $body['data']['object']['metadata'];
      add_user_meta($meta['user_id'], 'race_payments', $meta['race_id']);
      return new WP_REST_Response([
        'message' => "Added user:{$meta['user_id']} to race:{$meta['race_id']}"
      ], 201);
    endif;

    return new WP_REST_Response([
      'message' => "Received event {$body['type']}"
    ], 200);
  }
]);

/**
 * @link https://developer.wordpress.org/reference/functions/register_rest_route/
 * @since
 */
register_rest_route('wp/v2', '/checkouts', [
  'methods'  => ['POST'],
  'permission_callback' => function (WP_REST_Request $request) {
    return true;
  },
  'callback' => function (WP_REST_Request $request) {
    $body = $request->get_json_params();
    $postUrl = get_permalink(@$body['race_id']);
    $stripeSettings = get_option('stripe_settings');

    if (empty($body['price_id']) || empty($body['race_id']) || empty($body['user_id']) || empty($postUrl) || empty(get_user_by('id', $body['user_id']))):
      return new WP_REST_Response([
        'message' => 'El usuario y la carrera son requeridos'
      ], 201);
    endif;

    if (in_array($body['race_id'], get_user_meta($body['user_id'], 'race_payments'))):
      return new WP_REST_Response(['message' => 'Ya has comprado esta subscripción'], 400);
    endif;

    $response = json_decode(wp_remote_post("https://api.stripe.com/v1/checkout/sessions", [
      'method' => 'POST',
      'headers' => [
        'Authorization' => "Bearer {$stripeSettings['STRIPE_PRIVATE_KEY']}"
      ],
      'body' => [
        'success_url' => "{$postUrl}?user_id={$body['user_id']}",
        'cancel_url' => "{$postUrl}?cancel={$body['user_id']}",
        'payment_method_types' => ['card'],
        'line_items' => [
          ['price' => $body['price_id'], 'quantity' => 1],
        ],
        'allow_promotion_codes' => 'true',
        'shipping_address_collection' => [
          'allowed_countries' => ['ES']
        ],
        'submit_type' => 'pay',
        'metadata' => [
          'user_id' => $body['user_id'],
          'race_id' => $body['race_id']
        ],
        'mode' => 'payment'
      ]
    ])['body']);

    if (empty($response->id)):
      return new WP_REST_Response([
        'message' => "No se pudo crear la sesión"
      ], 400);
    endif;

    return new WP_REST_Response([
      'session_id' => $response->id
    ], 201);
  }
]);
