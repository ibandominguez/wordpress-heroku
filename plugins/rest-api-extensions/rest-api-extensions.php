<?php

/*
Plugin Name: Rest Api Extensions
Plugin URI: https://github.com/ibandominguez/wordpress-heroku/tree/master/plugins/rest-api-extensions
Description: Extend wordpress rest api default functionality.
Author: Ibán Dominguez Noda
Author URI: https://github.com/ibandominguez
Version: 0.1.0
*/

function signBasicHeaderToken($email, $password) {
  return 'basic ' . base64_encode($email . ':' . $password);
}

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

  update_user_meta($userId, 'image_url', $providerResponse->picture);
  update_user_meta($userId, 'oauth_provider', $request['provider']);
  update_user_meta($userId, 'oauth_id', $providerResponse->id);

  $userResponseData = getUserFormattedResponseData(get_user_by('id', $userId));
  $userResponseData['image_url'] = $providerResponse->picture;
  $userResponseData['basic_authorization_header'] = signBasicHeaderToken($userData['user_email'], $userData['user_pass']);
  $response = new WP_REST_Response($userResponseData);
  $response->set_status(201);
  return $response;
}

function registerAccessTokenHandler() {
  register_rest_route('wp/v2', '/access-token-users', array(
    'methods' => 'POST',
    'callback' => 'handleAccessTokenRequest'
  ));
}

function getUserFormattedResponseData($user) {
  $data = [];

  $data['id'] = $user->ID;
  $data['username'] = $user->user_login;
  $data['name'] = $user->display_name;
  $data['first_name'] = $user->first_name;
  $data['last_name'] = $user->last_name;
  $data['email'] = $user->user_email;
  $data['url'] = $user->user_url;
  $data['description'] = $user->description;
  $data['link'] = get_author_posts_url($user->ID, $user->user_nicename);
  $data['locale'] = get_user_locale($user);
  $data['nickname'] = $user->nickname;
  $data['slug'] = $user->user_nicename;
  $data['roles'] = array_values($user->roles);
  $data['registered_date'] = gmdate('c', strtotime($user->user_registered));
  $data['capabilities'] = (object) $user->allcaps;
  $data['extra_capabilities'] = (object) $user->caps;
  $data['avatar_urls'] = rest_get_avatar_urls($user);
  $data['meta'] = get_user_meta($user->ID);

  return $data;
}
