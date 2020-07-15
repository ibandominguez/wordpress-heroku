<?php

/*
Plugin Name: Rest Meta filters
Plugin URI: https://github.com/ibandominguez/wordpress-heroku/tree/master/plugins/meta-filters/rest-meta-filters.php
Description: Add rest api meta filter support
Author: Ibán Dominguez Noda
Author URI: https://github.com/ibandominguez
Version: 0.1.0
*/

/**
 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/
 */
add_action('rest_api_init', function() {
  $postTypes = get_post_types(['show_in_rest' => true], 'objects');
  $taxonomies = get_taxonomies(['show_in_rest' => true], 'objects');

  // Post types
  foreach ($postTypes as $postType):
    add_filter("rest_{$postType->name}_query", 'metaFiltersAddParamOrParams', 10, 2);
  endforeach;

  // Taxonomies and users
  foreach ($taxonomies as $taxonomy):
    add_filter("rest_{$taxonomy->name}_query", 'metaFiltersAddParamOrParams', 10, 2);
    add_filter('rest_user_query', 'metaFiltersAddParamOrParams', 10, 2);
  endforeach;
});

/**
 * @param  array
 * @param  WP_REST_Request
 * @return array $args.
 */
function metaFiltersAddParamOrParams($args, $request) {
  global $wp;

  $filter = $request['filter'];

	// Check for filter
	if (empty($filter) || !is_array($filter)):
		return $args;
	endif;

	// Handle posts_per_page
	if (isset($filter['posts_per_page']) && ((int) $filter['posts_per_page'] >= 1 && (int) $filter['posts_per_page'] <= 100)):
		$args['posts_per_page'] = $filter['posts_per_page'];
	endif;

  $vars = array_merge(apply_filters('rest_query_vars', $wp->public_query_vars), [
    'meta_query',
    'meta_key',
    'meta_value',
    'meta_compare'
  ]);

	foreach ($vars as $var):
		if (isset($filter[$var])):
			$args[$var] = $filter[$var];
		endif;
	endforeach;

	return $args;
}
