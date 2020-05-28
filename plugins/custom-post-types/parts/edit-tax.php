<?php
wp_nonce_field( 'tax_ui_inner_metabox', 'tax_ui_metabox_nonce' );
$tax_settings = isset(get_post_meta( $post->ID, '_tax_settings_meta')[0]) ? get_post_meta( $post->ID, '_tax_settings_meta')[0] : array();

$tax_id = isset($tax_settings['tax_id']) ? $tax_settings['tax_id'] : 'tax_' . $post->ID;

$tax_singular_name = isset($tax_settings['tax_singular_name']) ? $tax_settings['tax_singular_name'] : '';
$tax_hierarchical = isset($tax_settings['tax_hierarchical']) && $tax_settings['tax_hierarchical'] == 0 ? 0 : 1;
$tax_public = isset($tax_settings['tax_public']) && $tax_settings['tax_public'] == 0 ? 0 : 1;
$tax_post_types = isset($tax_settings['tax_post_types']) ? $tax_settings['tax_post_types'] : array();
$tax_role = isset($tax_settings['tax_role']) && $tax_settings['tax_role'] == 0 ? 0 : 1;

$tax_search_items = isset($tax_settings['tax_search_items']) ? $tax_settings['tax_search_items'] : '';
$tax_all_items = isset($tax_settings['tax_all_items']) ? $tax_settings['tax_all_items'] : '';
$tax_parent_item = isset($tax_settings['tax_parent_item']) ? $tax_settings['tax_parent_item'] : '';
$tax_parent_item_colon = isset($tax_settings['tax_parent_item_colon']) ? $tax_settings['tax_parent_item_colon'] : '';
$tax_edit_item = isset($tax_settings['tax_edit_item']) ? $tax_settings['tax_edit_item'] : '';
$tax_update_item = isset($tax_settings['tax_update_item']) ? $tax_settings['tax_update_item'] : '';
$tax_add_new_item = isset($tax_settings['tax_add_new_item']) ? $tax_settings['tax_add_new_item'] : '';
$tax_new_item_name = isset($tax_settings['tax_new_item_name']) ? $tax_settings['tax_new_item_name'] : '';
$tax_menu_name = isset($tax_settings['tax_menu_name']) ? $tax_settings['tax_menu_name'] : '';
?>
<div class="wp-cpt-row">
    <label for="tax_id">
        <?php _e( 'ID', 'custom-post-types' ); ?>
    </label>
    <input type="text" id="tax_id" name="tax_settings[tax_id]" value="<?php echo esc_attr( $tax_id ); ?>" placeholder="<?php _e( 'ex: product-category', 'custom-post-types' ); ?>" required />
    <small><?php _e( 'Taxonomy ID.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row">
    <label for="tax_singular_name">
        <?php _e( 'Single name', 'custom-post-types' ); ?>
    </label>
    <input type="text" id="tax_singular_name" name="tax_settings[tax_singular_name]" value="<?php echo esc_attr( $tax_singular_name ); ?>" placeholder="<?php _e( 'ex: Product Category', 'custom-post-types' ); ?>" required />
    <small><?php _e( 'Singular name.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row advanced-cpt-settings">
    <label for="tax_search_items">
        <?php _e( 'Search items', 'custom-post-types' ); ?>
    </label>
    <input type="text" id="tax_search_items" name="tax_settings[tax_search_items]" value="<?php echo esc_attr( $tax_search_items ); ?>" placeholder="<?php _e( 'ex: Search tags', 'custom-post-types' ); ?>" />
    <small><?php _e( 'The search items text.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row advanced-cpt-settings">
    <label for="tax_all_items">
        <?php _e( 'All items', 'custom-post-types' ); ?>
    </label>
    <input type="text" id="tax_all_items" name="tax_settings[tax_all_items]" value="<?php echo esc_attr( $tax_all_items ); ?>" placeholder="<?php _e( 'ex: All tags', 'custom-post-types' ); ?>" />
    <small><?php _e( 'The all items text.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row advanced-cpt-settings">
    <label for="tax_parent_item">
        <?php _e( 'Parent item', 'custom-post-types' ); ?>
    </label>
    <input type="text" id="tax_parent_item" name="tax_settings[tax_parent_item]" value="<?php echo esc_attr( $tax_parent_item ); ?>" placeholder="<?php _e( 'ex: Parent category', 'custom-post-types' ); ?>" />
    <small><?php _e( 'The parent item text.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row advanced-cpt-settings">
    <label for="tax_parent_item_colon">
        <?php _e( 'Parent item colon', 'custom-post-types' ); ?>
    </label>
    <input type="text" id="tax_parent_item_colon" name="tax_settings[tax_parent_item_colon]" value="<?php echo esc_attr( $tax_parent_item_colon ); ?>" placeholder="<?php _e( 'ex: Parent category', 'custom-post-types' ); ?>" />
    <small><?php _e( 'The parent item colon text.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row advanced-cpt-settings">
    <label for="tax_edit_item">
        <?php _e( 'Edit item', 'custom-post-types' ); ?>
    </label>
    <input type="text" id="tax_edit_item" name="tax_settings[tax_edit_item]" value="<?php echo esc_attr( $tax_edit_item ); ?>" placeholder="<?php _e( 'ex: Edit tag', 'custom-post-types' ); ?>" />
    <small><?php _e( 'The edit item text.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row advanced-cpt-settings">
    <label for="tax_update_item">
        <?php _e( 'Update item', 'custom-post-types' ); ?>
    </label>
    <input type="text" id="tax_update_item" name="tax_settings[tax_update_item]" value="<?php echo esc_attr( $tax_update_item ); ?>" placeholder="<?php _e( 'ex: Update tag', 'custom-post-types' ); ?>" />
    <small><?php _e( 'The update item text.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row advanced-cpt-settings">
    <label for="tax_add_new_item">
        <?php _e( 'Add new item', 'custom-post-types' ); ?>
    </label>
    <input type="text" id="tax_add_new_item" name="tax_settings[tax_add_new_item]" value="<?php echo esc_attr( $tax_add_new_item ); ?>" placeholder="<?php _e( 'ex: Add new tag', 'custom-post-types' ); ?>" />
    <small><?php _e( 'The add new item text.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row advanced-cpt-settings">
    <label for="tax_new_item_name">
        <?php _e( 'New item', 'custom-post-types' ); ?>
    </label>
    <input type="text" id="tax_new_item_name" name="tax_settings[tax_new_item_name]" value="<?php echo esc_attr( $tax_new_item_name ); ?>" placeholder="<?php _e( 'ex: New tag', 'custom-post-types' ); ?>" />
    <small><?php _e( 'The new item text.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row advanced-cpt-settings">
    <label for="tax_menu_name">
        <?php _e( 'Menu name', 'custom-post-types' ); ?>
    </label>
    <input type="text" id="tax_menu_name" name="tax_settings[tax_menu_name]" value="<?php echo esc_attr( $tax_menu_name ); ?>" placeholder="<?php _e( 'ex: Tags', 'custom-post-types' ); ?>" />
    <small><?php _e( 'The menu name text.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row">
    <label for="tax_hierarchical">
        <?php _e( 'Hierarchical', 'custom-post-types' ); ?>
    </label>
    <select id="tax_hierarchical" name="tax_settings[tax_hierarchical]">
        <option value="1" <?php selected( $tax_hierarchical, 1 ); ?>><?php _e( 'YES', 'custom-post-types' ); ?></option>
        <option value="0" <?php selected( $tax_hierarchical, 0 ); ?>><?php _e( 'NO', 'custom-post-types' ); ?></option>
    </select>    
    <small><?php _e( 'If set to "YES" it will be possible to set a parent TAXONOMY (as for the posts categories).', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row">
    <label for="tax_public">
        <?php _e( 'Visibility', 'custom-post-types' ); ?>
    </label>
    <select id="tax_public" name="tax_settings[tax_public]">
        <option value="1" <?php selected( $tax_public, 1 ); ?>><?php _e( 'YES', 'custom-post-types' ); ?></option>
        <option value="0" <?php selected( $tax_public, 0 ); ?>><?php _e( 'NO', 'custom-post-types' ); ?></option>
    </select>    
    <small><?php _e( 'If set to "YES" it will be shown in the frontend and will have a permalink and an archive template.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row">
    <label for="tax_post_types">
        <?php _e( 'Assignment', 'custom-post-types' ); ?>
    </label>
    <div>        
        <div><input type="checkbox" name="tax_settings[tax_post_types][]" value="post" <?php echo ( in_array("post", $tax_post_types) ? 'checked' : '');?>> <?php _e( 'Posts', 'custom-post-types' ); ?></div>
        <div><input type="checkbox" name="tax_settings[tax_post_types][]" value="page" <?php echo ( in_array("page", $tax_post_types) ? 'checked' : '');?>> <?php _e( 'Pages', 'custom-post-types' ); ?></div>


        <?php
        $post_types = get_post_types( array('_builtin' => false), 'objects');
        foreach ( $post_types  as $post_type ) { if($post_type->name != $this->cpt_ui_name && $post_type->name != $this->cpt_ui_name . '_tax' && $post_type->name != $this->cpt_ui_name . '_field' && $post_type->name != $this->cpt_ui_name . '_template') { ?>
        <div><input type="checkbox" name="tax_settings[tax_post_types][]" value="<?php echo $post_type->name; ?>" <?php echo ( in_array($post_type->name, $tax_post_types) ? 'checked' : '');?>> <?php echo $post_type->label; ?></div>
        <?php } }
        ?>
    </div>
    <small><?php _e( 'Choose for which POST TYPE use this taxonomy.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row">
    <label for="tax_role">
        <?php _e( 'Administrators only', 'custom-post-types' ); ?>
    </label>
    <select id="tax_role" name="tax_settings[tax_role]">
        <option value="1" <?php selected( $tax_role, 1 ); ?>><?php _e( 'YES', 'custom-post-types' ); ?></option>
        <option value="0" <?php selected( $tax_role, 0 ); ?>><?php _e( 'NO', 'custom-post-types' ); ?></option>
    </select>    
    <small><?php _e( 'If set to "YES", only the administrators can create / modify these contents, if "NO" all the roles with the minimum capacity of "edit_posts".', 'custom-post-types' ); ?></small>
</div>

<button class="advanced-cpt-settings-btn"><span class="dashicons 
    dashicons-admin-tools"></span><?php _e( 'Advanced view', 'custom-post-types' ); ?></button>
<button class="normal-cpt-settings-btn"><span class="dashicons 
    dashicons-admin-tools"></span><?php _e( 'Simple view', 'custom-post-types' ); ?></button>

