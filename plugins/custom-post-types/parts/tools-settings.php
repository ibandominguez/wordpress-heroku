<?php
$plugin_name = "Custom post types";
$plugin_banner_url = "https://ps.w.org/custom-post-types/assets/banner-772x250.png";

$author_name = "Andrea De Giovine";
$author_url = "https://www.andreadegiovine.it/?utm_source=tools_plugin_page&amp;utm_medium=plugin_details&amp;utm_campaign=custom_post_types";
?>
<div class="wp-cpt-plugin-banner">
    <div class="details">
        <h2 class="plugin-title"><?php echo $plugin_name;?></h2>
        <span class="plugin-author"><?php _e('By ', 'custom-post-types' ); ?><a title="<?php _e('WordPress plugin developer', 'custom-post-types' ); ?>" href="<?php echo $author_url;?>" target="_blank"><?php echo $author_name;?></a></span>
        <ul>
            <li><a href="https://wordpress.org/plugins/custom-post-types/" target="_blank"><?php _e('WordPress.org Plugin Page', 'custom-post-types' ); ?></a></li>
            <li><a href="https://wordpress.org/support/plugin/custom-post-types/" target="_blank"><?php _e('View support forum', 'custom-post-types' ); ?></a></li>
            <li><a href="https://wordpress.org/support/plugin/custom-post-types/reviews/#new-post" target="_blank"><?php _e('Add my review', 'custom-post-types' ); ?></a></li>
        </ul>
        <p>
            <a class="button button-secondary" href="https://www.andreadegiovine.it/outlinks/1422/?utm_source=tools_plugin_page&amp;utm_medium=plugin_page&amp;utm_campaign=custom_post_types" target="_blank"><?php _e('Donate to this plugin', 'custom-post-types' ); ?></a></p>
        <p style="margin-bottom: 0;">
            <?php echo $this->return_pro_button();?>
        </p>
    </div>
    <div class="banner"></div>
</div>