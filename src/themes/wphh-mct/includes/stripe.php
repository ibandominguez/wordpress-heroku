<?php

/**
 * @action
 *
 * Settings page config
 */
add_action('admin_menu', function() {
  add_options_page('Stripe settings', 'Stripe', 'manage_options', 'stripe-settings', function() { ?>
    <div class="wrap">
      <h1>Configure your stripe credentials</h1>
      <form method="post" action="options.php">
        <?php settings_fields('stripe_settings_group'); ?>
        <?php do_settings_sections('stripe-settings'); ?>
        <?php submit_button(); ?>
      </form>
    </div>
  <?php });
});

/**
 * @action
 *
 * Settings page fields
 */
add_action('admin_init', function() {
  $options = get_option('stripe_settings');

  register_setting('stripe_settings_group', 'stripe_settings', function($input) {
    return array_map(function($item) {
      return sanitize_text_field($item);
    }, $input);
  });

  add_settings_section(
    'stripe_settings', // ID
    'Stripe settings', // Title
    null, // Callback
    'stripe-settings' // Page
  );

  foreach (['STRIPE_PUBLIC_KEY', 'STRIPE_PRIVATE_KEY'] as $key):
    add_settings_field($key, $key, function() use ($key, $options) { ?>
      <input type="text" placeholder="Enter your key" name="stripe_settings[<?= $key; ?>]" value="<?= @$options[$key]; ?>">
    <?php }, 'stripe-settings', 'stripe_settings');
  endforeach;
});
