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
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
  <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
  <?php wp_head(); ?>
</head>
<body class="font-sans flex items-center justify-center h-screen bg-gray-100">

  <div class="rounded-sm shadow-lg bg-white">
    <div class="flex items-center p-5">
      <?php if (has_post_thumbnail()) : ?>
        <?php the_post_thumbnail('thumbnail', ['class' => 'h-20 w-auto']); ?>
      <?php endif; ?>
      <div class="p-3">
        <h2 class="text-2xl"><?= $post->post_title; ?></h2>
        <p><?= $meta['description']; ?></p>
      </div>
    </div>

    <?php if (!empty($_GET['user_id'])): ?>
      <div class="bg-gray-100 p-5">
        <select id="select" class="w-full p-3 rounded-xl shadow-xl">
          <?php foreach ($prices as $price): ?>
            <option value="<?= $price->id; ?>">
              <?= number_format($price->unit_amount / 100, 2); ?> <?= $price->currency; ?> · <?= !empty($price->nickname) ? $price->nickname : 'Inscripción'; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="p-5 bg-gray-300">
        <button onclick="checkout(jQuery('#select').val())" class="p-4 shadow-md rounded-full flex items-center mx-auto bg-white hover:bg-gray-500 hover:text-white transition-all duration-500">
          <span class="material-icons mr-2">shopping_cart</span>
          Proceder al pago
        </button>
      </div>
    <?php endif; ?>
  </div>

  <?php if (!empty($_GET['user_id'])): ?>
  <script src="https://js.stripe.com/v3/"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
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
          alert(JSON.stringify(response));
        });
      },
      error: function (response) {
        alert(JSON.stringify(response));
      }
    });
  };
  </script>
  <?php endif; ?>
</body>
</html>
