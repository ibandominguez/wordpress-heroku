<?php

/**
 * Plugin Name: WP Rest api user registration
 * Plugin URI: https://github.com/ibandominguez/wordpress-heroku/tree/master/plugins/rest-api-extensions/ModifyUsersPostRestRoute.php
 * Description: Allow users to register as subscribers using the rest api.
 * Author: Ibán Dominguez Noda
 * Author URI: https://github.com/ibandominguez
 * Version: 0.1.0
*/

class ModifyUsersPostRestRoute {
  /**
   * @var WP_REST_Users_Controller
   */
  private $baseController;

  public function __construct()
  {
    $this->baseController = new WP_REST_Users_Controller();

    /** @link https://developer.wordpress.org/reference/hooks/rest_api_init/ */
    add_action('rest_api_init', array($this, 'registerUsersPostRoute'));
  }

  /**
   * @return void
   */
  public function registerUsersPostRoute()
  {
    /** @link https://developer.wordpress.org/reference/functions/register_rest_route/ */
    register_rest_route('wp/v2', '/users', [[
      'methods' => WP_REST_Server::CREATABLE,
      'args' => $this->baseController->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
      'callback' => array($this, 'callback'),
      'permission_callback' => array($this, 'permissionCallback')
    ]]);
  }

  /**
   * @param WP_REST_Request|WP_Error $request
   *
   * @return WP_REST_Response
   */
  public function callback($request)
  {
    $response = $this->baseController->create_item($request);

    if (!is_wp_error($response)):
      $responseData = $response->get_data();
      $responseData['basic_authorization_header'] = $this->makeBasicAuthHeader($request['email'], $request['password']);
      $response->set_data($responseData);
    endif;

    return $response;
  }

  /**
   * @param WP_REST_Request $request
   *
   * @return Bool|WP_Error
   */
  public function permissionCallback($request) {
    if (!current_user_can('create_users') && ($request['roles'] && $request['roles'] !== array('subscriber'))):
      return new WP_Error(
        'rest_cannot_create_user',
        __('Sorry, you are only allowed to create new users with the subscriber role.'),
        array('status' => rest_authorization_required_code())
      );
    else:
      $request->set_param('roles', array('subscriber'));
    endif;

    return true;
  }

  /**
   * @param String $email
   * @param String $password
   *
   * @return String
   */
  private function makeBasicAuthHeader($email, $password)
  {
    return 'basic ' . base64_encode("{$email}:{$password}");
  }
}
