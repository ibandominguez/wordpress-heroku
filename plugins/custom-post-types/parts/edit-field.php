<?php
wp_nonce_field( 'field_ui_inner_metabox', 'field_ui_metabox_nonce' );
$field_settings = isset(get_post_meta( $post->ID, '_field_settings_meta')[0]) ? get_post_meta( $post->ID, '_field_settings_meta')[0] : array();

$field_position = isset($field_settings['field_position']) && $field_settings['field_position'] == 'side' ? 'side' : 'normal';
$field_post_types = isset($field_settings['field_post_types']) ? $field_settings['field_post_types'] : array();
$field_role = isset($field_settings['field_role']) && $field_settings['field_role'] == 0 ? 0 : 1;
$fields = isset($field_settings['fields']) ? $field_settings['fields'] : array();


$field_types_array = array(
    'text' => array(
        'label' => __( 'TEXT', 'custom-post-types' ),
    ),
    'number' => array(
        'label' => __( 'NUMBER', 'custom-post-types' ),
    ),
    'textarea' => array(
        'label' => __( 'TEXTAREA', 'custom-post-types' ),
    ),
    'email' => array(
        'label' => __( 'EMAIL', 'custom-post-types' ),
    ),
    'dropdown' => array(
        'label' => __( 'DROPDOWN', 'custom-post-types' ),
        'options' => 'the_dropdown_field_type_output',
    ),
    'date' => array(
        'label' => __( 'DATE', 'custom-post-types' ),
    ),
    'time' => array(
        'label' => __( 'TIME', 'custom-post-types' ),
    ),
    'file' => array(
        'label' => __( 'FILE UPLOAD', 'custom-post-types' ),
    ),
    'taxonomy_relationship' => array(
        'label' => __( 'TAXONOMY RELATIONSHIP', 'custom-post-types' ),
        'options' => 'the_tax_relationship_field_type_output',
    ),
    'post_relationship' => array(
        'label' => __( 'POST RELATIONSHIP', 'custom-post-types' ),
        'options' => 'the_post_relationship_field_type_output',
    ),
);
$field_types_array = apply_filters('form_edit_field_types',$field_types_array);  



function the_dropdown_field_type_output($id, $options = null){ ?>
<label><?php _e( 'Field options', 'custom-post-types' ); ?>
    <textarea class="field-extra-options" name="field_settings[fields][<?php echo $id;?>][options]" placeholder="<?php _e( 'Field options, one per row', 'custom-post-types' ); ?>"><?php echo $options;?></textarea>
</label>
<?php }

function the_tax_relationship_field_type_output($id, $options = null){ ?>
<label><?php _e( 'Field options', 'custom-post-types' ); ?>
    <select class="field-extra-options" name="field_settings[fields][<?php echo $id;?>][options]">
        <option value="category"><?php _e( 'Category', 'custom-post-types' ); ?></option>
        <option value="post_tag"><?php _e( 'Post tag', 'custom-post-types' ); ?></option>
        <?php 
                                                                      $taxs = get_taxonomies( array('_builtin' => false), 'objects');foreach ( $taxs  as $tax ) {  ?>
        <option value="<?php echo $tax->name; ?>"<?php selected( $options, $tax->name ); ?>><?php echo $tax->label; ?></option>
        <?php } ?>

    </select>
</label>
<?php }

function the_post_relationship_field_type_output($id, $options = null){ ?>
<label><?php _e( 'Field options', 'custom-post-types' ); ?>
    <select class="field-extra-options" name="field_settings[fields][<?php echo $id;?>][options]">
        <option value="post"<?php selected( $options, 'post' ); ?>><?php _e( 'Posts', 'custom-post-types' ); ?></option>
        <option value="page"<?php selected( $options, 'page' ); ?>><?php _e( 'Pages', 'custom-post-types' ); ?></option>
        <?php
                                                                       $post_types = get_post_types( array('_builtin' => false), 'objects');
                                                                       foreach ( $post_types  as $post_type ) { if($post_type->name != 'manage_cpt' && $post_type->name != 'manage_cpt_field'  && $post_type->name != 'manage_cpt_tax'  && $post_type->name != 'manage_cpt_template') { ?>            

        <option value="<?php echo $post_type->name; ?>"<?php selected( $options, $post_type->name ); ?>><?php echo $post_type->label; ?></option>           


        <?php } } ?>

    </select>
</label>
<?php }




?>


<div class="wp-cpt-row">
    <label for="field_position">
        <?php _e( 'Position', 'custom-post-types' ); ?>
    </label>
    <select id="field_position" name="field_settings[field_position]">
        <option value="normal" <?php selected( $field_position, 'normal' ); ?>><?php _e( 'EDITOR', 'custom-post-types' ); ?></option>
        <option value="side" <?php selected( $field_position, 'side' ); ?>><?php _e( 'SIDEBAR', 'custom-post-types' ); ?></option>
    </select>    
    <small><?php _e( 'If set to "EDITOR" it will be shown at the bottom of the central column, if "SIDEBAR" it will be shown in the sidebar.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row">
    <label for="field_post_types">
        <?php _e( 'Assignment', 'custom-post-types' ); ?>
    </label>
    <div>
        <div><input type="checkbox" name="field_settings[field_post_types][]" value="post" <?php echo ( in_array("post", $field_post_types) ? 'checked' : '');?>> <?php _e( 'Posts', 'custom-post-types' ); ?></div>
        <div><input type="checkbox" name="field_settings[field_post_types][]" value="page" <?php echo ( in_array("page", $field_post_types) ? 'checked' : '');?>> <?php _e( 'Pages', 'custom-post-types' ); ?></div>
        <?php
        $post_types = get_post_types( array('_builtin' => false), 'objects');
        foreach ( $post_types  as $post_type ) { if($post_type->name != $this->cpt_ui_name && $post_type->name != $this->cpt_ui_name . '_field'  && $post_type->name != $this->cpt_ui_name . '_tax' && $post_type->name != $this->cpt_ui_name . '_template') { ?>
        <div><input type="checkbox" name="field_settings[field_post_types][]" value="<?php echo $post_type->name; ?>" <?php echo ( in_array($post_type->name, $field_post_types) ? 'checked' : '');?>> <?php echo $post_type->label; ?></div>
        <?php } }
        ?>
    </div>
    <small><?php _e( 'Choose for which POST TYPE use this field.', 'custom-post-types' ); ?></small>
</div>

<div class="wp-cpt-row">
    <label for="field_role">
        <?php _e( 'Administrators only', 'custom-post-types' ); ?>
    </label>
    <select id="field_role" name="field_settings[field_role]">
        <option value="1" <?php selected( $field_role, 1 ); ?>><?php _e( 'YES', 'custom-post-types' ); ?></option>
        <option value="0" <?php selected( $field_role, 0 ); ?>><?php _e( 'NO', 'custom-post-types' ); ?></option>
    </select>    
    <small><?php _e( 'If set to "YES", only the administrators can create / modify these contents, if "NO" all the roles with the minimum capacity of "edit_posts".', 'custom-post-types' ); ?></small>
</div>

<h3><?php _e( 'Fields list', 'custom-post-types' ); ?></h3>
<p><?php _e( 'Add the custom fields you want to use when creating / editing the associated content types.<br>
To display the field in your template use the shortcode [custom-field id="field_XXXX"] that appears next to the created field.', 'custom-post-types' ); ?><br><?php _e( 'You can use the function included <strong>get_custom_field("field_XXXX")</strong> which returns the value of the field for the current post, or alternatively you can indicate a post ID to extract the value of the field <strong>get_custom_field("field_XXXX ", 66)</strong>.', 'custom-post-types' ); ?></p>

<?php   
foreach($fields as $id => $field){ if($field['type'] !== '0'){ ?>

<div class="wp-cpt-field-row field-form" id="<?php echo $id;?>">

    <div class="field-form-sx">
        <label><?php _e( 'Field name', 'custom-post-types' ); ?>
            <input type="text" name="field_settings[fields][<?php echo $id;?>][name]" placeholder="<?php _e( 'Field name', 'custom-post-types' ); ?>" value="<?php echo $field['name'];?>">
        </label>
    </div>
    <div class="field-form-dx">
        <label><?php _e( 'Field id', 'custom-post-types' ); ?>
            <input type="text" name="field_settings[fields][<?php echo $id;?>][id]" placeholder="<?php _e( 'Field id', 'custom-post-types' ); ?>" value="<?php echo ( isset($field['id']) && !empty($field['id']) ? $field['id'] : $id );?>">
        </label>
    </div>
    <div class="field-form-sx">
        <label><?php _e( 'Field type', 'custom-post-types' ); ?>
            <select class="field-type" name="field_settings[fields][<?php echo $id;?>][type]">
                <option value="0"><?php _e( 'Field type...', 'custom-post-types' ); ?></option>
                <?php
                                                              foreach($field_types_array as $type => $label){ ?>
                <option value="<?php echo $type;?>" <?php selected( $field['type'], $type ); ?>><?php echo $label['label']; ?></option>
                <?php } ?>
            </select>
        </label>
    </div>
    <div class="field-form-dx field-options">
        <?php

                                                              if( isset($field_types_array[$field['type']]['options']) ){
                                                                  $callback = $field_types_array[$field['type']]['options'];
                                                                  $callback($id, $field['options']);
                                                              }                                                     





                                                              /* if($field['type'] == 'dropdown' ){ ?>
        <label><?php _e( 'Field options', 'custom-post-types' ); ?>
            <textarea class="field-extra-options" name="field_settings[fields][<?php echo $id;?>][options]" placeholder="<?php _e( 'Field options, one per row', 'custom-post-types' ); ?>"><?php echo $field['options'];?></textarea>
        </label>
        <?php } */ ?>

        <?php if($field['type'] == 'taxonomy_relationship' ){ ?>
        <label><?php _e( 'Field options', 'custom-post-types' ); ?>
            <select class="field-extra-options" name="field_settings[fields][<?php echo $id;?>][options]">
                <option value="category"><?php _e( 'Category', 'custom-post-types' ); ?></option>
                <option value="post_tag"><?php _e( 'Post tag', 'custom-post-types' ); ?></option>
                <?php 
                                                             $taxs = get_taxonomies( array('_builtin' => false), 'objects');foreach ( $taxs  as $tax ) {  ?>
                <option value="<?php echo $tax->name; ?>"<?php selected( $field['options'], $tax->name ); ?>><?php echo $tax->label; ?></option>
                <?php } ?>

            </select>
        </label>
        <?php } ?>

        <?php if($field['type'] == 'post_relationship' ){ ?>
        <label><?php _e( 'Field options', 'custom-post-types' ); ?>
            <select class="field-extra-options" name="field_settings[fields][<?php echo $id;?>][options]">
                <option value="post"<?php selected( $field['options'], 'post' ); ?>><?php _e( 'Posts', 'custom-post-types' ); ?></option>
                <option value="page"<?php selected( $field['options'], 'page' ); ?>><?php _e( 'Pages', 'custom-post-types' ); ?></option>
                <?php
                                                         $post_types = get_post_types( array('_builtin' => false), 'objects');
                                                         foreach ( $post_types  as $post_type ) { if($post_type->name != $this->cpt_ui_name && $post_type->name != $this->cpt_ui_name . '_field'  && $post_type->name != $this->cpt_ui_name . '_tax'  && $post_type->name != $this->cpt_ui_name . '_template') { ?>            

                <option value="<?php echo $post_type->name; ?>"<?php selected( $field['options'], $post_type->name ); ?>><?php echo $post_type->label; ?></option>           


                <?php } } ?>

            </select>
        </label>
        <?php } ?>
    </div>
    <div class="field-form-sx">
        <label><input type="checkbox" name="field_settings[fields][<?php echo $id;?>][required]" value="1" <?php checked( (isset($field['required']) ? $field['required'] : 0), 1 ); ?>> <?php _e( 'Required', 'custom-post-types' ); ?></label>
    </div>
    <div class="field-form-delete">
        <a href="#" title="<?php _e( 'delete', 'custom-post-types' ); ?>"><span class="dashicons dashicons-trash"></span></a>
    </div>
    <div class="field-form-duplicate">
        <a href="#" title="<?php _e( 'duplicate', 'custom-post-types' ); ?>"><span class="dashicons dashicons-admin-page"></span></a>
    </div>

</div>

<?php }} ?>

<a href="#" class="button button-primary add-field"><?php _e( 'Add field', 'custom-post-types' ); ?></a> <button type="submit" class="button button-secondary"><?php _e( 'Save', 'custom-post-types' ); ?></button>

<div id="<?php echo $this->cpt_ui_name;?>-default_field_markup">
    <div class="default-field">
        <div class="field-form-sx">
            <label><?php _e( 'Field name', 'custom-post-types' ); ?>
                <input type="text" name="field_settings[fields][PLACEHOLDER_FIELD_ID][name]" placeholder="<?php _e( 'Field name', 'custom-post-types' ); ?>" value="">
            </label>
        </div>
        <div class="field-form-dx">
            <label><?php _e( 'Field id', 'custom-post-types' ); ?>
                <input type="text" name="field_settings[fields][PLACEHOLDER_FIELD_ID][id]" placeholder="<?php _e( 'Field id', 'custom-post-types' ); ?>" value="PLACEHOLDER_FIELD_ID">
            </label>
        </div>
        <div class="field-form-sx">
            <label><?php _e( 'Field type', 'custom-post-types' ); ?>
                <select class="field-type" name="field_settings[fields][PLACEHOLDER_FIELD_ID][type]">
                    <option value="0"><?php _e( 'Field type...', 'custom-post-types' ); ?></option>
                    <?php
                    foreach($field_types_array as $type => $data){ ?>
                    <option value="<?php echo $type;?>"><?php echo $data['label']; ?></option>
                    <?php } ?>
                </select>
            </label>
        </div>
        <div class="field-form-dx field-options">

        </div>
        <div class="field-form-sx">
            <label><input type="checkbox" name="field_settings[fields][PLACEHOLDER_FIELD_ID][required]" value="1"> <?php _e( 'Required', 'custom-post-types' ); ?></label>
        </div>
        <div class="field-form-delete">
            <a href="#" title="<?php _e( 'delete', 'custom-post-types' ); ?>"><span class="dashicons dashicons-trash"></span></a>
        </div>
        <div class="field-form-duplicate">
            <a href="#" title="<?php _e( 'duplicate', 'custom-post-types' ); ?>"><span class="dashicons dashicons-admin-page"></span></a>
        </div>
    </div>


    <?php

    foreach($field_types_array as $type => $data){

        if( isset( $data['options'] ) ){
            $callback = $data['options']; ?>
    <div class="default-options-<?php echo $type;?>">
        <?php $callback('PLACEHOLDER_FIELD_ID'); ?>
    </div>
    <?php }

    }


    ?>


    <script>
        jQuery( document ).ready( function( $ ) {

            function get_field_markup(id = null){
                var field_id = "field_" + Math.random().toString(36).substring(7);
                var markup_el = '';
                var replace_id = '';
                if(!id){
                    markup_el = $('#<?php echo $this->cpt_ui_name;?>-default_field_markup').find('.default-field');
                    replace_id = 'PLACEHOLDER_FIELD_ID';
                } else {
                    markup_el = $('.inside').find('.wp-cpt-field-row#' + id);
                    replace_id = id;
                }
                var field_form_html = '<div class="wp-cpt-field-row field-form" id="' + field_id + '">' + markup_el.html() + '</div>';
                var new_field_form = field_form_html.replace(new RegExp(replace_id, 'g'), field_id);
                return new_field_form;                
            }

            function get_options_markup(type, id){
                var field_id = id;
                var markup_el = $('#<?php echo $this->cpt_ui_name;?>-default_field_markup').find('.default-options-' + type);
                if(!markup_el.length){
                    return '';
                }
                var replace_id = 'PLACEHOLDER_FIELD_ID';
                var new_field_form = markup_el.html().replace(new RegExp(replace_id, 'g'), field_id);
                return new_field_form;                
            }

            $( ".add-field" ).click(function(e) {
                e.preventDefault();
                var new_field_form = get_field_markup();
                $(new_field_form).insertBefore(this);
            });

            $('.inside').on('click', '.field-form-duplicate a', function(e){
                e.preventDefault();
                var field_form = $( this ).closest('.field-form');
                var field_form_id = field_form.attr("id");
                var new_field_form = get_field_markup(field_form_id);
                $(new_field_form).insertAfter( field_form );
            });

            $('.inside').on('click', '.field-form-delete a', function(e){
                e.preventDefault();
                var field_form = $( this ).closest('.field-form');
                var field_form_id = field_form.attr("id");
                var set_empty_field = '<input type="hidden" name="field_settings[fields][' + field_form_id + '][type]" value="0">';
                $(set_empty_field).insertAfter( field_form );
                $(field_form).remove();
            });

            $('.inside').on('change', '.field-form .field-type', function(){
                var input_type = $(this).val();
                var field_form = $( this ).closest('.field-form');
                var field_options_container = $( field_form ).find('.field-options');
                var field_id = $( field_form ).attr('id');
                var field_settings = get_options_markup(input_type,field_id);
                $(field_options_container).html(field_settings);
            });

        });
    </script>

</div>