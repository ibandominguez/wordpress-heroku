<?php

/*
Plugin Name: Custom fields and types
Plugin URI: https://github.com/ibandominguez/heroku-wordpress/custom-fields-and-types
Description: Custom fields and types implementation
Author: Ibán Dominguez Noda
Author URI: https://github.com/ibandominguez
Version: 0.1.6
*/

require_once(__DIR__.'/includes/CustomField.php');
require_once(__DIR__.'/includes/CustomTypes.php');
require_once(__DIR__.'/includes/CustomFields.php');

CustomTypes::boot();
CustomFields::boot();
