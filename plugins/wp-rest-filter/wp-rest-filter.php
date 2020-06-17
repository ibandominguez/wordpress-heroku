<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://sk8.tech/?utm_source=wp-admin&utm_medium=forum&utm_campaign=wp-rest-filter
 * @since             1.0.0
 * @package           Wp_Rest_Filter
 *
 * @wordpress-plugin
 * Plugin Name:       WP REST Filter
 * Plugin URI:        https://sk8.tech/?utm_source=wp-admin&utm_medium=forum&utm_campaign=wp-rest-filter
 * Description:       This plugin adds the 'Filter' feature to the default WordPress REST APis. It supports CPT, ACF, Taxoomy, and Multiple Meta Queries.
 * Version:           1.4.3
 * Author:            SK8Tech
 * Author URI:        https://sk8.tech/?utm_source=wp-admin&utm_medium=forum&utm_campaign=wp-rest-filter
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-rest-filter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('WP_REST_FILTER_VERSION', '1.4.3');

/**
 * Initialize Freemius SDK
 *
 * @link https://freemius.com/help/documentation/selling-with-freemius/integrating-freemius-sdk/ Freemius Intregration Guide
 * @since 1.4.0
 * @author Jacktator
 */
if (!function_exists('wrf_fs')) {
	// Create a helper function for easy SDK access.
	function wrf_fs() {
		global $wrf_fs;

		if (!isset($wrf_fs)) {
			// Include Freemius SDK.
			require_once dirname(__FILE__) . '/freemius/start.php';

			$wrf_fs = fs_dynamic_init(array(
				'id' => '3359',
				'slug' => 'wp-rest-filter',
				'type' => 'plugin',
				'public_key' => 'pk_b6e92fbf44a5f1fb4b6aab57eca88',
				'is_premium' => false,
				'has_addons' => false,
				'has_paid_plans' => false,
				'menu' => array(
					'first-path' => 'plugins.php',
					'account' => false,
				),
			));
		}

		return $wrf_fs;
	}

	// Init Freemius.
	wrf_fs();
	// Signal that SDK was initiated.
	do_action('wrf_fs_loaded');

	function wrf_fs_custom_connect_message_on_connect(
		$message,
		$user_first_name,
		$plugin_title,
		$user_login,
		$site_link,
		$freemius_link
	) {
		return sprintf(
			__('Hey %1$s') . ',<br>' .
			__('never miss an important update -- opt-in to our security and feature updates notifications, and non-sensitive diagnostic tracking.', 'wp-rest-filter'),
			$user_first_name,
			'<b>' . $plugin_title . '</b>',
			'<b>' . $user_login . '</b>',
			$site_link,
			$freemius_link
		);
	}

	wrf_fs()->add_filter('connect_message', 'wrf_fs_custom_connect_message_on_connect', 10, 6);

	function wrf_fs_custom_connect_message_on_update(
		$message,
		$user_first_name,
		$plugin_title,
		$user_login,
		$site_link,
		$freemius_link
	) {
		return sprintf(
			__('Hey %1$s') . ',<br>' .
			__('Please help us improve %2$s! If you opt-in, some data about your usage of %2$s will be sent. If you skip this, that\'s okay! %2$s will still work just fine.', 'wp-rest-filter'),
			$user_first_name,
			'<b>' . $plugin_title . '</b>',
			'<b>' . $user_login . '</b>',
			$site_link,
			$freemius_link
		);
	}

	wrf_fs()->add_filter('connect_message_on_update', 'wrf_fs_custom_connect_message_on_update', 10, 6);

	// Not like register_uninstall_hook(), you do NOT have to use a static function.
	wrf_fs()->add_action('after_uninstall', 'wru_fs_uninstall_cleanup');
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-rest-filter-activator.php
 */
function activate_wp_rest_filter() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-wp-rest-filter-activator.php';
	Wp_Rest_Filter_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-rest-filter-deactivator.php
 */
function deactivate_wp_rest_filter() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-wp-rest-filter-deactivator.php';
	Wp_Rest_Filter_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wp_rest_filter');
register_deactivation_hook(__FILE__, 'deactivate_wp_rest_filter');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wp-rest-filter.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_rest_filter() {

	$plugin = new Wp_Rest_Filter();
	$plugin->run();

}
run_wp_rest_filter();
