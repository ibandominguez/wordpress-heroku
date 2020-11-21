<?php
global $post;

$meta = array_map(function($item) {
  return (count($item) === 1) ? $item[0] : $item;
}, get_post_meta($post->ID));

if (!empty($meta['stripe_product'])):
  $response = rest_do_request(new WP_REST_Request('GET', "/wp/v2/products/{$meta['stripe_product']}/prices"));
  $prices = !empty($response->data) ? $response->data : [];
endif;

if (!empty($_GET['user_id']) && in_array($post->ID, get_user_meta($_GET['user_id'], 'race_payments'))):
  wp_die('<p style="text-align: center">Ya has pagado esta subscripción</p>');
endif;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
  <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
  <?php wp_head(); ?>
  <script src="https://js.stripe.com/v3/"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
</head>
<body class="bg-light d-flex align-items-center justify-content-center">

  <?php if (isset($_GET['success'])): ?>
    <p class="alert alert-success my-5">EL pago se ha realizado con éxito</p>
  <?php else: ?>
    <div class="card mx-auto my-5" style="max-width: 450px; width: 100%">
      <?php if (has_post_thumbnail()) : ?>
        <?php the_post_thumbnail('thumbnail', ['class' => 'w-100', 'style' => 'height: auto']); ?>
      <?php endif; ?>
      <div class="card-body">
        <h5 class="card-title"><?= $post->post_title; ?></h5>
        <p class="card-text"><?= $meta['description']; ?></p>
        <?php if (!empty($_GET['user_id'])): ?>
          <ul class="list-group">
            <?php foreach ($prices as $price): ?>
              <li style="cursor: pointer" onclick="checkout('<?= $price->id; ?>')" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <?= !empty($price->nickname) ? $price->nickname : 'Inscripción'; ?>
                <span class="badge badge-primary badge-pill">
                  <?= number_format($price->unit_amount / 100, 2); ?> <?= $price->currency; ?>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($_GET['user_id'])): ?>
  <script type="text/javascript">
  var stripe = Stripe('<?= get_option('stripe_settings')['STRIPE_PUBLIC_KEY']; ?>');

  function checkout (priceId) {
    return jQuery.ajax({
      url: '/wp-json/wp/v2/checkouts',
      method: 'POST',
      data: JSON.stringify({ price_id: priceId, race_id: <?= $post->ID; ?>, user_id: <?= @$_GET['user_id']; ?> }),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      success: function (response) {
        stripe.redirectToCheckout({ sessionId: response.session_id }).then(function (response) {
          console.log(response);
        }).catch(function (response) {
          console.log(response);
        });
      },
      error: function (response) {
        console.log(response);
      }
    });
  };
  </script>
  <?php endif; ?>
</body>
</html>
