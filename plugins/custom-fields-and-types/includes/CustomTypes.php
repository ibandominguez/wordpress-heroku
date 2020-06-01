<?php

class CustomTypes
{
  private $postType = 'custom_type';
  private $metaKey = '_settings';

  static public function boot()
  {
    return new self();
  }

  public function __construct()
  {
    add_action('init', array($this, 'registerCustomTypes'));
    add_action('init', array($this, 'registerPostType'));
    add_action('add_meta_boxes', array($this, 'addPostTypeMetaBox'));
    add_action('save_post', array($this, 'handlePostTypeSave'));
  }

  public function registerPostType()
  {
    $args = array(
      'labels'             => array(
        'name'                  => _x('Custom types', 'Post type general name', $this->postType),
        'singular_name'         => _x('Custom type', 'Post type singular name', $this->postType),
        'menu_name'             => _x('Custom types', 'Admin Menu text', $this->postType),
        'name_admin_bar'        => _x('Custom type', 'Add New on Toolbar', $this->postType),
        'add_new'               => __('Add New', $this->postType),
        'add_new_item'          => __('Add New Custom type', $this->postType),
        'new_item'              => __('New Custom type', $this->postType),
        'edit_item'             => __('Edit Custom type', $this->postType),
        'view_item'             => __('View Custom type', $this->postType),
        'all_items'             => __('All Custom types', $this->postType),
        'search_items'          => __('Search Custom types', $this->postType),
        'parent_item_colon'     => __('Parent Custom types:', $this->postType),
        'not_found'             => __('No pluginTypes found.', $this->postType),
        'not_found_in_trash'    => __('No pluginTypes found in Trash.', $this->postType),
        'featured_image'        => _x('Custom type Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', $this->postType),
        'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', $this->postType),
        'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', $this->postType),
        'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', $this->postType),
        'archives'              => _x('Custom type archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', $this->postType),
        'insert_into_item'      => _x('Insert into pluginType', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', $this->postType),
        'uploaded_to_this_item' => _x('Uploaded to this pluginType', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', $this->postType),
        'filter_items_list'     => _x('Filter pluginTypes list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', $this->postType),
        'items_list_navigation' => _x('Custom types list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', $this->postType),
        'items_list'            => _x('Custom types list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', $this->postType),
      ),
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
      'menu_icon'          => 'dashicons-chart-pie',
      'supports'           => array('title')
    );

    register_post_type($this->postType, $args);
  }

  public function addPostTypeMetaBox($currentPostType)
  {
    register_meta('post', $this->metaKey, array(
      'object_subtype' => $this->postType,
      'type' => 'array',
      'description' => 'Custom type settings',
      'single' => true,
      'show_in_rest' => false
    ));

    add_meta_box(
      $this->postType.$this->metaKey,
      __('Custom type configuration', $this->postType),
      array($this, 'renderMetaBox'),
      $this->postType,
      'advanced',
      'high'
    );
  }

  public function formatInitialValues($settings)
  {
    if (empty($settings)):
      $settings = array();
    endif;

    $settings['menu_position'] = intval($settings['menu_position']);

    if (empty($settings['labels'])):
      $settings['labels'] = array(
        'name'                  => 'Post type general name',
        'singular_name'         => 'Post type singular name',
        'menu_name'             => 'Admin Menu text',
        'name_admin_bar'        => 'Add New on Toolbar',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New Custom type',
        'new_item'              => 'New Custom type',
        'edit_item'             => 'Edit Custom type',
        'view_item'             => 'View Custom type',
        'all_items'             => 'All Custom types',
        'search_items'          => 'Search Custom types',
        'parent_item_colon'     => 'Parent Custom types:',
        'not_found'             => 'No pluginTypes found.',
        'not_found_in_trash'    => 'No pluginTypes found in Trash.',
        'featured_image'        => 'Custom type Cover Image',
        'set_featured_image'    => 'Set cover image',
        'remove_featured_image' => 'Remove cover image',
        'use_featured_image'    => 'Use as cover image',
        'archives'              => 'Custom type archives',
        'insert_into_item'      => 'Insert into pluginType',
        'uploaded_to_this_item' => 'Uploaded to this pluginType',
        'filter_items_list'     => 'Filter pluginTypes list',
        'items_list_navigation' => 'Custom types list navigation',
        'items_list'            => 'Custom types list'
      );
    endif;

    return $settings;
  }

  public function renderMetaBox($post)
  {
    wp_nonce_field("{$this->metaKey}_box", "{$this->metaKey}_box_nonce");
    $value = get_post_meta($post->ID, $this->metaKey, true);
    $this->renderMetaBoxHTML($this->formatInitialValues($value));
  }

  public function renderMetaBoxHTML($value) { ?>
    <style media="screen">
    .form-settings input[type=text],
    .form-settings input[type=number],
    .form-settings select { width: 100%; margin-top: 5; margin-bottom: 10px; }
    </style>
    <div class="form-settings" ng-app ng-init='settings = <?= !empty($value) ? json_encode($value) : '{}'; ?>'>
      <h4>Settings</h4>

      <div class="form-group">
        <label>Key
          <input ng-change="settings.key = settings.key.toLowerCase().replace(' ', '')" type="text" maxlength="20" name="<?= $this->metaKey; ?>[key]" ng-model="settings.key">
        </label>
        <label>Slug rewrite
          <input type="text" name="<?= $this->metaKey; ?>[rewrite][slug]" ng-model="settings.rewrite.slug">
        </label>
        <label>Menu position
          <input type="number" name="<?= $this->metaKey; ?>[menu_position]" ng-model="settings.menu_position">
        </label>
      </div>

      <div class="form-group">
        <input type="checkbox" name="<?= $this->metaKey; ?>[public]" ng-checked="!!settings.public" ng-model="settings.public" value="true" /> Public<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[publicly_queryable]" ng-checked="!!settings.publicly_queryable" ng-model="settings.publicly_queryable" value="true" /> Public queryable<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[show_ui]" ng-checked="!!settings.show_ui" ng-model="settings.show_ui" value="true" /> Show ui<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[show_in_menu]" ng-checked="!!settings.show_in_menu" ng-model="settings.show_in_menu" value="true" /> Show in menu<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[query_var]" ng-checked="!!settings.query_var" ng-model="settings.query_var" value="true" /> Query var<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[has_archive]" ng-checked="!!settings.has_archive" ng-model="settings.has_archive" value="true" /> Has archive<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[hierarchical]" ng-checked="!!settings.hierarchical" ng-model="settings.hierarchical" value="true" /> Hierarchical<br>
      </div>

      <h4>Post type supports</h4>

      <div class="form-group">
        <input type="checkbox" name="<?= $this->metaKey; ?>[supports][]" ng-checked="settings.supports.indexOf('title') !== -1" value="title" /> Title<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[supports][]" ng-checked="settings.supports.indexOf('editor') !== -1" value="editor" /> Editor<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[supports][]" ng-checked="settings.supports.indexOf('comments') !== -1" value="comments" /> Comments<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[supports][]" ng-checked="settings.supports.indexOf('revisions') !== -1" value="revisions" /> Revisions<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[supports][]" ng-checked="settings.supports.indexOf('trackbacks') !== -1" value="trackbacks" /> Trackbacks<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[supports][]" ng-checked="settings.supports.indexOf('author') !== -1" value="author" /> Author<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[supports][]" ng-checked="settings.supports.indexOf('excerpt') !== -1" value="excerpt" /> Excerpt<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[supports][]" ng-checked="settings.supports.indexOf('page') !== -1" value="page-attributes" /> Page attributes<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[supports][]" ng-checked="settings.supports.indexOf('thumbnail') !== -1" value="thumbnail" /> Thumbnail<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[supports][]" ng-checked="settings.supports.indexOf('custom') !== -1" value="custom-fields" /> Custom fields<br>
        <input type="checkbox" name="<?= $this->metaKey; ?>[supports][]" ng-checked="settings.supports.indexOf('post') !== -1" value="post-formats" /> Post formats<br>
      </div>

      <h4>Labels</h4>

      <div class="form-group">
        <label>name
          <input type="text" name="<?= $this->metaKey; ?>[labels][name]" ng-model="settings.labels.name">
        </label>
        <label>singular_name
          <input type="text" name="<?= $this->metaKey; ?>[labels][singular_name]" ng-model="settings.labels.singular_name">
        </label>
        <label>menu_name
          <input type="text" name="<?= $this->metaKey; ?>[labels][menu_name]" ng-model="settings.labels.menu_name">
        </label>
        <label>name_admin_bar
          <input type="text" name="<?= $this->metaKey; ?>[labels][name_admin_bar]" ng-model="settings.labels.name_admin_bar">
        </label>
        <label>add_new
          <input type="text" name="<?= $this->metaKey; ?>[labels][add_new]" ng-model="settings.labels.add_new">
        </label>
        <label>add_new_item
          <input type="text" name="<?= $this->metaKey; ?>[labels][add_new_item]" ng-model="settings.labels.add_new_item">
        </label>
        <label>new_item
          <input type="text" name="<?= $this->metaKey; ?>[labels][new_item]" ng-model="settings.labels.new_item">
        </label>
        <label>edit_item
          <input type="text" name="<?= $this->metaKey; ?>[labels][edit_item]" ng-model="settings.labels.edit_item">
        </label>
        <label>view_item
          <input type="text" name="<?= $this->metaKey; ?>[labels][view_item]" ng-model="settings.labels.view_item">
        </label>
        <label>all_items
          <input type="text" name="<?= $this->metaKey; ?>[labels][all_items]" ng-model="settings.labels.all_items">
        </label>
        <label>search_items
          <input type="text" name="<?= $this->metaKey; ?>[labels][search_items]" ng-model="settings.labels.search_items">
        </label>
        <label>parent_item_colon
          <input type="text" name="<?= $this->metaKey; ?>[labels][parent_item_colon]" ng-model="settings.labels.parent_item_colon">
        </label>
        <label>not_found
          <input type="text" name="<?= $this->metaKey; ?>[labels][not_found]" ng-model="settings.labels.not_found">
        </label>
        <label>not_found_in_trash
          <input type="text" name="<?= $this->metaKey; ?>[labels][not_found_in_trash]" ng-model="settings.labels.not_found_in_trash">
        </label>
        <label>featured_image
          <input type="text" name="<?= $this->metaKey; ?>[labels][featured_image]" ng-model="settings.labels.featured_image">
        </label>
        <label>set_featured_image
          <input type="text" name="<?= $this->metaKey; ?>[labels][set_featured_image]" ng-model="settings.labels.set_featured_image">
        </label>
        <label>remove_featured_image
          <input type="text" name="<?= $this->metaKey; ?>[labels][remove_featured_image]" ng-model="settings.labels.remove_featured_image">
        </label>
        <label>use_featured_image
          <input type="text" name="<?= $this->metaKey; ?>[labels][use_featured_image]" ng-model="settings.labels.use_featured_image">
        </label>
        <label>archives
          <input type="text" name="<?= $this->metaKey; ?>[labels][archives]" ng-model="settings.labels.archives">
        </label>
        <label>insert_into_item
          <input type="text" name="<?= $this->metaKey; ?>[labels][insert_into_item]" ng-model="settings.labels.insert_into_item">
        </label>
        <label>uploaded_to_this_item
          <input type="text" name="<?= $this->metaKey; ?>[labels][uploaded_to_this_item]" ng-model="settings.labels.uploaded_to_this_item">
        </label>
        <label>filter_items_list
          <input type="text" name="<?= $this->metaKey; ?>[labels][filter_items_list]" ng-model="settings.labels.filter_items_list">
        </label>
        <label>items_list_navigation
          <input type="text" name="<?= $this->metaKey; ?>[labels][items_list_navigation]" ng-model="settings.labels.items_list_navigation">
        </label>
        <label>items_list
          <input type="text" name="<?= $this->metaKey; ?>[labels][items_list]" ng-model="settings.labels.items_list">
        </label>
      </div>
    </div>
  <?php }

  public function handlePostTypeSave($postId)
  {
    $nonce = @$_POST["{$this->metaKey}_box_nonce"];
    $data = @$_POST[$this->metaKey];

    if (
      empty($data) || // Our desired update data does not exists
      !wp_verify_nonce($nonce, "{$this->metaKey}_box") || // Verify nonce
      (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || // Check for autosave
      !current_user_can('edit_' . ($_POST['post_type'] === 'page' ? 'page' : 'post'), $postId) // Permissions
    ) {
      return $postId;
    }

    foreach ($data as $key => &$value):
      if ($value === 'true'):
        $value = true;
      endif;
    endforeach;

    update_post_meta($postId, $this->metaKey, $data);
  }

  public function registerCustomTypes()
  {
    /** @link https://developer.wordpress.org/reference/functions/get_posts/ */
    $customTypes = get_posts(array(
      'numberposts' => -1,
      'post_type' => $this->postType
    ));

    foreach ($customTypes as $type):
      $settings = get_post_meta($type->ID, $this->metaKey, true);

      if (!empty($settings['key']) && strlen($settings['key']) <= 20):
        register_post_type($settings['key'], $settings);
      endif;
    endforeach;
  }

  private function dump($mixed, $terminate = false) {
    echo '<pre>';
    var_dump($mixed);
    echo '</pre>';

    if ($terminate):
      exit();
    endif;
  }
}
