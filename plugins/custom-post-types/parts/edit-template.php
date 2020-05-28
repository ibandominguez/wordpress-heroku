<?php
            wp_nonce_field( 'template_ui_inner_metabox', 'template_ui_metabox_nonce' );
            $template_settings = isset(get_post_meta( $post->ID, '_template_settings_meta')[0]) ? get_post_meta( $post->ID, '_template_settings_meta')[0] : array();

            $template_used_by = isset($template_settings['field_used_by']) ? $template_settings['field_used_by'] : '';
?>
<?php if(empty($template_used_by)){ ?>
<p><?php _e( 'Choose the type of content where you want to use the custom template, then click on the save or update button (for Gutenberg you must also refresh the page to show avaiable custom fields).', 'custom-post-types' ); ?></p>
<?php } ?>
<div class="wp-field-row">
    <label for="used_by" title="<?php _e( 'Used by', 'custom-post-types' ); ?>"><?php _e( 'Used by', 'custom-post-types' ); ?></label>
    <select id="used_by" name="template_settings[field_used_by]" onchange="set_cpt_fields('used_by');">
        <option value=""><?php _e( '- Choose -', 'custom-post-types' ); ?></option>
        <optgroup label="<?php _e( 'Post types', 'custom-post-types' ); ?>">
            <option value="post"<?php selected( $template_used_by, 'post' ); ?>><?php _e( 'Posts', 'custom-post-types' ); ?></option>
            <option value="page"<?php selected( $template_used_by, 'page' ); ?>><?php _e( 'Pages', 'custom-post-types' ); ?></option>
            <?php
            $ignore_post_type = array();
            $all_custom_templates = get_posts( array( 'posts_per_page' => -1, 'post_type' => $this->cpt_ui_name . '_template' ) );
            foreach($all_custom_templates as $template){
                $template_settings = get_post_meta( $template->ID, '_template_settings_meta')[0];
                if(!empty($template_settings['field_used_by']) && $template->ID != get_the_ID()){
                    $ignore_post_type[] = $template_settings['field_used_by'];
                }
            }


            $created_post_types = get_posts( array('posts_per_page' => -1, 'post_type' => $this->cpt_ui_name) );

            foreach($created_post_types as $post_type){

                $cpt_settings = get_post_meta( $post_type->ID, '_cpt_settings_meta')[0];
                
                $cpt_id = isset($cpt_settings['cpt_id']) && !empty($cpt_settings['cpt_id']) ? $cpt_settings['cpt_id'] : 'cpt_' . $post_type->ID;
                
                if(!in_array($cpt_id, $ignore_post_type)){
                    echo '<option value="'.$cpt_id.'" '.selected( $template_used_by, $cpt_id ).'>'.$post_type->post_title.'</option>';
                }
            } 
            ?>
        </optgroup>
    </select>
</div>
<?php if(!empty($template_used_by)){ 


                $all_custom_metaboxes = get_posts( array( 'posts_per_page' => -1, 'post_type' => $this->cpt_ui_name . '_field' ) );
                foreach($all_custom_metaboxes as $metaboxes){

                    $field_settings = get_post_meta( $metaboxes->ID, '_field_settings_meta')[0];
                    $fields = isset($field_settings['fields']) ? $field_settings['fields'] : array();
                    if(isset($field_settings['field_post_types']) && in_array($template_used_by, $field_settings['field_post_types'])){
                        echo "<hr><p>".__( 'Add the custom fields you want to use when creating / editing the associated content types.<br>
To display the field in your template use the shortcode [custom-field id="field_XXXX"] that appears next to the created field.', 'custom-post-types' )."</p>";
                        echo "<p style=\"text-align: center; text-transform: uppercase;\"><strong>".__( 'Default field', 'custom-post-types' )."</strong></p>";
                        echo '<p>
    <label for="custom_field_title" title="'.__( 'Post title', 'custom-post-types' ).'"><strong>'.__( 'Post title', 'custom-post-types' ).'</strong></label><br>
    <input type="text" class="shortcode-custom-fields" value=\'[custom-field id="title"]\' readonly="readonly" onfocus="this.select();" id="custom_field_title">
</p>';
                        echo '<p>
    <label for="custom_field_content" title="'.__( 'Post content', 'custom-post-types' ).'"><strong>'.__( 'Post content', 'custom-post-types' ).'</strong></label><br>
    <input type="text" class="shortcode-custom-fields" value=\'[custom-field id="content"]\' readonly="readonly" onfocus="this.select();" id="custom_field_content">
</p>';
                        echo '<p>
    <label for="custom_field_excerpt" title="'.__( 'Post excerpt', 'custom-post-types' ).'"><strong>'.__( 'Post excerpt', 'custom-post-types' ).'</strong></label><br>
    <input type="text" class="shortcode-custom-fields" value=\'[custom-field id="excerpt"]\' readonly="readonly" onfocus="this.select();" id="custom_field_excerpt">
</p>';
                        echo '<p>
    <label for="custom_field_image" title="'.__( 'Post image', 'custom-post-types' ).'"><strong>'.__( 'Post image', 'custom-post-types' ).'</strong></label><br>
    <input type="text" class="shortcode-custom-fields" value=\'[custom-field id="image"]\' readonly="readonly" onfocus="this.select();" id="custom_field_image">
</p>';
                        echo '<p>
    <label for="custom_field_date" title="'.__( 'Post date', 'custom-post-types' ).'"><strong>'.__( 'Post date', 'custom-post-types' ).'</strong></label><br>
    <input type="text" class="shortcode-custom-fields" value=\'[custom-field id="date"]\' readonly="readonly" onfocus="this.select();" id="custom_field_date">
</p>';
                        echo '<p>
    <label for="custom_field_author" title="'.__( 'Post author', 'custom-post-types' ).'"><strong>'.__( 'Post author', 'custom-post-types' ).'</strong></label><br>
    <input type="text" class="shortcode-custom-fields" value=\'[custom-field id="author"]\' readonly="readonly" onfocus="this.select();" id="custom_field_author">
</p>';
                        echo "<p style=\"text-align: center; text-transform: uppercase;\"><strong>".__( 'Custom field', 'custom-post-types' )."</strong></p>";


                        foreach($fields as $id => $field){ if($field['type'] !== '0'){

?><p>
    <label for="custom_field_<?php echo $id;?>" title="<?php echo $field['name'];?>"><strong><?php echo $field['name'];?></strong></label><br>
    <input type="text" class="shortcode-custom-fields" value='[custom-field id="<?php echo $id;?>"]' readonly="readonly" onfocus="this.select();" id="custom_field_<?php echo $id;?>">
</p>
<?php } } } } } 