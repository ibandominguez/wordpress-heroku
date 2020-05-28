<?php

/*
Plugin Name: PluginTemplate
Plugin URI: https://github.com/ibandominguez/wp-plugin-template
Description: Plugin Wp Tempalte
Author: Ibán Dominguez Noda
Author URI: https://github.com/ibandominguez
Version: 0.1.0
*/

class PluginTemplate
{
  private $postType = 'my-post-type';

  public function __construct()
  {
    add_action('init', array($this, 'registerPostType'));
    add_action('add_meta_boxes', array($this, 'addPostTypeMetaBox'));
    add_action('save_post', array($this, 'handlePostTypeSave'));
  }

  public function registerPostType()
  {
    $labels = array(
      'name'                  => _x('My PluginTypes', 'Post type general name', 'textdomain'),
      'singular_name'         => _x('My PluginType', 'Post type singular name', 'textdomain'),
      'menu_name'             => _x('My PluginTypes', 'Admin Menu text', 'textdomain'),
      'name_admin_bar'        => _x('My PluginType', 'Add New on Toolbar', 'textdomain'),
      'add_new'               => __('Add New', 'textdomain'),
      'add_new_item'          => __('Add New My PluginType', 'textdomain'),
      'new_item'              => __('New My PluginType', 'textdomain'),
      'edit_item'             => __('Edit My PluginType', 'textdomain'),
      'view_item'             => __('View My PluginType', 'textdomain'),
      'all_items'             => __('All My PluginTypes', 'textdomain'),
      'search_items'          => __('Search My PluginTypes', 'textdomain'),
      'parent_item_colon'     => __('Parent My PluginTypes:', 'textdomain'),
      'not_found'             => __('No pluginTypes found.', 'textdomain'),
      'not_found_in_trash'    => __('No pluginTypes found in Trash.', 'textdomain'),
      'featured_image'        => _x('My PluginType Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'textdomain'),
      'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'textdomain'),
      'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'textdomain'),
      'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'textdomain'),
      'archives'              => _x('My PluginType archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'textdomain'),
      'insert_into_item'      => _x('Insert into pluginType', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'textdomain'),
      'uploaded_to_this_item' => _x('Uploaded to this pluginType', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'textdomain'),
      'filter_items_list'     => _x('Filter pluginTypes list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'textdomain'),
      'items_list_navigation' => _x('My PluginTypes list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'textdomain'),
      'items_list'            => _x('My PluginTypes list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'textdomain'),
    );

    $args = array(
      'labels'             => $labels,
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array('slug' => $this->postType),
      'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => null,
      'supports'           => array('title', 'thumbnail'),
    );

    register_post_type($this->postType, $args);
  }

  public function addPostTypeMetaBox($postType)
  {
    add_meta_box(
      "meta-{$this->postType}-id",
      __('Some Meta Box Headline', 'textdomain'),
      array($this, 'renderMetaBox'),
      $postType,
      'advanced',
      'high'
    );
  }

  public function renderMetaBox($post)
  {
    // Add an nonce field so we can check for it later.
    wp_nonce_field('myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce');

    // Use get_post_meta to retrieve an existing value from the database.
    $value = get_post_meta($post->ID, '_my_meta_value_key', true);

    // Display the form, using the current value.
    ?>
      <label for="_my_meta_value_key">
        <?php _e('Description for this field', 'textdomain'); ?>
        <input type="text" id="_my_meta_value_key" name="_my_meta_value_key" value="<?php echo esc_attr($value); ?>" />
      </label>
    <?php
  }

  public function handlePostTypeSave($postId)
  {
    $nonce = @$_POST['myplugin_inner_custom_box_nonce'];
    $data = sanitize_text_field(@$_POST['_my_meta_value_key']);

    if (
      empty($nonce) || // Check if nonce exists
      !wp_verify_nonce($nonce, 'myplugin_inner_custom_box') || // Verify nonce
      (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || // Check for autosave
      !current_user_can('edit_' . ($_POST['post_type'] === 'page' ? 'page' : 'post'), $postId) // Permissions
    ) {
      return $postId;
    }

    update_post_meta($postId, '_my_meta_value_key', $data);
  }
}

new PluginTemplate();
