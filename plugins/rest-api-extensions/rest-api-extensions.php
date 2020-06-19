<?php

/*
Plugin Name: Rest Api Extensions
Plugin URI: https://github.com/ibandominguez/wordpress-heroku/tree/master/plugins/rest-api-extensions
Description: Extend wordpress rest api default functionality.
Author: Ibán Dominguez Noda
Author URI: https://github.com/ibandominguez
Version: 0.1.0
*/

require_once __DIR__.'/includes/basic.php';
require_once __DIR__.'/includes/access-token.php';
require_once __DIR__.'/includes/ModifyUsersPostRestRoute.php';
require_once __DIR__.'/includes/UserMetasRoutes.php';

// Basic auth
add_filter('determine_current_user', 'determineCurrentUser', 20);
add_filter('rest_authentication_errors', 'restAuthenticationErrors');

// Access token
add_action('rest_api_init', 'registerAccessTokenHandler');

/**
 * Moddifies POST /wp-json/wp/v2/users
 * allowing users to be registered as subscribers and
 * returning the basic authorization header in the default response
 */
ModifyUsersPostRestRoute::boot();

/**
 * Adds metas routes
 *
 * GET /users/me/<metas_key>
 * POST /users/me/<metas_key> {any...}
 * GET /users/me/<metas_key>/<meta_id>
 * PUT /users/me/<metas_key>/<meta_id> {any...}
 * DELETE /users/me/<metas_key><meta_id>
 */
UserMetasRoutes::boot();
