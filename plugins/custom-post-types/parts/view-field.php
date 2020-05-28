<?php

wp_nonce_field( 'custom_inner_metabox', 'custom_metabox_nonce' );

$metabox_id = explode('_',$metabox['id'])[0];
$custom_metabox = get_post($metabox_id);
$all_fields = get_post_meta( $custom_metabox->ID, '_field_settings_meta')[0]['fields'] ? get_post_meta( $custom_metabox->ID, '_field_settings_meta')[0]['fields'] : array();

foreach($all_fields as $key => $field){

    $type = isset($field['type']) ? $field['type'] : null;
    $label = isset($field['name']) ? $field['name'] : null;
    $id = isset($field['id']) ? $field['id'] : $key;
    $required = isset($field['required']) ? ' required' : '';
    $options = isset($field['options']) ? $field['options'] : array();

    $value = metadata_exists('post', $post->ID, $id) ? get_post_meta( $post->ID, $id, true ) : false;
    if(!$value){
        $value = isset(get_post_meta( $post->ID, '_custom_meta')[0][$id]['value']) ? get_post_meta( $post->ID, '_custom_meta')[0][$id]['value'] : '';
    }
    if(!$value){
        $value = isset(get_post_meta( $post->ID, '_custom_meta')[0][$id]) ? get_post_meta( $post->ID, '_custom_meta')[0][$id] : '';
    }

    if($label){

?>

<div class="wp-field-row">
    <label for="<?php echo $id;?>_field" title="<?php echo $label; ?>">
        <?php echo $label; ?>
    </label>
    <?php if($type == 'text'){ ?>
    <input type="text" id="<?php echo $id;?>_field" name="custom_field[<?php echo $id;?>]" value="<?php echo $value;?>"<?php echo $required;?>/>
    <?php } elseif($type == 'number'){ ?>
    <input type="number" id="<?php echo $id;?>_field" name="custom_field[<?php echo $id;?>]" value="<?php echo $value;?>"<?php echo $required;?>/>
    <?php } elseif($type == 'email'){ ?>
    <input type="email" id="<?php echo $id;?>_field" name="custom_field[<?php echo $id;?>]" value="<?php echo $value;?>"<?php echo $required;?>/>
    <?php } elseif($type == 'textarea'){ ?>


    <!--<textarea id="<?php echo $id;?>_field" name="custom_field[<?php echo $id;?>]"<?php echo $required;?>><?php echo $value;?></textarea>-->

    <?php
                                        wp_editor( $value , $id.'_field', array(
                                            'wpautop'       => true,
                                            'media_buttons' => true,
                                            'textarea_name' => 'custom_field['.$id.']',
                                            'editor_class'  => false,
                                            'textarea_rows' => 10,
                                            'teeny' => false,
                                            'quicktags' => true
                                        ) );
    ?>


    <?php } elseif($type == 'dropdown'){ ?>
    <select id="<?php echo $id;?>_field" name="custom_field[<?php echo $id;?>]"<?php echo $required;?>>
        <option value=""><?php _e( '- Choose -', 'custom-post-types' ); ?></option>
        <?php
                                        $options = explode(PHP_EOL, $options);    
                                        foreach($options as $option){ ?>
        <option value="<?php echo str_replace(array("\r", "\n"), '', $option); ?>"<?php selected( str_replace(array("\r", "\n"), '', $value), str_replace(array("\r", "\n"), '', $option)); ?>><?php echo str_replace(array("\r", "\n"), '', $option); ?></option>
        <?php } ?>
    </select>
    <?php } elseif($type == 'date'){ ?>
    <input type="date" id="<?php echo $id;?>_field" name="custom_field[<?php echo $id;?>]" value="<?php echo $value;?>"<?php echo $required;?>/>
    <?php } elseif($type == 'time'){ ?>
    <input type="time" id="<?php echo $id;?>_field" name="custom_field[<?php echo $id;?>]" value="<?php echo $value;?>"<?php echo $required;?>/>
    <?php } elseif($type == 'file'){ ?>
    <div class="file-uploader-field">
        <input id="<?php echo $id;?>_field" type="text" name="custom_field[<?php echo $id;?>]" value="<?php echo $value;?>"<?php echo $required;?>/>
        <input type="button" class="button-primary" value="<?php _e( 'Select', 'custom-post-types' ); ?>" data-file="<?php echo $id;?>_field" />
    </div>

    <?php } elseif($type == 'taxonomy_relationship'){ ?>
    <select id="<?php echo $id;?>_field" name="custom_field[<?php echo $id;?>]"<?php echo $required;?>>
        <option value=""><?php _e( '- Choose -', 'custom-post-types' ); ?></option>
        <?php
                                                     $taxonomy = taxonomy_exists($options) ? $options : false;
                                                     if( $taxonomy && !empty($taxonomy) ){
                                                         $terms = get_terms( array(
                                                             'taxonomy' => $taxonomy,
                                                             'hide_empty' => false,
                                                         ) );  

                                                         foreach($terms as $term){ ?>
        <option value="<?php echo $term->term_id; ?>"<?php selected( $value, $term->term_id ); ?>><?php echo $term->name; ?></option>
        <?php }
                                                     }
        ?>
    </select>

    <?php } elseif($type == 'post_relationship'){ ?>
    <select id="<?php echo $id;?>_field" name="custom_field[<?php echo $id;?>]"<?php echo $required;?>>
        <option value=""><?php _e( '- Choose -', 'custom-post-types' ); ?></option>
        <?php
                                                 $post_type_slug = post_type_exists( $options ) ? $options : false;
                                                 if( $post_type_slug && !empty($post_type_slug) ){
                                                     $post_list = get_posts( array( 'posts_per_page' => -1, 'post_type'  => $post_type_slug, 'post_status' => 'any' ) );

                                                     foreach($post_list as $post_rel){ ?>
        <option value="<?php echo $post_rel->ID; ?>"<?php selected( $value, $post_rel->ID ); ?>><?php echo $post_rel->post_title; ?></option>
        <?php }
                                                 }
        ?>
    </select>

    <?php }

               do_action('view_field_types',$id,$type,$value,$required,$options);

    ?>
</div>

<?php }}