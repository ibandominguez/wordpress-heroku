<?php

/*
Plugin Name: Flexible user meta endpoints
Plugin URI: https://github.com/ibandominguez/wordpress-heroku/tree/master/plugins/users-meta-endpoints/
Description: Creates the following squema free routes: CRUD /users/me/{metas_key}/{meta_id}
Author: Ibán Dominguez Noda
Author URI: https://github.com/ibandominguez
Version: 0.1.0
*/

require_once __DIR__.'/includes/UserMetasRoutes.php';

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
