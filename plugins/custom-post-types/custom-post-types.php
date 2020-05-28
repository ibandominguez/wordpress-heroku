<?php
/*
Plugin Name: Custom post types
Plugin URI: https://www.andreadegiovine.it/download/custom-post-types/?utm_source=wordpress_org&utm_medium=plugin_link&utm_campaign=custom_post_types
Description: Create / manage custom post types, custom taxonomies, custom fields and custom templates easily, directly from the WordPress dashboard without writing code.
Author: Andrea De Giovine
Author URI: https://www.andreadegiovine.it/?utm_source=wordpress_org&utm_medium=plugin_details&utm_campaign=custom_post_types
Text Domain: custom-post-types
Domain Path: /languages/
Version: 2.1.1
*/

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Invalid request.' );
}

if ( ! class_exists( 'adg_custom_post_types' ) ) {
    class adg_custom_post_types {
        public $cpt_ui_name = 'manage_cpt';
        public $priority = PHP_INT_MAX;
        public $theme_template_compatibility = array();

        public function __construct(){
            register_activation_hook( __FILE__, array( $this, 'plugin_activate' ) );

            // Init template compatibility for custom templates
            add_filter('add_theme_compatibility_template', array( $this, 'update_theme_compatibility_template') );
            add_filter('get_field_from_shortcode', array( $this, 'update_shortcode_field_view'), 10, 3 );
            add_filter('form_edit_field_types', array( $this, 'update_form_edit_field_types') );
            add_action('view_field_types', array( $this, 'update_form_view_field_types'), 10, 5 );
            add_action('init', array( $this, 'init_pro_version') );
            $this->setup_theme_template_compatibility();

            // Init main plugin code
            add_action( 'init', array( $this, 'init_load_textdomain' ), -1 );
            add_action( 'init', array( $this, 'init_created_cpt' ), 0 );
            add_action( 'init', array( $this, 'init_created_tax' ), 0 );
            add_filter( 'template_include', array( $this, 'init_created_template' ), 0 );
            add_action( 'init', array( $this, 'init_cpt_ui' ), 10 );
            add_action( 'wp', array( $this, 'force_404_ui_cpt' ), $this->priority );
            add_action( 'init', array( $this, 'init_tax_ui' ), 10 );
            add_action( 'init', array( $this, 'init_field_ui' ), 10 );
            add_action( 'init', array( $this, 'init_template_ui' ), 10 );

            // Init columns in dashboard list
            add_filter( 'manage_posts_columns', array( $this, 'init_cpt_admin_columns' ), $this->priority );
            add_action( 'manage_posts_custom_column' , array( $this, 'init_render_cpt_admin_columns' ), $this->priority, 2 );

            // Plugin admin menu
            add_action( 'admin_menu', array( $this, 'init_admin_menu' ), $this->priority );
            add_action( 'admin_enqueue_scripts', array( $this, 'init_admin_cpt_ui_assets' ), $this->priority );

            // UI metaboxes
            add_action( 'add_meta_boxes', array( $this, 'init_cpt_ui_metabox' ), $this->priority );
            add_action( 'save_post', array( $this, 'init_cpt_ui_save_metabox' ), $this->priority );

            // Edit cpt manager page
            add_action( 'edit_form_top', array( $this, 'init_admin_form_top' ), $this->priority );
            add_action( 'edit_form_after_title', array( $this, 'init_admin_form_sub' ), $this->priority );

            // Other
            add_filter( 'wp_insert_post_data' , array( $this, 'init_cpt_ui_unique_name' ), $this->priority, 2 );
            add_action( 'admin_notices', array( $this, 'init_admin_notices' ), $this->priority);
            add_filter( 'plugin_action_links', array( $this, 'init_admin_action_links' ), $this->priority, 2);
            add_filter( 'post_row_actions', array( $this, 'remove_cpts_action_links'), 10, 2);
            add_action( 'admin_bar_menu', array( $this, 'remove_view_from_admin_bar'), $this->priority );
            add_action( 'wp_before_admin_bar_render', array( $this, 'remove_view_from_admin_bar_2'), $this->priority );
            add_action( 'init', array( $this, 'gutenberg_custom_fields_block') );

            // Init shortcode
            add_action( 'init', array( $this, 'init_plugin_shortcodes') );
        }

        public function init_plugin_shortcodes(){
            if( !is_admin() && !is_rest() ){
                add_shortcode( 'custom-field', array( $this, 'field_shortcode' ) );
                add_shortcode( 'custom-tax', array( $this, 'tax_shortcode' ) );
            }
        }

        public function update_form_view_field_types( $id,$type,$value,$required, $options ){
            // v 2.0.0
            if($type == 'image'){ ?>
<div class="file-uploader-field">
    <input id="<?php echo $id;?>_field" type="text" name="custom_field[<?php echo $id;?>]" value="<?php echo $value;?>"<?php echo $required;?>/>
    <input type="button" class="button-primary" value="<?php _e( 'Select', 'custom-post-types' ); ?>" data-file="<?php echo $id;?>_field" />
</div>
<?php }

            // v 2.0.4
            if($type == 'checkbox'){ ?>
<div class="checkbox-group">
    <?php
                $options = explode(PHP_EOL, $options);
                $value = !empty($value) ? $value : array();
                foreach($options as $option){ ?>
    <label><input type="checkbox" name="custom_field[<?php echo $id;?>][]" value="<?php echo str_replace(array("\r", "\n"), '', $option); ?>"<?php echo (in_array( str_replace(array("\r", "\n"), '', $option), $value ) ? ' checked' : '' ); ?>><?php echo str_replace(array("\r", "\n"), '', $option); ?></label><br>
    <?php } ?>
</div>
<?php }

            // DEBUG: Custom added
            /* NewType */ if ($type === 'route'): ?>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.7.5/angular.min.js"></script>
            <script type="text/javascript">
            var app = angular.module('app', []);
            app.run(function($rootScope) {
              try {
                $rootScope.route = <?php echo !empty($value) ? $value : 'null'; ?> || [];
              } catch (e) {
                $rootScope.route = [];
              }

              $rootScope.openRouteInGmaps = function () {
                var origin = $rootScope.route[0].latitude + ',' + $rootScope.route[0].longitude;
                var destination = $rootScope.route[$rootScope.route.length - 1].latitude + ',' + $rootScope.route[$rootScope.route.length - 1].longitude;
                var points = $rootScope.route.map(function(coord) {
                  return coord.latitude + ',' + coord.longitude;
                }).join('%7C');

                window.open([
                  'https://www.google.com/maps/dir/?api=1',
                  'origin=' + origin,
                  'destination=' + destination,
                  'travelmode=walking',
                  'waypoints=' + points,
                ].join('&'), '_blank')
              }
            });
            </script>
            <div ng-app="app" style="display: none" ng-style="{ display: 'block' }">
              <input id="<?php echo $id; ?>_field" type="hidden" name="custom_field[<?php echo $id; ?>]" value="{{ route }}" <?php echo $required; ?> />

              <div style="margin-bottom: 10px">
                <div style="display: flex; margin-bottom: 2.5px" ng-repeat="coordinate in route track by $index">
                  <input required min="-90" max="90" step="0.0000001" placeholder="Format: 28.1329271" type="number" ng-model="coordinate.latitude">
                  <input onchange="checkForEmbed()" required min="-180" max="180" step="0.0000001" placeholder="Format: -15.4428294" type="number" ng-model="coordinate.longitude">
                  <span class="button button-warning" ng-click="route.splice($index, 1)">remove</span>
                </div>
                <span class="button button-primary" ng-click="route.push({ latitude: '', longitude: '' })">+ Add coordinate</span>
              </div>

              <span ng-show="route.length > 2" ng-click="openRouteInGmaps()" class="button">Open in google maps (walking mode)</span>
            </div>
            <?php endif; /* EndNewType */
            // DEBUG: Custom added
        }

        public function update_form_edit_field_types( $types ){
            // v 2.0.0
            $types['image'] = array('label' => __( 'IMAGE', 'custom-post-types' ));

            // v 2.0.4
            $types['checkbox'] = array(
                'label' => __('CHECKBOX', 'custom-post-types'),
                'options' => function ($id, $options = null) { ?>
                  <label><?php _e( 'Field options', 'custom-post-types' ); ?>
                      <textarea class="field-extra-options" name="field_settings[fields][<?php echo $id;?>][options]" placeholder="<?php _e( 'Field options, one per row', 'custom-post-types' ); ?>"><?php echo $options;?></textarea>
                  </label>
                <?php }
            );

            // DEBUG: Custom added
            $types['route'] = array('label' => __( 'ROUTE', 'custom-post-types' ));
            // DEBUG: Custom added

            return $types;
        }

        public function update_shortcode_field_view( $value, $field_type, $id ){
            // v 2.0.0
            if($field_type == 'image'){
                $value = '<img src="' . $value . '" class="field-'.$id.'">';
            }

            // v 2.0.4
            if($field_type == 'checkbox'){
                $value = '<span class="field-'.$id.'">'.implode(', ', $value).'</span>';
            }

            // DEBUG: Custom added
            if ($field_type === 'route') {
              $value = '<span class="field-'.$id.'">'.implode(', ', $value).'</span>';
            }
            // DEBUG: Custom added

            return $value;
        }

        public function update_theme_compatibility_template( $themes ){
            // v 1.3.5
            $themes['nisarg'] = array(
                'single_template' => get_template_directory() . '/single.php',
                'replace_type' => 'preg',
                'replace' => array("#while(.+)endwhile;#s"),
                'after' => " echo '</main>'; ",
            );

            // v 2.0.4
            $themes['twentytwenty'] = array(
                'single_template' => get_template_directory() . '/singular.php',
                'replace_type' => 'string',
                'replace' => "get_template_part( 'template-parts/content', get_post_type() )",
            );

            // v 2.0.5
            $themes['secretum'] = array(
                'single_template' => get_template_directory() . '/single.php',
                'replace_type' => 'string',
                'replace' => "get_template_part( 'template-parts/post/content', get_post_format() )",
            );
            $themes['qaengine'] = array(
                'single_template' => get_template_directory() . '/framework/templates/single.php',
                'replace_type' => 'string',
                'replace' => 'echo $the_content',
            );

            // v 2.0.6
            $themes['__x__'] = array(
                'single_template' => get_template_directory() . '/framework/views/ethos/wp-single.php',
                'replace_type' => 'preg',
                'replace' => array("#while(.+)endwhile;#s"),
            );

            // v 2.0.8
            $themes['enfold'] = array(
                'single_template' => get_template_directory() . '/single.php',
                'replace_type' => 'string',
                'replace' => "get_template_part( 'includes/loop', 'index' )",
            );

            // v 2.0.9
            $themes['boss'] = array(
                'single_template' => get_template_directory() . '/single.php',
                'replace_type' => 'string',
                'replace' => "get_template_part( 'content', get_post_format() )",
            );

            // v 2.1.1
            $themes['flatsome'] = array(
                'single_template' => get_template_directory() . '/single.php',
                'replace_type' => 'string',
                'replace' => "get_template_part( 'template-parts/posts/layout', get_theme_mod('blog_post_layout','right-sidebar') )",
            );


            return $themes;
        }

        public function setup_theme_template_compatibility(){
            $theme_template_compatibility = array(
                'oceanwp' => array(
                    'single_template' => get_template_directory() . '/singular.php',
                    'replace_type' => 'preg',
                    'replace' => array("#while(.+)endwhile;#s"),
                ),
                'blocksy' => array(
                    'single_template' => get_template_directory() . '/single.php',
                    'replace_type' => 'string',
                    'replace' => "get_template_part( 'template-parts/content', get_post_type() )",
                ),
                'astra' => array(
                    'single_template' => get_template_directory() . '/single.php',
                    'replace_type' => 'string',
                    'replace' => "astra_content_loop()",
                ),
                'envo-magazine' => array(
                    'single_template' => get_template_directory() . '/single.php',
                    'replace_type' => 'string',
                    'replace' => "get_template_part( 'content', 'single' )",
                ),
                'default' => array(
                    'single_template' => get_template_directory() . '/single.php',
                    'replace_type' => 'preg',
                    'replace' => array("#while(.+)endwhile;#s", "#while ((.+)) {([^}]+)#s"),
                ),
            );
            $this->theme_template_compatibility = apply_filters('add_theme_compatibility_template',$theme_template_compatibility);
        }

        public function is_pro_version_active(){
            $return = false;
            $pro_version = in_array( 'custom-post-types-pro/custom-post-types-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
            if($pro_version){
                $return = true;
            }
            return $return;
        }

        public function init_pro_version(){
            if( !$this->is_pro_version_active() ){
                add_action('tools_export_page', array($this, 'go_pro_content'));
                add_action('tools_import_page', array($this, 'go_pro_content'));
            }
        }


        public function return_pro_button(){
            if( !$this->is_pro_version_active() ){
                return '<a class="button button-primary button-hero button-icon pro-icon" href="https://www.andreadegiovine.it/webmaster/custom-post-types-pro?utm_source=tools_plugin_page&amp;utm_medium=plugin_page&amp;utm_campaign=custom_post_types" target="_blank">'.__('Become PRO', 'custom-post-types' ).' - 12.20€ <small><del>99€</del></small></a>';
            }
        }

        public function go_pro_content(){
            $output = '<div class="cpt-pro">';
            $output .= __('<h2>Become PRO</h2><p><strong>PRO subscription</strong> of the Custom post types plugin for WordPress<br>Here\'s what the subscription includes:</p>
            <ul>
            <li>Unlimited <strong>PRO updates</strong></li>
            <li><strong>PRO fields</strong> types</li>
            <li><strong>Export</strong> all settings</li>
            <li><strong>Import</strong> all settings</li>
            <li><strong>PRO support</strong> priority</li>
            </ul>
            <p>The subscription has a duration of <strong>12 months</strong>.<br>The subscription will be renewed at the <strong>same price</strong>.</p>', 'custom-post-types');

            $output .= $this->return_pro_button();

            $output .= '</div>';
            echo $output;
        }

        public function return_fields_list_block(){
            $post = '';
            $template_settings = '';
            $template_used_by = '';

            if(isset($_GET['post'])){
                $post = get_post($_GET['post']);
                $template_settings = isset(get_post_meta( $post->ID, '_template_settings_meta')[0]) ? get_post_meta( $post->ID, '_template_settings_meta')[0] : array();
                $template_used_by = isset($template_settings['field_used_by']) ? $template_settings['field_used_by'] : '';
            }

            $return_fields_list = array(
                'title' => __( 'Post title', 'custom-post-types' ),
                'content' => __( 'Post content', 'custom-post-types' ),
                'excerpt' => __( 'Post excerpt', 'custom-post-types' ),
                'image' => __( 'Post image', 'custom-post-types' ),
                'date' => __( 'Post date', 'custom-post-types' ),
                'author' => __( 'Post author', 'custom-post-types' ),
            );

            if(!empty($template_used_by)){

                $all_custom_metaboxes = get_posts( array( 'posts_per_page' => -1, 'post_type' => $this->cpt_ui_name . '_field' ) );
                foreach($all_custom_metaboxes as $metaboxes){
                    $field_settings = get_post_meta( $metaboxes->ID, '_field_settings_meta')[0];
                    $fields = isset($field_settings['fields']) ? $field_settings['fields'] : array();
                    if(isset($field_settings['field_post_types']) && in_array($template_used_by, $field_settings['field_post_types'])){
                        foreach($fields as $id => $field){ if($field['type'] !== '0'){
                            $return_fields_list[$id] = $field['name'];
                        }
                                                         }
                    }
                }
            }
            return $return_fields_list;

        }

        public function return_taxs_list_block(){
            $post = '';
            $template_settings = '';
            $template_used_by = '';

            if(isset($_GET['post'])){
                $post = get_post($_GET['post']);
                $template_settings = isset(get_post_meta( $post->ID, '_template_settings_meta')[0]) ? get_post_meta( $post->ID, '_template_settings_meta')[0] : array();
                $template_used_by = isset($template_settings['field_used_by']) ? $template_settings['field_used_by'] : '';
            }

            $return_taxs_list = array(
                'category' => __( 'Categories', 'custom-post-types' ),
                'post_tag' => __( 'Tags', 'custom-post-types' ),
            );

            if(!empty($template_used_by)){

                $taxs = get_taxonomies( array('_builtin' => false), 'objects');
                foreach ( $taxs  as $tax ) {
                    $return_taxs_list[$tax->name] = $tax->label;
                }

            }
            return $return_taxs_list;

        }

        public function gutenberg_custom_fields_block() {
            $post = '';
            $template_settings = '';
            $template_used_by = '';
            $post_type = '';

            if(isset($_GET['post']) && get_post($_GET['post'])){
                $post = get_post($_GET['post']);
                $template_settings = isset(get_post_meta( $post->ID, '_template_settings_meta')[0]) ? get_post_meta( $post->ID, '_template_settings_meta')[0] : array();
                $template_used_by = isset($template_settings['field_used_by']) ? $template_settings['field_used_by'] : '';
                $post_type = $post->post_type;
            }

            if($this->cpt_ui_name . '_template' === $post_type){

                wp_register_script(
                    'cpt_custom_fields_block',
                    plugins_url( 'assets/block.js', __FILE__ ),
                    array( 'wp-blocks', 'wp-element', 'wp-data' )
                );

                wp_register_script(
                    'cpt_custom_taxs_block',
                    plugins_url( 'assets/block-2.js', __FILE__ ),
                    array( 'wp-blocks', 'wp-element', 'wp-data' )
                );

                $translation_array = array(
                    'name' => _x( 'Custom field', 'block', 'custom-post-types' ),
                    'select' => _x( 'Field', 'block', 'custom-post-types' ),
                    'not_used' => _x( 'To continue set "Used by" from "Document > Template settings" tab and refresh this page', 'block', 'custom-post-types' ),
                    'keywords' => array(
                        _x( 'field', 'block', 'custom-post-types' ),
                        _x( 'fields', 'block', 'custom-post-types' ),
                        _x( 'custom field', 'block', 'custom-post-types' ),
                        _x( 'custom fields', 'block', 'custom-post-types' ),
                    ),
                    'used_by' => $template_used_by,
                    'fields' => $this->return_fields_list_block(),
                );
                wp_localize_script( 'cpt_custom_fields_block', 'cpt_block', $translation_array );

                $translation_array_2 = array(
                    'name' => _x( 'List taxonomies', 'block', 'custom-post-types' ),
                    'select' => _x( 'Taxonomy', 'block', 'custom-post-types' ),
                    'not_used' => _x( 'To continue set "Used by" from "Document > Template settings" tab and refresh this page', 'block', 'custom-post-types' ),
                    'keywords' => array(
                        _x( 'tax', 'block', 'custom-post-types' ),
                        _x( 'taxonomy', 'block', 'custom-post-types' ),
                        _x( 'taxonomies', 'block', 'custom-post-types' ),
                        _x( 'category', 'block', 'custom-post-types' ),
                    ),
                    'used_by' => $template_used_by,
                    'taxs' => $this->return_taxs_list_block(),
                );
                wp_localize_script( 'cpt_custom_taxs_block', 'cpt_block_2', $translation_array_2 );

                register_block_type( 'custom-post-types/custom-field', array(
                    'editor_script' => 'cpt_custom_fields_block',
                ) );

                register_block_type( 'custom-post-types/custom-tax', array(
                    'editor_script' => 'cpt_custom_taxs_block',
                ) );

            }

        }

        public function init_load_textdomain() {
            load_plugin_textdomain( 'custom-post-types', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

        public function plugin_activate() {
            $upload = wp_upload_dir();
            $upload_dir = $upload['basedir'];
            $upload_dir = $upload_dir . '/custom-templates';
            if (! is_dir($upload_dir)) {
                mkdir( $upload_dir, 0700 );
            }
        }

        public function remove_view_from_admin_bar($wp_admin_bar){
            global $post;

            $cpts_to_remove_view = array(
                $this->cpt_ui_name . '_template',
                $this->cpt_ui_name . '_field',
                $this->cpt_ui_name . '_tax',
                $this->cpt_ui_name,
            );
            if ( ( is_single() && in_array($post->post_type, $cpts_to_remove_view) ) || ( isset($_GET['post']) && get_post($_GET['post']) && in_array(get_post($_GET['post'])->post_type, $cpts_to_remove_view) ) ) {
                $wp_admin_bar->remove_node('view');
            }
        }

        public function remove_view_from_admin_bar_2(){
            global $post;
            global $wp_admin_bar;

            $cpts_to_remove_view = array(
                $this->cpt_ui_name . '_template',
                $this->cpt_ui_name . '_field',
                $this->cpt_ui_name . '_tax',
                $this->cpt_ui_name,
            );
            if ( ( is_single() && in_array($post->post_type, $cpts_to_remove_view) ) || ( isset($_GET['post']) && get_post($_GET['post']) && in_array(get_post($_GET['post'])->post_type, $cpts_to_remove_view) ) ) {
                $wp_admin_bar->remove_node('view');
            }
        }

        public function remove_cpts_action_links($actions, $post){
            $cpts_to_remove_view = array(
                $this->cpt_ui_name . '_template',
                $this->cpt_ui_name . '_field',
                $this->cpt_ui_name . '_tax',
                $this->cpt_ui_name,
            );

            if (in_array($post->post_type, $cpts_to_remove_view)) {
                unset($actions['view']);
            }
            return $actions;
        }

        public function force_404_ui_cpt() {

            global $post;

            $cpts_to_force_404 = array(
                $this->cpt_ui_name . '_template',
                $this->cpt_ui_name . '_field',
                $this->cpt_ui_name . '_tax',
                $this->cpt_ui_name,
            );

            if ( is_singular( $cpts_to_force_404 ) ) {
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
            }

        }

        public function init_cpt_ui(){
            $labels = array(
                'name'               => _x( 'Custom post types', 'Dashboard menu', 'custom-post-types' ),
                'singular_name'      => __( 'Post type', 'custom-post-types' ),
                'menu_name'          => __( 'Post types', 'custom-post-types' ),
                'name_admin_bar'     => __( 'Post type', 'custom-post-types' ),
                'add_new'            => __( 'Add post type', 'custom-post-types' ),
                'add_new_item'       => __( 'Add new post type', 'custom-post-types' ),
                'new_item'           => __( 'New post type', 'custom-post-types' ),
                'edit_item'          => __( 'Edit post type', 'custom-post-types' ),
                'view_item'          => __( 'View post type', 'custom-post-types' ),
                'all_items'          => _x( 'Custom post types', 'Dashboard menu', 'custom-post-types' ),
                'search_items'       => __( 'Search post type', 'custom-post-types' ),
                'not_found'          => __( 'No post type available.', 'custom-post-types' ),
                'not_found_in_trash' => __( 'No post type in the trash.', 'custom-post-types' )
            );

            $args = array(
                'labels'             => $labels,
                'description'        => __( 'Create and manage custom post types.', 'custom-post-types' ),
                'public'             => false,
                'publicly_queryable' => false,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'query_var'          => false,
                'rewrite'            => false,
                'capabilities' => array(
                    'edit_post'          => 'administrator',
                    'read_post'          => 'administrator',
                    'delete_post'        => 'administrator',
                    'edit_posts'         => 'administrator',
                    'edit_others_posts'  => 'administrator',
                    'delete_posts'       => 'administrator',
                    'publish_posts'      => 'administrator',
                    'read_private_posts' => 'administrator'
                ),
                'has_archive'        => false,
                'hierarchical'       => false,
                'menu_position'      => null,
                'supports'           => array( 'title' ),
                'menu_icon'          => 'dashicons-index-card',
                'can_export'         => false,
            );
            register_post_type( $this->cpt_ui_name, $args );
        }

        public function init_tax_ui(){
            $labels = array(
                'name'               => __( 'Custom taxonomies', 'custom-post-types' ),
                'singular_name'      => __( 'Taxonomy', 'custom-post-types' ),
                'menu_name'          => __( 'Taxonomy', 'custom-post-types' ),
                'name_admin_bar'     => __( 'Taxonomy', 'custom-post-types' ),
                'add_new'            => __( 'Add taxonomy', 'custom-post-types' ),
                'add_new_item'       => __( 'Add new taxonomy', 'custom-post-types' ),
                'new_item'           => __( 'New taxonomy', 'custom-post-types' ),
                'edit_item'          => __( 'Edit taxonomy', 'custom-post-types' ),
                'view_item'          => __( 'View taxonomy', 'custom-post-types' ),
                'all_items'          => __( 'Custom taxonomies', 'custom-post-types' ),
                'search_items'       => __( 'Search taxonomy', 'custom-post-types' ),
                'not_found'          => __( 'No taxonomy available.', 'custom-post-types' ),
                'not_found_in_trash' => __( 'No taxonomy in the trash.', 'custom-post-types' )
            );

            $args = array(
                'labels'             => $labels,
                'description'        => __( 'Create and manage custom taxonomies.', 'custom-post-types' ),
                'public'             => false,
                'publicly_queryable' => false,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'query_var'          => false,
                'rewrite'            => false,
                'capabilities' => array(
                    'edit_post'          => 'administrator',
                    'read_post'          => 'administrator',
                    'delete_post'        => 'administrator',
                    'edit_posts'         => 'administrator',
                    'edit_others_posts'  => 'administrator',
                    'delete_posts'       => 'administrator',
                    'publish_posts'      => 'administrator',
                    'read_private_posts' => 'administrator'
                ),
                'has_archive'        => false,
                'hierarchical'       => false,
                'menu_position'      => null,
                'supports'           => array( 'title' ),
                'menu_icon'          => 'dashicons-index-card',
                'show_in_menu'       => 'edit.php?post_type=' . $this->cpt_ui_name,
                'can_export'         => false,
            );
            register_post_type( $this->cpt_ui_name . '_tax', $args );
        }

        public function init_field_ui(){
            $labels = array(
                'name'               => __( 'Custom fields', 'custom-post-types' ),
                'singular_name'      => __( 'Field', 'custom-post-types' ),
                'menu_name'          => __( 'Field', 'custom-post-types' ),
                'name_admin_bar'     => __( 'Field', 'custom-post-types' ),
                'add_new'            => __( 'Add field', 'custom-post-types' ),
                'add_new_item'       => __( 'Add new field', 'custom-post-types' ),
                'new_item'           => __( 'New field', 'custom-post-types' ),
                'edit_item'          => __( 'Edit field', 'custom-post-types' ),
                'view_item'          => __( 'View field', 'custom-post-types' ),
                'all_items'          => __( 'Custom fields', 'custom-post-types' ),
                'search_items'       => __( 'Search Field', 'custom-post-types' ),
                'not_found'          => __( 'No field available.', 'custom-post-types' ),
                'not_found_in_trash' => __( 'No field in the trash.', 'custom-post-types' )
            );

            $args = array(
                'labels'             => $labels,
                'description'        => __( 'Create and manage custom fields.', 'custom-post-types' ),
                'public'             => false,
                'publicly_queryable' => false,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'query_var'          => false,
                'rewrite'            => false,
                'capabilities' => array(
                    'edit_post'          => 'administrator',
                    'read_post'          => 'administrator',
                    'delete_post'        => 'administrator',
                    'edit_posts'         => 'administrator',
                    'edit_others_posts'  => 'administrator',
                    'delete_posts'       => 'administrator',
                    'publish_posts'      => 'administrator',
                    'read_private_posts' => 'administrator'
                ),
                'has_archive'        => false,
                'hierarchical'       => false,
                'menu_position'      => null,
                'supports'           => array( 'title' ),
                'menu_icon'          => 'dashicons-index-card',
                'show_in_menu'       => 'edit.php?post_type=' . $this->cpt_ui_name,
                'can_export'         => false,
            );
            register_post_type( $this->cpt_ui_name . '_field', $args );
        }

        public function init_template_ui(){
            $labels = array(
                'name'               => __( 'Custom templates', 'custom-post-types' ),
                'singular_name'      => __( 'Template', 'custom-post-types' ),
                'menu_name'          => __( 'Template', 'custom-post-types' ),
                'name_admin_bar'     => __( 'Template', 'custom-post-types' ),
                'add_new'            => __( 'Add template', 'custom-post-types' ),
                'add_new_item'       => __( 'Add new template', 'custom-post-types' ),
                'new_item'           => __( 'New template', 'custom-post-types' ),
                'edit_item'          => __( 'Edit template', 'custom-post-types' ),
                'view_item'          => __( 'View template', 'custom-post-types' ),
                'all_items'          => __( 'Custom template', 'custom-post-types' ),
                'search_items'       => __( 'Search template', 'custom-post-types' ),
                'not_found'          => __( 'No template available.', 'custom-post-types' ),
                'not_found_in_trash' => __( 'No template in the trash.', 'custom-post-types' )
            );

            $args = array(
                'labels'             => $labels,
                'description'        => __( 'Create and manage custom templates.', 'custom-post-types' ),
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'show_in_rest'       => true,
                'query_var'          => false,
                'rewrite'            => false,
                'capabilities' => array(
                    'edit_post'          => 'update_core',
                    'read_post'          => 'update_core',
                    'delete_post'        => 'update_core',
                    'edit_posts'         => 'update_core',
                    'edit_others_posts'  => 'update_core',
                    'delete_posts'       => 'update_core',
                    'publish_posts'      => 'update_core',
                    'read_private_posts' => 'update_core'
                ),
                'has_archive'        => false,
                'hierarchical'       => false,
                'menu_position'      => null,
                'supports'           => array( 'title', 'editor' ),
                'menu_icon'          => 'dashicons-index-card',
                'show_in_menu'       => 'edit.php?post_type=' . $this->cpt_ui_name,
                'can_export'         => false,
            );
            register_post_type( $this->cpt_ui_name . '_template', $args );



            $this->copy_theme_single_template();
        }

        public function init_admin_menu(){
            remove_submenu_page('edit.php?post_type=' . $this->cpt_ui_name,'post-new.php?post_type=' . $this->cpt_ui_name);

            //create new top-level menu
            add_submenu_page( 'edit.php?post_type='.$this->cpt_ui_name , __('Tools & Settings &lsaquo; Custom post types', 'custom-post-types' ), __( 'Tools & Settings', 'custom-post-types' ), 'administrator', 'tools', array($this,'init_tools_page') );
            //call register settings function
            add_action( 'admin_init', array($this,'init_tools_settings') );
        }

        public function init_tools_settings() {
            //register our settings
            register_setting( $this->cpt_ui_name . '_settings', $this->cpt_ui_name . '_settings_tools' );
        }

        public function init_tools_page() {
            include( plugin_dir_path( __FILE__ ) . 'parts/tools.php');
        }

        public function init_admin_cpt_ui_assets() {
            wp_enqueue_style( $this->cpt_ui_name . '_css', plugins_url('assets/style.css', __FILE__) );
            wp_enqueue_script( $this->cpt_ui_name . '_js', plugins_url('assets/script.js', __FILE__), array( 'jquery' ) );
        }

        public function init_cpt_ui_metabox( $post_type ) {
            if ( $post_type == $this->cpt_ui_name ) {
                add_meta_box(
                    'cpt_ui_metabox',
                    __( 'Post type settings', 'custom-post-types' ),
                    array( $this, 'init_cpt_ui_render_metabox' ),
                    $post_type,
                    'normal',
                    'high'
                );
            } elseif ( $post_type == $this->cpt_ui_name . '_tax' ) {
                add_meta_box(
                    'tax_ui_metabox',
                    __( 'Taxonomy settings', 'custom-post-types' ),
                    array( $this, 'init_tax_ui_render_metabox' ),
                    $post_type,
                    'normal',
                    'high'
                );
            } elseif ( $post_type == $this->cpt_ui_name . '_field' ) {
                add_meta_box(
                    'field_ui_metabox',
                    __( 'Field settings', 'custom-post-types' ),
                    array( $this, 'init_field_ui_render_metabox' ),
                    $post_type,
                    'normal',
                    'high'
                );
            } elseif ( $post_type == $this->cpt_ui_name . '_template' ) {
                add_meta_box(
                    'template_ui_metabox',
                    __( 'Template settings', 'custom-post-types' ),
                    array( $this, 'init_template_ui_render_metabox' ),
                    $post_type,
                    'side',
                    'low'
                );
            }

            // Search for custom field box for current post type

            $all_custom_metaboxes = get_posts( array( 'posts_per_page' => -1, 'post_type' => $this->cpt_ui_name . '_field' ) );
            foreach($all_custom_metaboxes as $metaboxes){

                $field_settings = get_post_meta( $metaboxes->ID, '_field_settings_meta');

                if(!isset($field_settings['field_post_types'])){
                    $field_settings = get_post_meta( $metaboxes->ID, '_field_settings_meta', true);
                }

                if(isset($field_settings['field_post_types']) && in_array($post_type, $field_settings['field_post_types'])){

                    $box_id = $metaboxes->ID;
                    $post_type_name = get_post_type_object($post_type)->labels->singular_name;

                    add_meta_box(
                        $box_id . '_metabox',
                        $metaboxes->post_title,
                        array( $this, 'render_custom_metabox' ),
                        $post_type,
                        isset($field_settings['field_position']) && $field_settings['field_position'] == 'side' ? 'side' : 'normal',
                        'high'
                    );

                }
            }

        }

        public function init_cpt_ui_render_metabox( $post ) {
            include( plugin_dir_path( __FILE__ ) . 'parts/edit-cpt.php');
        }

        public function init_tax_ui_render_metabox( $post ) {
            include( plugin_dir_path( __FILE__ ) . 'parts/edit-tax.php');
        }

        public function init_field_ui_render_metabox( $post ) {
            include( plugin_dir_path( __FILE__ ) . 'parts/edit-field.php');
        }

        public function init_template_ui_render_metabox( $post ) {
            include( plugin_dir_path( __FILE__ ) . 'parts/edit-template.php');
        }

        public function render_custom_metabox( $post, $metabox ) {
            include( plugin_dir_path( __FILE__ ) . 'parts/view-field.php');
        }

        public function init_cpt_ui_save_metabox( $post_id ) {
            $post = get_post($post_id);
            $post_type = $post->post_type;
            if ( $post_type == $this->cpt_ui_name ) {
                if ( ! isset( $_POST['cpt_ui_metabox_nonce'] ) ) {
                    return $post_id;
                }
                $nonce = $_POST['cpt_ui_metabox_nonce'];
                if ( ! wp_verify_nonce( $nonce, 'cpt_ui_inner_metabox' ) ) {
                    return $post_id;
                }
                if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                    return $post_id;
                }
                if ( ! current_user_can( 'activate_plugins', $post_id ) ) {
                    return $post_id;
                }
                $mydata = $_POST['cpt_settings'];
                $mydata['cpt_id'] = sanitize_title($mydata['cpt_id']);

                update_post_meta( $post_id, '_cpt_settings_meta', $mydata );

                flush_rewrite_rules();
            } elseif ( $post_type == $this->cpt_ui_name . '_tax' ) {
                if ( ! isset( $_POST['tax_ui_metabox_nonce'] ) ) {
                    return $post_id;
                }
                $nonce = $_POST['tax_ui_metabox_nonce'];
                if ( ! wp_verify_nonce( $nonce, 'tax_ui_inner_metabox' ) ) {
                    return $post_id;
                }
                if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                    return $post_id;
                }
                if ( ! current_user_can( 'activate_plugins', $post_id ) ) {
                    return $post_id;
                }
                $mydata = $_POST['tax_settings'];
                $mydata['tax_id'] = sanitize_title($mydata['tax_id']);

                update_post_meta( $post_id, '_tax_settings_meta', $mydata );

                flush_rewrite_rules();
            } elseif ( $post_type == $this->cpt_ui_name . '_field' ) {
                if ( ! isset( $_POST['field_ui_metabox_nonce'] ) ) {
                    return $post_id;
                }
                $nonce = $_POST['field_ui_metabox_nonce'];
                if ( ! wp_verify_nonce( $nonce, 'field_ui_inner_metabox' ) ) {
                    return $post_id;
                }
                if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                    return $post_id;
                }
                if ( ! current_user_can( 'activate_plugins', $post_id ) ) {
                    return $post_id;
                }
                $mydata = $_POST['field_settings'];
                update_post_meta( $post_id, '_field_settings_meta', $mydata );

                flush_rewrite_rules();
            } elseif ( $post_type == $this->cpt_ui_name . '_template' ) {
                if ( ! isset( $_POST['template_ui_metabox_nonce'] ) ) {
                    return $post_id;
                }
                $nonce = $_POST['template_ui_metabox_nonce'];
                if ( ! wp_verify_nonce( $nonce, 'template_ui_inner_metabox' ) ) {
                    return $post_id;
                }
                if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                    return $post_id;
                }
                if ( ! current_user_can( 'activate_plugins', $post_id ) ) {
                    return $post_id;
                }
                $mydata = $_POST['template_settings'];
                update_post_meta( $post_id, '_template_settings_meta', $mydata );

                flush_rewrite_rules();
            }

            if ( isset( $_POST['custom_metabox_nonce'] ) ) {
                $nonce = $_POST['custom_metabox_nonce'];
                if ( ! wp_verify_nonce( $nonce, 'custom_inner_metabox' ) ) {
                    return $post_id;
                }
                if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                    return $post_id;
                }
                if ( ! current_user_can( 'activate_plugins', $post_id ) ) {
                    return $post_id;
                }
                $mydata = $_POST['custom_field'];
                if(is_array($mydata) && !empty($mydata)){
                    foreach($mydata as $meta => $value){
                        update_post_meta( $post_id, $meta, $value );
                    }
                }
            }
        }

        public function init_created_cpt(){

            $created_post_types = get_posts( array('posts_per_page' => -1, 'post_type' => $this->cpt_ui_name) );

            foreach($created_post_types as $post_type){

                $cpt_settings = get_post_meta( $post_type->ID, '_cpt_settings_meta')[0];

                $cpt_id = isset($cpt_settings['cpt_id']) && !empty($cpt_settings['cpt_id']) ? $cpt_settings['cpt_id'] : 'cpt_' . $post_type->ID;

                $cpt_add_new = isset($cpt_settings['cpt_add_new']) && !empty($cpt_settings['cpt_add_new']) ? $cpt_settings['cpt_add_new'] : sprintf( __( 'Add new %s', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_add_new_item = isset($cpt_settings['cpt_add_new_item']) && !empty($cpt_settings['cpt_add_new_item']) ? $cpt_settings['cpt_add_new_item'] : sprintf( __( 'Add new %s', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_edit_item = isset($cpt_settings['cpt_edit_item']) && !empty($cpt_settings['cpt_edit_item']) ? $cpt_settings['cpt_edit_item'] : sprintf( __( 'Edit %s', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_new_item = isset($cpt_settings['cpt_new_item']) && !empty($cpt_settings['cpt_new_item']) ? $cpt_settings['cpt_new_item'] : sprintf( __( 'Add %s', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_view_item = isset($cpt_settings['cpt_view_item']) && !empty($cpt_settings['cpt_view_item']) ? $cpt_settings['cpt_view_item'] : sprintf( __( 'View %s', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_view_items = isset($cpt_settings['cpt_view_items']) && !empty($cpt_settings['cpt_view_items']) ? $cpt_settings['cpt_view_items'] : sprintf( __( 'View %s', 'custom-post-types' ), $post_type->post_title );
                $cpt_search_items = isset($cpt_settings['cpt_search_items']) && !empty($cpt_settings['cpt_search_items']) ? $cpt_settings['cpt_search_items'] : sprintf( __( 'Search %s', 'custom-post-types' ), $post_type->post_title );
                $cpt_not_found = isset($cpt_settings['cpt_not_found']) && !empty($cpt_settings['cpt_not_found']) ? $cpt_settings['cpt_not_found'] : __( 'No Contents avaiable.', 'custom-post-types' );
                $cpt_not_found_in_trash = isset($cpt_settings['cpt_not_found_in_trash']) && !empty($cpt_settings['cpt_not_found_in_trash']) ? $cpt_settings['cpt_not_found_in_trash'] : __( 'No Contents in the trash.', 'custom-post-types' );
                $cpt_parent_item_colon = isset($cpt_settings['cpt_parent_item_colon']) && !empty($cpt_settings['cpt_parent_item_colon']) ? $cpt_settings['cpt_parent_item_colon'] : sprintf( __( 'Parent %s', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_all_items = isset($cpt_settings['cpt_all_items']) && !empty($cpt_settings['cpt_all_items']) ? $cpt_settings['cpt_all_items'] : sprintf( __( 'View %s', 'custom-post-types' ), $post_type->post_title );
                $cpt_archives = isset($cpt_settings['cpt_archives']) && !empty($cpt_settings['cpt_archives']) ? $cpt_settings['cpt_archives'] : sprintf( __( '%s archives', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_attributes = isset($cpt_settings['cpt_attributes']) && !empty($cpt_settings['cpt_attributes']) ? $cpt_settings['cpt_attributes'] : sprintf( __( '%s attributes', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_insert_into_item = isset($cpt_settings['cpt_insert_into_item']) && !empty($cpt_settings['cpt_insert_into_item']) ? $cpt_settings['cpt_insert_into_item'] : sprintf( __( 'Insert into %s', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_uploaded_to_this_item = isset($cpt_settings['cpt_uploaded_to_this_item']) && !empty($cpt_settings['cpt_uploaded_to_this_item']) ? $cpt_settings['cpt_uploaded_to_this_item'] : sprintf( __( 'Uploaded to this %s', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_featured_image = isset($cpt_settings['cpt_featured_image']) && !empty($cpt_settings['cpt_featured_image']) ? $cpt_settings['cpt_featured_image'] : __( 'Featured image', 'custom-post-types' );
                $cpt_set_featured_image = isset($cpt_settings['cpt_set_featured_image']) && !empty($cpt_settings['cpt_set_featured_image']) ? $cpt_settings['cpt_set_featured_image'] : __( 'Set featured image', 'custom-post-types' );
                $cpt_remove_featured_image = isset($cpt_settings['cpt_remove_featured_image']) && !empty($cpt_settings['cpt_remove_featured_image']) ? $cpt_settings['cpt_remove_featured_image'] : __( 'Remove featured image', 'custom-post-types' );
                $cpt_use_featured_image = isset($cpt_settings['cpt_use_featured_image']) && !empty($cpt_settings['cpt_use_featured_image']) ? $cpt_settings['cpt_use_featured_image'] : __( 'Use as featured image', 'custom-post-types' );
                $cpt_menu_name = isset($cpt_settings['cpt_menu_name']) && !empty($cpt_settings['cpt_menu_name']) ? $cpt_settings['cpt_menu_name'] : __( $post_type->post_title, 'custom-post-types' );
                $cpt_name_admin_bar = isset($cpt_settings['cpt_name_admin_bar']) && !empty($cpt_settings['cpt_name_admin_bar']) ? $cpt_settings['cpt_name_admin_bar'] : __( $cpt_settings['cpt_singular_name'], 'custom-post-types' );
                $cpt_item_published = isset($cpt_settings['cpt_item_published']) && !empty($cpt_settings['cpt_item_published']) ? $cpt_settings['cpt_item_published'] : sprintf( __( '%s published', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_item_published_privately = isset($cpt_settings['cpt_item_published_privately']) && !empty($cpt_settings['cpt_item_published_privately']) ? $cpt_settings['cpt_item_published_privately'] : sprintf( __( '%s published privately', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_item_reverted_to_draft = isset($cpt_settings['cpt_item_reverted_to_draft']) && !empty($cpt_settings['cpt_item_reverted_to_draft']) ? $cpt_settings['cpt_item_reverted_to_draft'] : sprintf( __( '%s reverted to draft', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_item_scheduled = isset($cpt_settings['cpt_item_scheduled']) && !empty($cpt_settings['cpt_item_scheduled']) ? $cpt_settings['cpt_item_scheduled'] : sprintf( __( '%s scheduled', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );
                $cpt_item_updated = isset($cpt_settings['cpt_item_updated']) && !empty($cpt_settings['cpt_item_updated']) ? $cpt_settings['cpt_item_updated'] : sprintf( __( '%s updated', 'custom-post-types' ), $cpt_settings['cpt_singular_name'] );

                $labels = array(
                    'name'               => __( $post_type->post_title, 'custom-post-types' ),
                    'singular_name'      => __( $cpt_settings['cpt_singular_name'], 'custom-post-types' ),
                    'menu_name'          => $cpt_menu_name,
                    'name_admin_bar'     => $cpt_name_admin_bar,
                    'add_new'            => $cpt_add_new,
                    'add_new_item'       => $cpt_add_new_item,
                    'new_item'           => $cpt_new_item,
                    'edit_item'          => $cpt_edit_item,
                    'view_item'          => $cpt_view_item,
                    'view_items'         => $cpt_view_items,
                    'all_items'          => $cpt_all_items,
                    'search_items'       => $cpt_search_items,
                    'parent_item_colon'  => $cpt_parent_item_colon,
                    'archives'           => $cpt_archives,
                    'insert_into_item'   => $cpt_insert_into_item,
                    'uploaded_to_this_item' => $cpt_uploaded_to_this_item,
                    'featured_image'     => $cpt_featured_image,
                    'set_featured_image' => $cpt_set_featured_image,
                    'remove_featured_image' => $cpt_remove_featured_image,
                    'use_featured_image' => $cpt_use_featured_image,
                    'attributes'         => $cpt_attributes,
                    'item_published'     => $cpt_item_published,
                    'item_published_privately' => $cpt_item_published_privately,
                    'item_reverted_to_draft' => $cpt_item_reverted_to_draft,
                    'item_scheduled'     => $cpt_item_scheduled,
                    'item_updated'       => $cpt_item_updated,
                    'not_found'          => $cpt_not_found,
                    'not_found_in_trash' => $cpt_not_found_in_trash
                );

                $supports = array('title','page-attributes');

                if( isset($cpt_settings['cpt_editor']) && $cpt_settings['cpt_editor'] == 1 ){
                    array_push($supports, 'editor');
                }
                if( isset($cpt_settings['cpt_excerpt']) && $cpt_settings['cpt_excerpt'] == 1 ){
                    array_push($supports, 'excerpt');
                }
                if( isset($cpt_settings['cpt_thumbnail']) && $cpt_settings['cpt_thumbnail'] == 1 ){
                    array_push($supports, 'thumbnail');
                }

                $args = array(
                    'labels'             => $labels,
                    'description'        => __( 'Post type created with the "Custom post type" plugin.', 'custom-post-types' ),
                    'public'             => $cpt_settings['cpt_public'] == 0 ? false : true,
                    'publicly_queryable' => $cpt_settings['cpt_public'] == 0 ? false : true,
                    'show_ui'            => true,
                    'show_in_menu'       => true,
                    'show_in_rest'       => $cpt_settings['cpt_public'] == 0 ? false : true,
                    'query_var'          => $cpt_settings['cpt_public'] == 0 ? false : true,
                    'rewrite'            => $cpt_settings['cpt_public'] == 0 ? false : array( 'slug' => sanitize_title($post_type->post_title) ),
                    'capabilities' => array(
                        'edit_post'          => $cpt_settings['cpt_role'] == 0 ? 'edit_posts' : 'update_core',
                        'read_post'          => $cpt_settings['cpt_role'] == 0 ? 'edit_posts' : 'update_core',
                        'delete_post'        => $cpt_settings['cpt_role'] == 0 ? 'edit_posts' : 'update_core',
                        'edit_posts'         => $cpt_settings['cpt_role'] == 0 ? 'edit_posts' : 'update_core',
                        'edit_others_posts'  => $cpt_settings['cpt_role'] == 0 ? 'edit_posts' : 'update_core',
                        'delete_posts'       => $cpt_settings['cpt_role'] == 0 ? 'edit_posts' : 'update_core',
                        'publish_posts'      => $cpt_settings['cpt_role'] == 0 ? 'edit_posts' : 'update_core',
                        'read_private_posts' => $cpt_settings['cpt_role'] == 0 ? 'edit_posts' : 'update_core'
                    ),
                    'has_archive'        => $cpt_settings['cpt_public'] == 0 ? false : true,
                    'hierarchical'       => $cpt_settings['cpt_hierarchical'] == 0 ? false : true,
                    'menu_position'      => null,
                    'supports'           => $supports,
                    'menu_icon'          => $cpt_settings['cpt_icon'] ? $cpt_settings['cpt_icon'] : 'dashicons-tag'
                );
                register_post_type( $cpt_id , $args );

            }

        }

        public function init_created_tax(){

            $created_taxonomies = get_posts( array('posts_per_page' => -1, 'post_type' => $this->cpt_ui_name . '_tax') );

            foreach($created_taxonomies as $taxonomy){

                $tax_settings = get_post_meta( $taxonomy->ID, '_tax_settings_meta')[0];

                $tax_id = isset($tax_settings['tax_id']) && !empty($tax_settings['tax_id']) ? $tax_settings['tax_id'] : 'tax_' . $taxonomy->ID;

                $tax_search_items = isset($tax_settings['tax_search_items']) && !empty($tax_settings['tax_search_items']) ? $tax_settings['tax_search_items'] : sprintf( __( 'Search %s', 'custom-post-types' ), $taxonomy->post_title );
                $tax_all_items = isset($tax_settings['tax_all_items']) && !empty($tax_settings['tax_all_items']) ? $tax_settings['tax_all_items'] : sprintf( __( 'View %s', 'custom-post-types' ), $taxonomy->post_title );
                $tax_parent_item = isset($tax_settings['tax_parent_item']) && !empty($tax_settings['tax_parent_item']) ? $tax_settings['tax_parent_item'] : sprintf( __( 'Parent %s', 'custom-post-types' ), $tax_settings['tax_singular_name'] );
                $tax_parent_item_colon = isset($tax_settings['tax_parent_item_colon']) && !empty($tax_settings['tax_parent_item_colon']) ? $tax_settings['tax_parent_item_colon'] : sprintf( __( 'Parent %s', 'custom-post-types' ), $tax_settings['tax_singular_name'] );
                $tax_edit_item = isset($tax_settings['tax_edit_item']) && !empty($tax_settings['tax_edit_item']) ? $tax_settings['tax_edit_item'] : sprintf( __( 'Edit %s', 'custom-post-types' ), $tax_settings['tax_singular_name'] );
                $tax_update_item = isset($tax_settings['tax_update_item']) && !empty($tax_settings['tax_update_item']) ? $tax_settings['tax_update_item'] : sprintf( __( 'Update %s', 'custom-post-types' ), $tax_settings['tax_singular_name'] );
                $tax_add_new_item = isset($tax_settings['tax_add_new_item']) && !empty($tax_settings['tax_add_new_item']) ? $tax_settings['tax_add_new_item'] : sprintf( __( 'Add %s', 'custom-post-types' ), $tax_settings['tax_singular_name'] );
                $tax_new_item_name = isset($tax_settings['tax_new_item_name']) && !empty($tax_settings['tax_new_item_name']) ? $tax_settings['tax_new_item_name'] : sprintf( __( '%s name', 'custom-post-types' ), $tax_settings['tax_singular_name'] );
                $tax_menu_name = isset($tax_settings['tax_menu_name']) && !empty($tax_settings['tax_menu_name']) ? $tax_settings['tax_menu_name'] : __( $taxonomy->post_title, 'custom-post-types' );

                $labels = array(
                    'name'              => __( $taxonomy->post_title, 'custom-post-types' ),
                    'singular_name'     => __( $tax_settings['tax_singular_name'], 'custom-post-types' ),
                    'search_items'      => $tax_search_items,
                    'all_items'         => $tax_all_items,
                    'parent_item'       => $tax_parent_item,
                    'parent_item_colon' => $tax_parent_item_colon,
                    'edit_item'         => $tax_edit_item,
                    'update_item'       => $tax_update_item,
                    'add_new_item'      => $tax_add_new_item,
                    'new_item_name'     => $tax_new_item_name,
                    'menu_name'         => $tax_menu_name,
                );

                $args = array(
                    'hierarchical'      => $tax_settings['tax_hierarchical'] == 0 ? false : true,
                    'labels'            => $labels,
                    'show_ui'           => true,
                    'show_admin_column' => true,
                    'public'            => $tax_settings['tax_public'] == 0 ? false : true,
                    'query_var'         => $tax_settings['tax_public'] == 0 ? false : true,
                    'rewrite'           => $tax_settings['tax_public'] == 0 ? false : array( 'slug' => sanitize_title($taxonomy->post_title) ),
                    'show_in_rest'       => $tax_settings['tax_public'] == 0 ? false : true,
                    'capabilities' => array(
                        'manage_terms' => $tax_settings['tax_role'] == 0 ? 'edit_posts' : 'update_core',
                        'edit_terms' => $tax_settings['tax_role'] == 0 ? 'edit_posts' : 'update_core',
                        'delete_terms' => $tax_settings['tax_role'] == 0 ? 'edit_posts' : 'update_core',
                        'assign_terms' => $tax_settings['tax_role'] == 0 ? 'edit_posts' : 'update_core',
                    )
                );

                register_taxonomy( $tax_id , $tax_settings['tax_post_types'] ? $tax_settings['tax_post_types'] : array(), $args );

            }

        }

        public function init_created_template($load_template){

            $current_theme_name = wp_get_theme()->get( 'TextDomain' );
            $upload = wp_upload_dir();
            $upload_dir = $upload['basedir'];
            $current_teme_single_template = $upload_dir . '/custom-templates/' . $current_theme_name .'-single.php';

            if (is_single()){
                global $post;
                $created_templates = get_posts( array('posts_per_page' => -1, 'post_type' => $this->cpt_ui_name . '_template') );
                foreach($created_templates as $template){
                    $template_settings = get_post_meta( $template->ID, '_template_settings_meta')[0];
                    $template_used_by = isset($template_settings['field_used_by']) ? $template_settings['field_used_by'] : '';
                    if($template_used_by === $post->post_type && file_exists($current_teme_single_template)){
                        $load_template = $current_teme_single_template;
                    }
                }
            }

            return $load_template;
        }

        public function init_admin_form_top() {
            $screen = get_current_screen();
            if( ($screen->post_type == $this->cpt_ui_name && $screen->id == $this->cpt_ui_name) || ($screen->post_type == $this->cpt_ui_name . '_tax' && $screen->id == $this->cpt_ui_name . '_tax')  ) { ?>
<div class="<?php echo $this->cpt_ui_name;?>_top">
    <label for="title"><?php _e( 'Name', 'custom-post-types' ); ?></label>
</div>
<?php }
        }

        public function init_admin_form_sub() {
            $screen = get_current_screen();
            if( ($screen->post_type == $this->cpt_ui_name && $screen->id == $this->cpt_ui_name) || ($screen->post_type == $this->cpt_ui_name . '_tax' && $screen->id == $this->cpt_ui_name . '_tax')  ) { ?>
<div class="<?php echo $this->cpt_ui_name;?>_sub">
    <small><?php _e( 'It must be unique. It will be used in the menu and as a slug in the URLs.', 'custom-post-types' ); ?></small>
</div>
<?php }
        }

        public function work_in_progress() {
?>
<div class="wrap">
    <h1><?php echo __( 'Work in progress...', 'custom-post-types' );?></h1>

    <p><?php echo __( 'I am working to implement this feature which will be free to all users. <br> Below is the donation link to help me support processing costs and the link to my website.', 'custom-post-types' );?></p>
    <p><a href="https://www.andreadegiovine.it/outlinks/1422/?utm_source=wordpress_org&utm_medium=plugin_page&utm_campaign=<?php echo (!empty($_GET['page']) ? $_GET['page'] : 'installed_plugin' );?>" class="button button-primary" target="_blank"><?php echo __( 'Send donation', 'custom-post-types' );?></a></p>
    <p><a href="https://www.andreadegiovine.it/" class="button button-primary" target="_blank"><?php echo __( 'Developer website', 'custom-post-types' );?></a></p>
    <p><a href="https://www.andreadegiovine.it/invia-suggerimento?oggetto=Custom%20Post%20Types%20Plugin" class="button button-primary" target="_blank"><?php echo __( 'Send suggestion', 'custom-post-types' );?></a></p>

</div>
<?php }

        public function init_cpt_ui_unique_name( $data , $postarr ){
            if( count(get_posts( array('posts_per_page' => 2, 'post_type'  => $data['post_type'], 'title' => $data['post_title'], 'exclude' => $postarr['ID']) )) >= 1 && ($data['post_type'] == $this->cpt_ui_name . '_tax' || $data['post_type'] == $this->cpt_ui_name )){
                $data['post_title'] .= ' ' . $postarr['ID'];
            }
            return $data;
        }

        public function init_cpt_admin_columns($columns){
            global $pagenow;
            if( 'edit.php' === $pagenow && isset($_GET['post_type']) && $_GET['post_type'] === $this->cpt_ui_name ){
                $date = $columns['date'];
                unset($columns['date']);
                $columns['cpt_id'] = __( 'ID', 'custom-post-types' );
                $columns['cpt_base'] = __( 'Permalink base', 'custom-post-types' );
                $columns['cpt_count'] = __( 'Count', 'custom-post-types' );
                $columns['date'] = $date;
            } elseif( 'edit.php' === $pagenow && isset($_GET['post_type']) && $_GET['post_type'] === $this->cpt_ui_name . '_tax' ){
                $date = $columns['date'];
                unset($columns['date']);
                $columns['cpt_id'] = __( 'ID', 'custom-post-types' );
                $columns['cpt_base'] = __( 'Permalink base', 'custom-post-types' );
                $columns['cpt_used'] = __( 'Used by', 'custom-post-types' );
                $columns['date'] = $date;
            } elseif( 'edit.php' === $pagenow && isset($_GET['post_type']) && $_GET['post_type'] === $this->cpt_ui_name . '_field' ){
                $date = $columns['date'];
                unset($columns['date']);
                $columns['cpt_field_count'] = __( 'Fields count', 'custom-post-types' );
                $columns['cpt_used'] = __( 'Used by', 'custom-post-types' );
                $columns['date'] = $date;
            } elseif( 'edit.php' === $pagenow && isset($_GET['post_type']) && $_GET['post_type'] === $this->cpt_ui_name . '_template' ){
                $date = $columns['date'];
                unset($columns['date']);
                $columns['cpt_used'] = __( 'Used by', 'custom-post-types' );
                $columns['date'] = $date;
            }

            return $columns;
        }

        public function init_render_cpt_admin_columns( $column, $post_id ) {
            global $pagenow;

            if( 'edit.php' === $pagenow && isset($_GET['post_type']) && $_GET['post_type'] === $this->cpt_ui_name ){
                switch ( $column ) {

                    case 'cpt_id' :
                        $cpt_settings = isset(get_post_meta( $post_id, '_cpt_settings_meta')[0]) ? get_post_meta( $post_id, '_cpt_settings_meta')[0] : array();
                        echo (isset($cpt_settings['cpt_id']) ? $cpt_settings['cpt_id'] : 'cpt_' . $post_id);
                        break;


                    case 'cpt_base' :
                        $cpt_settings = get_post_meta( $post_id, '_cpt_settings_meta')[0];
                        echo ( $cpt_settings['cpt_public'] == 0 ? '-' : '/' . sanitize_title(get_post($post_id)->post_title) . '/' );
                        break;

                    case 'cpt_count' :
                        $cpt_settings = isset(get_post_meta( $post_id, '_cpt_settings_meta')[0]) ? get_post_meta( $post_id, '_cpt_settings_meta')[0] : array();
                        echo '<a href="' . admin_url( 'edit.php?post_type=' . (isset($cpt_settings['cpt_id']) ? $cpt_settings['cpt_id'] : 'cpt_' . $post_id) ) . '">' . wp_count_posts((isset($cpt_settings['cpt_id']) ? $cpt_settings['cpt_id'] : 'cpt_' . $post_id))->publish . '</a>';
                        break;

                }
            } elseif( 'edit.php' === $pagenow && isset($_GET['post_type']) && $_GET['post_type'] === $this->cpt_ui_name . '_tax' ){
                switch ( $column ) {

                    case 'cpt_id' :
                        $tax_settings = isset(get_post_meta( $post_id, '_tax_settings_meta')[0]) ? get_post_meta( $post_id, '_tax_settings_meta')[0] : array();
                        echo (isset($tax_settings['tax_id']) && !empty($tax_settings['tax_id']) ? $tax_settings['tax_id'] : 'tax_' . $post_id);
                        break;


                    case 'cpt_base' :
                        $tax_settings = get_post_meta( $post_id, '_tax_settings_meta')[0];
                        echo ( $tax_settings['tax_public'] == 0 ? '-' : '/' . sanitize_title(get_post($post_id)->post_title) . '/' );
                        break;

                    case 'cpt_used' :
                        global $wp_taxonomies;
                        $tax_settings = isset(get_post_meta( $post_id, '_tax_settings_meta')[0]) ? get_post_meta( $post_id, '_tax_settings_meta')[0] : array();
                        if(isset( $wp_taxonomies[(isset($tax_settings['tax_id']) && !empty($tax_settings['tax_id']) ? $tax_settings['tax_id'] : 'tax_' . $post_id)] )){
                            $output = array();
                            foreach($wp_taxonomies[(isset($tax_settings['tax_id']) && !empty($tax_settings['tax_id']) ? $tax_settings['tax_id'] : 'tax_' . $post_id)]->object_type as $post_type){
                                $obj = get_post_type_object( $post_type );
                                if($obj){
                                    $output[] = '<a href="' . admin_url( 'edit.php?post_type=' . $post_type ) . '">' . $obj->labels->name . '</a>';
                                }
                            }
                            echo (!empty($output) ? implode(', ',$output) : '-');
                        } else {
                            echo '-';
                        }
                        break;

                }
            } elseif( 'edit.php' === $pagenow && isset($_GET['post_type']) && $_GET['post_type'] === $this->cpt_ui_name . '_field' ){
                switch ( $column ) {

                    case 'cpt_used' :
                        $field_settings = isset(get_post_meta( $post_id, '_field_settings_meta')[0]) ? get_post_meta( $post_id, '_field_settings_meta')[0] : array();
                        $field_post_types = isset($field_settings['field_post_types']) ? $field_settings['field_post_types'] : array();


                        if(!empty($field_post_types)){
                            $output = array();
                            foreach($field_post_types as $post_type){
                                $obj = get_post_type_object( $post_type );
                                if($obj){
                                    $output[] = '<a href="' . admin_url( 'edit.php?post_type=' . $post_type ) . '">' . $obj->labels->name . '</a>';
                                }
                            }
                            echo (!empty($output) ? implode(', ',$output) : '-');
                        } else {
                            echo '-';
                        }
                        break;

                    case 'cpt_field_count' :
                        $field_settings = isset(get_post_meta( $post_id, '_field_settings_meta')[0]) ? get_post_meta( $post_id, '_field_settings_meta')[0] : array();
                        $all_fields = isset($field_settings['fields']) ? $field_settings['fields'] : array();

                        echo count($all_fields);
                        break;
                }
            } elseif( 'edit.php' === $pagenow && isset($_GET['post_type']) && $_GET['post_type'] === $this->cpt_ui_name . '_template' ){
                switch ( $column ) {

                    case 'cpt_used' :
                        $template_settings = isset(get_post_meta( $post_id, '_template_settings_meta')[0]) ? get_post_meta( $post_id, '_template_settings_meta')[0] : array();

                        $field_post_types = isset($template_settings['field_used_by']) ? $template_settings['field_used_by'] : '';

                        if(!empty($field_post_types) && post_type_exists($field_post_types)){
                            $obj = get_post_type_object( $field_post_types );

                            $output = '<a href="' . admin_url( 'edit.php?post_type=' . $field_post_types ) . '">' . $obj->labels->name . '</a>';
                            echo (!empty($output) ? $output : '-');
                        } else {
                            echo '-';
                        }

                        break;

                }
            }
        }

        public function init_admin_notices(){
            if(!isset($_COOKIE[ $this->cpt_ui_name . '_notices_close'])) { ?>
<?php
                if( !$this->is_pro_version_active() ) {
                    printf( '<div class="%1$s"><p>%2$s<br><strong><a href="%3$s" target="_blank">%4$s</a></strong> - <a href="%5$s" target="_blank">%6$s</a> - <a href="%7$s" target="_blank">%8$s</a> - <a href="#" class="%9$s-notices-close">%10$s</a></p></div>', $this->cpt_ui_name . '-notices notice notice-success is-dismissible', __( '<strong>Custom post type</strong> notice:<br>Thanks for using this plugin! Do you want to help us grow to add new features?', 'custom-post-types' ) , 'https://www.andreadegiovine.it/webmaster/custom-post-types-pro?utm_source=tools_plugin_page&utm_medium=plugin_page&utm_campaign=custom_post_types', __( 'Become PRO', 'custom-post-types' ), 'https://wordpress.org/support/plugin/custom-post-types/reviews/#new-post', __( 'Review', 'custom-post-types' ), 'https://www.andreadegiovine.it/outlinks/1422/?utm_source=wordpress_dashboard&utm_medium=notices&utm_campaign=custom_post_types', __( 'Send donation', 'custom-post-types' ), $this->cpt_ui_name, __( 'Close and don\'t show again', 'custom-post-types' ) );
                } else {
                    printf( '<div class="%1$s"><p>%2$s<br><strong><a href="%3$s" target="_blank">%4$s</a></strong> - <a href="%5$s" target="_blank">%6$s</a> - <a href="#" class="%7$s-notices-close">%8$s</a></p></div>', $this->cpt_ui_name . '-notices notice notice-success is-dismissible', __( '<strong>Custom post type</strong> notice:<br>Thanks for using this plugin! Do you want to help us grow to add new features?', 'custom-post-types' ) , 'https://wordpress.org/support/plugin/custom-post-types/reviews/#new-post', __( 'Review', 'custom-post-types' ), 'https://www.andreadegiovine.it/outlinks/1422/?utm_source=wordpress_dashboard&utm_medium=notices&utm_campaign=custom_post_types', __( 'Send donation', 'custom-post-types' ), $this->cpt_ui_name, __( 'Close and don\'t show again', 'custom-post-types' ) );
                }
?>
<script>
    function createCookie(name, value, days) {
        var expires;
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toGMTString();
        } else {
            expires = "";
        }
        document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
    }
    jQuery(document).ready(function ($) {
        $( ".<?php echo $this->cpt_ui_name;?>-notices .<?php echo $this->cpt_ui_name;?>-notices-close" ).click(function(e) {
            e.preventDefault();
            createCookie('<?php echo $this->cpt_ui_name;?>_notices_close',1,7);
            $( ".<?php echo $this->cpt_ui_name;?>-notices .notice-dismiss" ).click();
        });
    });
</script>
<?php }

            $current_theme_name = wp_get_theme()->get( 'TextDomain' );
            if( is_child_theme() ){
                $current_theme_name = wp_get_theme()->get( 'Template' );
            }
            $upload = wp_upload_dir();
            $upload_dir = $upload['basedir'];
            $current_teme_single_template = $upload_dir . '/custom-templates/' . $current_theme_name .'-single.php';
            $theme_single_template = file_exists(get_template_directory() . '/single.php') ? file_get_contents(get_template_directory() . '/single.php') : '';

            $theme_compatibility_array = $this->theme_template_compatibility;

            if( !isset($theme_compatibility_array[$current_theme_name]) && empty($theme_single_template) ){
                printf( '<div class="%1$s"><p>%2$s<br><a href="%3$s" class="button-primary" style="margin-top:1em;" target="_blank">%4$s</a> <a href="%5$s" class="button-secondary" style="margin-top:1em;" target="_blank">%6$s</a></p></div>', $this->cpt_ui_name . '-notices notice notice-error', __( '<strong>Custom post type</strong> notice:<br>The theme you are using does not contain the "single.php" file in the main theme folder. To use custom templates the theme must have the "single.php" file.', 'custom-post-types' ) , 'https://wordpress.org/support/plugin/custom-post-types/', __( 'Ask for integration', 'custom-post-types' ), 'https://developer.wordpress.org/themes/template-files-section/post-template-files/', __( 'WordPress documentation', 'custom-post-types' ) );
            }

            $theme_has_while = false;
            if( !isset($theme_compatibility_array[$current_theme_name]) && !empty($theme_single_template) ){
                foreach($theme_compatibility_array['default']['replace'] as $replace_regex){
                    if( preg_match($replace_regex,$theme_single_template,$matches) ){
                        $theme_has_while = true;
                    }
                }
                if( !$theme_has_while ){
                    printf( '<div class="%1$s"><p>%2$s<br><a href="%3$s" class="button-primary" style="margin-top:1em;" target="_blank">%4$s</a> <a href="%5$s" class="button-secondary" style="margin-top:1em;" target="_blank">%6$s</a></p></div>', $this->cpt_ui_name . '-notices notice notice-error', __( '<strong>Custom post type</strong> notice:<br>The theme you are using does not contain the "while () : ... endwhile;" loop in the "single.php" file. To use custom templates the theme must have this "while" loop type in its "single.php" file.', 'custom-post-types' ) , 'https://wordpress.org/support/plugin/custom-post-types/', __( 'Ask for integration', 'custom-post-types' ), 'https://codex.wordpress.org/The_Loop#Loop_Examples', __( 'WordPress documentation', 'custom-post-types' ) );
                }
            }

            if( !file_exists($current_teme_single_template) && ( $theme_has_while || isset($theme_compatibility_array[$current_theme_name]) ) ){
                printf( '<div class="%1$s"><p>%2$s<br><a href="%3$s" class="button-primary" style="margin-top:1em;">%4$s</a></p></div>', $this->cpt_ui_name . '-notices notice notice-error', __( '<strong>Custom post type</strong> notice:<br>Is this the first time you use the plugin or have you changed the theme?<br>In order to use custom templates, the plugin must copy the "single.php" file of the current theme to the "custom-templates" folder contained in the WordPress "uploads" folder.', 'custom-post-types' ) , admin_url( 'edit.php?post_type='.$this->cpt_ui_name.'_template&action=copy_theme_template' ), __( 'Continue', 'custom-post-types' ) );
            }

        }

        public function init_admin_action_links($links, $file){
            if ( $file == 'custom-post-types/custom-post-types.php' ) {
                $links[] = sprintf( '<a href="%s" target="_blank"> %s </a>', 'https://wordpress.org/support/plugin/custom-post-types', __( 'Support', 'custom-post-types' ) );
                $links[] = sprintf( '<a href="%s" target="_blank"> %s </a>', 'https://wordpress.org/support/plugin/custom-post-types/reviews/#new-post', __( 'Review', 'custom-post-types' ) );
            }
            return $links;
        }

        public function field_shortcode($atts){
            $a = shortcode_atts( array(
                'id' => null,
            ), $atts );

            if(!$a['id']){
                return 'errore';
            }

            global $post;

            if($a['id'] == 'title'){
                return $post->post_title;
            } elseif($a['id'] == 'content'){
                return do_shortcode( $post->post_content );
            } elseif($a['id'] == 'excerpt'){
                return $post->post_excerpt;
            } elseif($a['id'] == 'image'){
                $img_src = get_the_post_thumbnail_url($post->ID,'full');
                return '<img src="' . $img_src . '" class="field-'.$a['id'].'">';
            } elseif($a['id'] == 'date'){
                return get_the_date( get_option( 'date_format' ), $post->ID );
            } elseif($a['id'] == 'author'){
                $author_id=$post->post_author;
                return get_the_author_meta( 'user_nicename' , $author_id );
            }

            $all_custom_metaboxes = get_posts( array( 'posts_per_page' => -1, 'post_type' => $this->cpt_ui_name . '_field' ) );
            $all_metabox_array = array();
            foreach($all_custom_metaboxes as $metaboxes){
                $field_settings = get_post_meta( $metaboxes->ID, '_field_settings_meta')[0];
                $all_metabox_array = array_merge($all_metabox_array, $field_settings['fields']);
            }

            $field_type = isset($all_metabox_array[$a['id']]['type']) ? $all_metabox_array[$a['id']]['type'] : '';
            if(!$field_type){
                $field_type = isset(get_post_meta( $post->ID, '_custom_meta')[0][$a['id']]['type']) ? get_post_meta( $post->ID, '_custom_meta')[0][$a['id']]['type'] : '';
            }
            $value = '';
            if( isset($all_metabox_array[$a['id']]['id']) ){
                $value = metadata_exists('post', $post->ID, $all_metabox_array[$a['id']]['id']) ? get_post_meta( $post->ID, $all_metabox_array[$a['id']]['id'], true ) : '';
            }
            if(!$value){
                $value = isset(get_post_meta( $post->ID, '_custom_meta')[0][$a['id']]['value']) ? get_post_meta( $post->ID, '_custom_meta')[0][$a['id']]['value'] : '';
            }
            if(!$value){
                $value = isset(get_post_meta( $post->ID, '_custom_meta')[0][$a['id']]) ? get_post_meta( $post->ID, '_custom_meta')[0][$a['id']] : '';
            }

            if(!$field_type){

                if (DateTime::createFromFormat('Y-m-d', $value) !== FALSE) {
                    $value = date(get_option( 'date_format' ), strtotime($value));
                }

                if (DateTime::createFromFormat('H:i', $value) !== FALSE) {
                    $value = date(get_option( 'time_format' ), strtotime($value));
                }

            } else {
                if($field_type == 'date'){
                    $value = date(get_option( 'date_format' ), strtotime($value));
                }

                if($field_type == 'time'){
                    $value = date(get_option( 'time_format' ), strtotime($value));
                }

                if($field_type == 'taxonomy_relationship'){
                    $value = '<a href="' . get_term_link( (int) $value ) . '">' . get_term($value)->name . '</a>';
                }

                if($field_type == 'post_relationship'){
                    $value = '<a href="' . get_permalink($value) . '">' . get_post($value)->post_title . '</a>';
                }

                if($field_type == 'file'){
                    $value = '<a href="' . $value . '" target="_blank">' . apply_filters('field_type_file_label',__( 'Attached file', 'custom-post-types' )) . '</a>';
                }

                $value = apply_filters('get_field_from_shortcode',$value,$field_type,$a['id']);
            }

            return $value;

        }

        public function tax_shortcode($atts){
            $a = shortcode_atts( array(
                'id' => null,
            ), $atts );

            if(!$a['id']){
                return 'errore';
            }

            global $post;

            $taxonomies = '';

            $terms = get_the_terms( $post->ID, $a['id'] );

            if ( $terms && ! is_wp_error( $terms ) ){

                $terms_array = array();

                foreach($terms as $term){
                    $terms_array[] = '<a href="'.get_term_link($term->term_id).'">'.$term->name.'</a>';
                }

                $taxonomies .= implode(', ', $terms_array );

            }

            return $taxonomies;

        }


        public function copy_theme_single_template(){
            $current_theme_name = wp_get_theme()->get( 'TextDomain' );
            if( is_child_theme() ){
                $current_theme_name = wp_get_theme()->get( 'Template' );
            }
            $upload = wp_upload_dir();
            $upload_dir = $upload['basedir'];
            $current_teme_single_template = $upload_dir . '/custom-templates/' . $current_theme_name .'-single.php';
            if (!file_exists($current_teme_single_template) && (isset($_GET['action']) && 'copy_theme_template' == $_GET['action'] )) {

                $theme_compatibility_array = $this->theme_template_compatibility;

                $template_to_copy = isset( $theme_compatibility_array[$current_theme_name] ) ? $theme_compatibility_array[$current_theme_name] : $theme_compatibility_array['default'] ;

                $copy_template = false;
                $theme_single_template = file_get_contents( $template_to_copy['single_template'] );
                $add_after = isset( $template_to_copy['after'] ) ? $template_to_copy['after'] : '' ;
                if($template_to_copy['replace_type'] == 'string'){
                    $new_single_template = str_replace($template_to_copy['replace'], "custom_post_types_get_custom_template()".$add_after, $theme_single_template);
                    $copy_template = true;
                } else {
                    if( !is_array( $template_to_copy['replace'] ) ){
                        if( preg_match($template_to_copy['replace'],$theme_single_template,$matches) ){
                            $new_single_template = preg_replace( $template_to_copy['replace'], "custom_post_types_get_custom_template();\n".$add_after, $theme_single_template);
                            $copy_template = true;
                        }
                    } else {
                        foreach($template_to_copy['replace'] as $replace_regex){
                            if(!$copy_template && preg_match($replace_regex,$theme_single_template,$matches)){
                                $new_single_template = preg_replace( $replace_regex, "custom_post_types_get_custom_template();\n".$add_after, $theme_single_template);
                                $copy_template = true;
                            }
                        }
                    }
                }

                if($copy_template){
                    $new_file = fopen($current_teme_single_template, "w") or die("Unable to create the template file! Check permission.");
                    fwrite($new_file, $new_single_template);
                    fclose($new_file);
                    wp_redirect( admin_url( 'edit.php?post_type='.$this->cpt_ui_name.'_template' ) );
                    exit;
                } else {
                    die("Unable to create the template file! Check theme compatibility.");
                }

            }

        }


    }

    new adg_custom_post_types();
}

if (!function_exists('get_custom_field')) {
    function get_custom_field($field_id = null, $post_id = null){
        global $post;
        if(!$field_id || is_admin() || !is_single()){
            return;
        }
        if( $post && ( !$post_id || !get_post($post_id) ) ) {
            $post_id = $post->ID;
        }
        $value = metadata_exists('post', $post->ID, $field_id) ? get_post_meta( $post->ID, $field_id, true ) : '';
        if(!$value){
            $value = isset(get_post_meta( $post_id, '_custom_meta')[0][$field_id]['value']) ? get_post_meta( $post_id, '_custom_meta')[0][$field_id]['value'] : '';
        }
        if(!$value){
            $value = isset(get_post_meta( $post_id, '_custom_meta')[0][$field_id]) ? get_post_meta( $post_id, '_custom_meta')[0][$field_id] : '';
        }
        return $value;
    }
}

if ( !function_exists( 'is_rest' ) ) {
    /**
     * Checks if the current request is a WP REST API request.
     *
     * Case #1: After WP_REST_Request initialisation
     * Case #2: Support "plain" permalink settings
     * Case #3: It can happen that WP_Rewrite is not yet initialized,
     *          so do this (wp-settings.php)
     * Case #4: URL Path begins with wp-json/ (your REST prefix)
     *          Also supports WP installations in subfolders
     *
     * @returns boolean
     * @author matzeeable
     */
    function is_rest() {
        $prefix = rest_get_url_prefix( );
        if (defined('REST_REQUEST') && REST_REQUEST // (#1)
            || isset($_GET['rest_route']) // (#2)
            && strpos( trim( $_GET['rest_route'], '\\/' ), $prefix , 0 ) === 0)
            return true;
        // (#3)
        global $wp_rewrite;
        if ($wp_rewrite === null) $wp_rewrite = new WP_Rewrite();

        // (#4)
        $rest_url = wp_parse_url( trailingslashit( rest_url( ) ) );
        $current_url = wp_parse_url( add_query_arg( array( ) ) );
        return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
    }
}

function init_plugin_dev_functions(){

    if (!function_exists('custom_post_types_get_custom_template')) {
        function custom_post_types_get_custom_template(){
            if (is_single()){
                global $post;
                $created_templates = get_posts( array('posts_per_page' => -1, 'post_type' => 'manage_cpt_template') );
                foreach($created_templates as $template){
                    $template_settings = get_post_meta( $template->ID, '_template_settings_meta')[0];
                    $template_used_by = isset($template_settings['field_used_by']) ? $template_settings['field_used_by'] : '';
                    if($template_used_by === $post->post_type){
                        echo do_shortcode($template->post_content);
                    }
                }
            }
        }
    }

}
add_action('init', 'init_plugin_dev_functions');
