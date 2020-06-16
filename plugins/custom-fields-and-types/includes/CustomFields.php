<?php

class CustomFields
{
  private $pluginTextDomain = 'fields-group-text-domain';
  private $customFieldsGroupPostType = 'fields_group';
  private $settingsMetaKey = '_my_cfg_settings';

  static public function boot()
  {
    return new self();
  }

  public function __construct()
  {
    add_action('init', array($this, 'registerCustomFieldsGroupPostType'));
    add_action('init', array($this, 'configureCustomFieldsGroups'));
    add_action('add_meta_boxes', array($this, 'addPostTypeMetaBox'));
    add_action('save_post', array($this, 'handlePostTypeSave'));
    add_action('admin_enqueue_scripts', array($this, 'registerScriptsAndStyles'));
  }

  /**
   * @return void
   */
  public function registerCustomFieldsGroupPostType()
  {
    $labels = array(
      'name'                  => _x('Custom fields', 'Post type general name', $this->pluginTextDomain),
      'singular_name'         => _x('Custom field', 'Post type singular name', $this->pluginTextDomain),
      'menu_name'             => _x('Custom fields', 'Admin Menu text', $this->pluginTextDomain),
      'name_admin_bar'        => _x('Custom field', 'Add New on Toolbar', $this->pluginTextDomain),
      'add_new'               => __('Add New', $this->pluginTextDomain),
      'add_new_item'          => __('Add New Custom field', $this->pluginTextDomain),
      'new_item'              => __('New Custom field', $this->pluginTextDomain),
      'edit_item'             => __('Edit Custom field', $this->pluginTextDomain),
      'view_item'             => __('View Custom field', $this->pluginTextDomain),
      'all_items'             => __('All Custom fields', $this->pluginTextDomain),
      'search_items'          => __('Search Custom fields', $this->pluginTextDomain),
      'parent_item_colon'     => __('Parent Custom fields:', $this->pluginTextDomain),
      'not_found'             => __('No pluginTypes found.', $this->pluginTextDomain),
      'not_found_in_trash'    => __('No pluginTypes found in Trash.', $this->pluginTextDomain),
      'featured_image'        => _x('Custom field Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', $this->pluginTextDomain),
      'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', $this->pluginTextDomain),
      'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', $this->pluginTextDomain),
      'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', $this->pluginTextDomain),
      'archives'              => _x('Custom field archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', $this->pluginTextDomain),
      'insert_into_item'      => _x('Insert into pluginType', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', $this->pluginTextDomain),
      'uploaded_to_this_item' => _x('Uploaded to this pluginType', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', $this->pluginTextDomain),
      'filter_items_list'     => _x('Filter pluginTypes list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', $this->pluginTextDomain),
      'items_list_navigation' => _x('Custom fields list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', $this->pluginTextDomain),
      'items_list'            => _x('Custom fields list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', $this->pluginTextDomain),
    );

    $args = array(
      'labels'             => $labels,
      'public'             => false,
      'publicly_queryable' => false,
      'show_ui'            => true,
      'show_in_menu'       => current_user_can('administrator'),
      'query_var'          => true,
      'rewrite'            => array('slug' => $this->customFieldsGroupPostType),
      'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => null,
      'menu_icon'          => 'dashicons-chart-pie',
      'supports'           => array('title'),
    );

    register_post_type($this->customFieldsGroupPostType, $args);
  }

  /**
   * @param String $currentPostType
   *
   * @return void
   */
  public function addPostTypeMetaBox($currentPostType)
  {
    if ($currentPostType === $this->customFieldsGroupPostType):
      /** @link https://developer.wordpress.org/reference/functions/register_meta/ */
      register_meta('post', $this->settingsMetaKey, array(
        'object_subtype' => $currentPostType,
        'type' => 'array', // 'string', 'boolean', 'integer', 'number', 'array', and 'object'.
        'description' => 'Custom fields group settings',
        'single' => true,
        // 'sanitize_callback' => function() {},
        // 'auth_callback' => function() {},
        'show_in_rest' => false
      ));

      /** @link https://developer.wordpress.org/reference/functions/add_meta_box/ */
      add_meta_box(
        "meta-{$this->customFieldsGroupPostType}-id",
        __('Custom fields group settings', $this->pluginTextDomain),
        array($this, 'renderMetaBox')
      );
    endif;
  }

  /**
   * @param WP_Post $post
   *
   * @return void
   */
  public function renderMetaBox($post)
  {
    wp_nonce_field($this->settingsMetaKey, "{$this->settingsMetaKey}_nonce");
    $value = get_post_meta($post->ID, $this->settingsMetaKey, true);
    $this->renderSettingsForm($this->formatInitialValues($value)); // HTML to be embbed in the form
  }

  public function formatInitialValues($settings)
  {
    if (empty($settings)):
      $settings = array();
    endif;

    if (empty($settings['fields'])):
      $settings['fields'] = array();
    endif;

    return $settings;
  }

  /**
   * @param Array $settings - previous db stored settings
   *
   * @return void
   */
  public function renderSettingsForm($settings) { ?>
    <style media="screen">
    .row { display: flex; justify-content: center; align-items: center; }
    .item { padding: 15px; }
    .flex-full { flex: 1; }
    .flex-half { flex: 0.5; }
    .flex-quarter { flex: 0.25; }
    .w-100 { width: 100%; }
    .mb-sm { margin-bottom: 5px; }
    .text-center { text-align: center; }
    </style>
    <div ng-app ng-init='data = <?= json_encode($settings); ?>'>
      <!-- PostTypes -->
      <div class="row">
        <div class="item flex-quarter">
          <h4>Post types:</h4>
        </div>
        <div class="item flex-full">
          <?php foreach (get_post_types(array('public' => true)) as $type): ?>
            <div>
              <input type="checkbox"
                ng-checked="data.settings.types && data.settings.types.indexOf('<?= $type; ?>') !== -1"
                name="<?= $this->settingsMetaKey; ?>[settings][types][]"
                value="<?= $type; ?>"
              > <?= $type; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="item flex-half">
          <small>Select all the post type that should render the following fields</small>
        </div>
      </div>
      <!-- /PostTypes -->
      <hr>
      <!-- CustomFields -->
      <div class="row" ng-repeat="field in data.fields track by $index">
        <div class="item flex-full">
          <div class="mb-sm">
            <label for="">Field name</label>
            <input
              class="w-100"
              type="text"
              ng-model="data.fields[$index].name"
              name="<?= $this->settingsMetaKey; ?>[fields][{{ $index }}][name]"
              placeholder="Field name"
              ng-change="data.fields[$index].key = data.fields[$index].name.toLowerCase().split(' ').join('_')"
            >
          </div>
          <div class="mb-sm">
            <label for="">Field key</label>
            <input class="w-100" type="text" ng-model="data.fields[$index].key" name="<?= $this->settingsMetaKey; ?>[fields][{{ $index }}][key]" placeholder="Field key">
          </div>
          <div class="mb-sm">
            <label for="">Field type</label>
            <select class="w-100" name="<?= $this->settingsMetaKey; ?>[fields][{{ $index }}][type]" ng-model="data.fields[$index].type">
              <?php foreach (CustomField::$types as $type): ?>
                <option value="<?= $type; ?>" selected><?= $type; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-sm" ng-if="data.fields[$index].type === 'select'">
            <label for="">One option per line:</label>
            <textarea class="w-100" name="<?= $this->settingsMetaKey; ?>[fields][{{ $index }}][options]" ng-model="data.fields[$index].options"></textarea>
          </div>
          <div class="mb-sm" ng-if="data.fields[$index].type === 'route'">
            <label for="">Google api key:</label>
            <input class="w-100" type="text" ng-model="data.fields[$index].options" name="<?= $this->settingsMetaKey; ?>[fields][{{ $index }}][options]" placeholder="Google api key">
          </div>
        </div>
        <div class="item flex-full">
          <input type="checkbox" value="true" ng-checked="data.fields[$index].show_in_rest" name="<?= $this->settingsMetaKey; ?>[fields][{{ $index }}][show_in_rest]"> Add to rest api <br>
          <input type="checkbox" value="true" ng-checked="data.fields[$index].required" name="<?= $this->settingsMetaKey; ?>[fields][{{ $index }}][required]"> Required
        </div>
        <div class="item flex-quarter text-center">
          <a ng-click="data.fields.splice($index, 1)" href="#">
            <span class="dashicons dashicons-trash"></span>
          </a>
        </div>
      </div>
      <!-- /CustomFields -->
      <hr>
      <a ng-click="data.fields.push({})" class="button button-primary add-field">Add field</a>
    </div>
  <?php }

  /**
   * @param Integer $postId
   *
   * @return void
   */
  public function handlePostTypeSave($postId)
  {
    $nonce = @$_POST["{$this->settingsMetaKey}_nonce"];
    $data = @$_POST[$this->settingsMetaKey];

    if (
      empty($data) || // Our desired update data does not exists
      !wp_verify_nonce($nonce, $this->settingsMetaKey) || // Verify nonce
      (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || // Check for autosave
      !current_user_can('edit_' . ($_POST['post_type'] === 'page' ? 'page' : 'post'), $postId) // Permissions
      // TODO: Add server side validation
    ) {
      return $postId;
    }

    update_post_meta($postId, $this->settingsMetaKey, $data);
  }

  /**
   * @return void
   */
  public function registerScriptsAndStyles()
  {
    wp_enqueue_script('angular-one-cdn', 'https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.7.5/angular.min.js');
  }

  /**
   * @return void
   */
  public function configureCustomFieldsGroups()
  {
    /** @link https://developer.wordpress.org/reference/functions/get_posts/ */
    $fieldGroups = get_posts(array(
      'numberposts' => -1,
      'post_type' => $this->customFieldsGroupPostType
    ));

    foreach ($fieldGroups as $group):
      $settings = get_post_meta($group->ID, $this->settingsMetaKey, true);
      $fields = !empty($settings['fields']) ? $settings['fields'] : array();

      // Force hide custom fields meta box
      if (!empty($settings['settings']['types'])):
        add_action('admin_menu', function() use ($settings) {
          foreach ($settings['settings']['types'] as $type):
            remove_meta_box('postcustom', $type, 'normal');
          endforeach;
        });
      endif;

      // Register metas
      $fields = array_map(function($field) use ($settings) {
        $type = new CustomField($field);

        if (!empty($settings['settings']['types'])):
          $type->register($settings['settings']['types']);
        endif;

        return $type;
      }, $fields);

      // Setting metaboxes
      add_action('add_meta_boxes', function($currentPostType) use ($settings, $group, $fields) {
        if (in_array($currentPostType, $settings['settings']['types'])):
          add_meta_box($group->post_name, $group->post_title, function($post) use ($group, $settings, $fields) {
            wp_nonce_field($group->post_name, "{$group->post_name}_nonce");
            $meta = get_post_meta($post->ID);

            foreach ($fields as $field):
              $field->setValue(!empty($meta[$field->key]) ? $meta[$field->key][0] : null);
              $field->render();
            endforeach;
          });
        endif;
      });

      // Save post handling
      add_action('save_post', function($postId) use ($group, $settings) {
        $nonce = @$_POST["{$group->post_name}_nonce"];

        $fieldKeys = array_map(function($field) {
          return $field['key'];
        }, $settings['fields']);

        if (
          // TODO: Add server side validation
          !wp_verify_nonce($nonce, $group->post_name) || // Verify nonce
          (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) // Check for autosave
        ) {
          return $postId;
        }

        foreach ($_POST as $key => $value):
          if (in_array($key, $fieldKeys)):
            update_post_meta($postId, $key, $value);
          endif;
        endforeach;
      });
    endforeach;
  }
}
