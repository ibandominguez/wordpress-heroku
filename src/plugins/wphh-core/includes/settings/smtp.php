<?php

/**
 * @action
 *
 * Settings page config
 */
add_action('admin_menu', function() {
  add_options_page('Settings Admin', 'SMTP', 'manage_options', 'smtp-settings-page', function() { ?>
    <div class="wrap">
      <h1>Configure your smtp account</h1>
      <form method="post" action="options.php">
        <?php settings_fields('smtp_group'); ?>
        <?php do_settings_sections('smtp-settings-page'); ?>
        <?php submit_button(); ?>
      </form>
    </div>
  <?php });
});

/**
 * @filter
 *
 * Add links to settings page
 */
add_filter('plugin_action_links_'.plugin_basename(__FILE__), function($links) {
  $settings_link = '<a href="options-general.php?page=smtp-settings-page.php">Settings</a>';
  array_unshift($links, $settings_link);
  return $links;
});

/**
 * @action
 *
 * Settings page fields
 */
add_action('admin_init', function() {
  $options = get_option('smtp_settings');

  register_setting('smtp_group', 'smtp_settings', function($input) {
    return array_map(function($item) {
      return sanitize_text_field($item);
    }, $input);
  });

  add_settings_section(
    'smtp_settings', // ID
    'SMTP Settings', // Title
    null, // Callback
    'smtp-settings-page' // Page
  );

  foreach (['Host', 'Username', 'Password', 'From', 'FromName'] as $key):
    add_settings_field($key, $key, function() use ($key, $options) { ?>
      <input type="text" name="smtp_settings[<?= $key; ?>]" value="<?= @$options[$key]; ?>">
    <?php }, 'smtp-settings-page', 'smtp_settings');
  endforeach;

  add_settings_field('Port', 'Port', function() use ($options) { ?>
    <input type="number" name="smtp_settings[Port]" value="<?= @$options['Port']; ?>">
  <?php }, 'smtp-settings-page', 'smtp_settings');

  add_settings_field('SMTPSecure', 'SMTPSecure', function() use ($options) { ?>
    <select name="smtp_settings[SMTPSecure]">
      <?php foreach (['none', 'ssl', 'tls'] as $value): ?>
        <option value="<?= $value; ?>" <?= @$options['SMTPSecure'] === $value ? 'selected': ''; ?>>
          <?= $value; ?>
        </option>
      <?php endforeach; ?>
    </select>
  <?php }, 'smtp-settings-page', 'smtp_settings');

  add_settings_field('SMTPAuth', 'SMTPAuth', function() use ($options) { ?>
    <input type="checkbox" name="smtp_settings[SMTPAuth]" value="1" <?= checked(1, @$options['SMTPAuth'], false); ?>>
  <?php }, 'smtp-settings-page', 'smtp_settings');
});

/**
 * @action
 *
 * Overwrite phpmailer settings
 */
add_action('phpmailer_init', function($phpmailer) {
  $options = get_option('smtp_settings');
  $options['SMTPAuth'] = (bool) $options['SMTPAuth'];
  $options['Port'] = (int) $options['Port'];

  if (!empty($options)):
    $phpmailer->isSMTP();
    $phpmailer->SMTPAutoTLS = false;

    foreach ($options as $key => $value):
      if (!empty($value)):
        $phpmailer->{$key} = $value;
      endif;
    endforeach;
  endif;
});
