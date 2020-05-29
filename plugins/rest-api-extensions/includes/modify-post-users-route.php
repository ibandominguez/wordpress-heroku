<?php

function modifyPostUsersRoute() {
  $users_controller = new WP_REST_Users_Controller();

  register_rest_route('wp/v2', '/users', array(
    array(
      'methods'             => WP_REST_Server::CREATABLE,
      'args'                => $users_controller->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
      'callback'            => function($request) use ($users_controller) {
        $response = $users_controller->create_item($request);

        if (!is_wp_error($response)):
          $responseData = $response->get_data();
          $responseData['basic_authorization_header'] = signBasicHeaderToken($request['email'], $request['password']);
          $response->set_data($responseData);
        endif;
        
        return $response;
      },
      'permission_callback' => function($request) {
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
    )
  ));
}
