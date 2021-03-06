<?php

// TODO: Add language support and translate theme

add_action('init', function () {
  require_once __DIR__.'/includes/types/race.php';
  require_once __DIR__.'/includes/types/session.php';
  require_once __DIR__.'/includes/types/attachment.php';
  require_once __DIR__.'/includes/roles/runner.php';
  require_once __DIR__.'/includes/settings/stripe.php';
});

add_action('rest_api_init', function () {
  require_once __DIR__.'/includes/api/routes/payments.php';
  require_once __DIR__.'/includes/api/routes/users.php';
  require_once __DIR__.'/includes/api/routes/inscriptions.php';
  require_once __DIR__.'/includes/api/fields/user.php';
  require_once __DIR__.'/includes/api/fields/race.php';
  require_once __DIR__.'/includes/api/fields/session.php';
});
