<?php

class MainDisablingOptions
{
  private $options;

  public function __construct()
  {
    $this->options = get_option('main_disabling_options');

    add_action('admin_menu', array($this, 'addPluginPage'));
    add_action('admin_init', array($this, 'initPage'));
    add_action('init', array($this, 'doPluginOptions'));
  }

  public function addPluginPage()
  {
    // This page will be under "Settings"
    add_options_page(
      'Settings Admin',
      'Main disabling settings',
      'manage_options',
      'mdo-settings-page',
      array($this, 'createAdminPage')
    );
  }

  public function createAdminPage() { ?>
    <div class="wrap">
      <h1>Main disabling options</h1>
      <form method="post" action="options.php">
        <?php settings_fields('mdo_group'); ?>
        <?php do_settings_sections('mdo-settings-page'); ?>
        <?php submit_button(); ?>
      </form>
    </div>
  <?php }

  /**
  * Register and add settings
  */
  public function initPage()
  {
    register_setting(
      'mdo_group', // Option group
      'main_disabling_options', // Option name
      array($this, 'sanitize') // Sanitize
    );

    add_settings_section(
      'mdo_section_id', // ID
      'Main disabling options', // Title
      function() {
        print('Disable the following types');
      }, // Callback
      'mdo-settings-page' // Page
    );

    add_settings_field(
      'disable_comments',
      'Disable comments',
      array($this, 'renderDisableCommentsCheckBox'),
      'mdo-settings-page',
      'mdo_section_id'
    );

    add_settings_field(
      'disable_pages',
      'Disable pages',
      array($this, 'renderDisablePagesCheckBox'),
      'mdo-settings-page',
      'mdo_section_id'
    );

    add_settings_field(
      'disable_posts',
      'Disable posts',
      array($this, 'renderDisablePostsCheckBox'),
      'mdo-settings-page',
      'mdo_section_id'
    );
  }

  public function sanitize($input)
  {
    $newInput = array();

    foreach ($input as $key => $value):
      $newInput[$key] = absint($value);
    endforeach;

    return $newInput;
  }

  public function renderDisableCommentsCheckBox()
  {
    $html = '<input type="checkbox" id="checkbox_comments" name="main_disabling_options[disable_comments]" value="1"' . checked(1, @$this->options['disable_comments'], false ) . '/>';
    $html .= '<label for="checkbox_comments">Disable comments</label>';
    echo $html;
  }

  public function renderDisablePagesCheckBox()
  {
    $html = '<input type="checkbox" id="checkbox_pages" name="main_disabling_options[disable_pages]" value="1"' . checked(1, @$this->options['disable_pages'], false ) . '/>';
    $html .= '<label for="checkbox_pages">Disable pages</label>';
    echo $html;
  }

  public function renderDisablePostsCheckBox()
  {
    $html = '<input type="checkbox" id="checkbox_posts" name="main_disabling_options[disable_posts]" value="1"' . checked(1, @$this->options['disable_posts'], false ) . '/>';
    $html .= '<label for="checkbox_posts">Disable posts</label>';
    echo $html;
  }

  public function doPluginOptions()
  {
    if (@$this->options['disable_comments']):
      $this->disableComments();
    endif;

    if (@$this->options['disable_pages']):
      $this->disablePages();
    endif;

    if (@$this->options['disable_posts']):
      $this->disablePosts();
    endif;
  }

  public function disableComments()
  {
    add_action('admin_init', function () {
      // Redirect any user trying to access comments page
      global $pagenow;

      if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
      }

      // Remove comments metabox from dashboard
      remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

      // Disable support for comments and trackbacks in post types
      foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
          remove_post_type_support($post_type, 'comments');
          remove_post_type_support($post_type, 'trackbacks');
        }
      }
    });

    // Close comments on the front-end
    add_filter('comments_open', '__return_false', 20, 2);
    add_filter('pings_open', '__return_false', 20, 2);

    // Hide existing comments
    add_filter('comments_array', '__return_empty_array', 10, 2);

    // Remove comments page in menu
    add_action('admin_menu', function () {
      remove_menu_page('edit-comments.php');
    });

    // Remove comments links from admin bar
    add_action('init', function () {
      if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
      }
    });
  }

  public function disablePages()
  {
    add_action('admin_menu', function() {
      remove_menu_page('edit.php?post_type=page');
    });

    add_action('wp_before_admin_bar_render', function() {
      global $wp_admin_bar;
      $wp_admin_bar->remove_menu('new-page');
    });

    add_action('wp_dashboard_setup', function() {
      global $wp_meta_boxes;
      unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
      unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    });
  }

  public function disablePosts()
  {
    add_action('admin_menu', function() {
      remove_menu_page('edit.php');
    });

    add_action('wp_before_admin_bar_render', function() {
      global $wp_admin_bar;
      $wp_admin_bar->remove_menu('new-post');
    });

    add_action('wp_dashboard_setup', function() {
      global $wp_meta_boxes;
      unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
      unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    });
  }
}
