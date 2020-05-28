<?php

function makeRequest($provider, $token) {
  switch ($provider) {
    case 'facebook':
      return json_decode(file_get_contents("https://graph.facebook.com/me?access_token=$token"));
    case 'google':
      return json_decode(file_get_contents("https://www.googleapis.com/oauth2/v1/userinfo?access_token=$token"));
  }
}

function handleAccessTokenRequest($request) {
  $types = array('facebook', 'google');

  if (
    empty($request['provider']) ||
    empty($request['token']) ||
    !in_array($request['provider'], $types)
  ):
    $response = new WP_REST_Response(array('message' => 'Provider and token are required'));
    $response->set_status(400);
    return $response;
  endif;

  $providerResponse = makeRequest(
    $request['provider'],
    $request['token']
  );

  if (empty($providerResponse->email) || empty($providerResponse->name)):
    $response = new WP_REST_Response(array('message' => 'Error fetching data from provider'));
    $response->set_status(400);
    return $response;
  endif;

  $userData = array(
    'user_login' => $providerResponse->email,
    'user_pass' => wp_generate_password(),
    'user_email' => $providerResponse->email,
    'first_name' => $providerResponse->given_name,
    'last_name' => $providerResponse->family_name,
    'display_name' => $providerResponse->name,
    'role' => 'subscriber'
  );

  if ($userId = email_exists($providerResponse->email)):
    $userData['ID'] = $userId;
    wp_update_user($userData);
  endif;

  $userId = wp_insert_user($userData);

  add_user_meta($userId, 'image_url', $providerResponse->picture);
  add_user_meta($userId, 'oauth_provider', $request['provider']);
  add_user_meta($userId, 'oauth_id', $providerResponse->id);

  $userData['image_url'] = $providerResponse->picture;
  $userData['basic_authorization_header'] = 'basic ' . base64_encode($userData['user_email'] . ':' . $userData['user_pass']);
  $response = new WP_REST_Response($userData);
  $response->set_status(201);
  return $response;
}

function registerAccessTokenHandler() {
  register_rest_route('wp/v2', '/access-token-users', array(
    'methods' => 'POST',
    'callback' => 'handleAccessTokenRequest'
  ));
}
