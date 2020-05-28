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

// Basic auth
add_filter('determine_current_user', 'determineCurrentUser', 20);
add_filter('rest_authentication_errors', 'restAuthenticationErrors');

// Rest api meta support
add_action('rest_api_init', 'retrievePostMeta');
