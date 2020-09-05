<?php
global $post;

$meta = array_map(function($item) {
  return (count($item) === 1) ? $item[0] : $item;
}, get_post_meta($post->ID));
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
  <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
  <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-light">
  <main class="container py-5" style="max-width: 600px">
    <!-- Race details -->
    <div class="card mb-1">
      <div class="d-flex">
        <div class="p-5 bg-light">
          <h2 class="m-0 p-0"><?= $meta['price']; ?> €</h2>
        </div>
        <div class="card-body">
          <h5 class="card-title"><?= $post->post_title; ?></h5>
          <p class="card-text"><?= $meta['description']; ?></p>
        </div>
      </div>
    </div>
    <!-- /Race details -->

    <?php if (!empty($_GET['user_id'])): ?>
      <!-- Payment form -->
      <form id="payment-form" class="mb-1">
        <div id="card-element" class="form-control"></div>
        <button id="submit" class="btn btn-outline-primary w-100 mt-1">
          <div id="spinner" class="spinner-border text-primary" role="status"></div>
          <span id="button-text">Pagar</span>
        </button>
      </form>
      <p id="message" class="alert" style="background: none; border: 0;"></p>
      <!-- /Payment form -->
    <?php endif; ?>
  </main>

  <script type="text/javascript">
  jQuery(document).ready(function() {
    var stripe = Stripe('<?= get_option('stripe_settings')['STRIPE_PUBLIC_KEY']; ?>');
    var $form = jQuery("#payment-form").hide();
    var $submitButton = jQuery('button').attr('disabled', true);
    var $submitButtonText = $submitButton.find('#button-text');
    var $spinner = jQuery("#spinner").hide();
    var $message = jQuery('#message');

    /**
     * Create payment intent
     * based on user data
     */
    jQuery.ajax('/wp-json/wp/v2/payment_intents', {
      method: 'POST',
      data: JSON.stringify({ user_id: <?= @$_GET['user_id']; ?>, race_id: <?= $post->ID; ?> }),
      headers: { 'Content-type': 'application/json' },
      error: function(response) {
        if (response.responseJSON && response.responseJSON.message) {
          showError(response.responseJSON.message);
        }
      },
      success: function(response) {
        var elements = stripe.elements();
        var style = { base: { color: "#32325d", fontSize: "16px" }, invalid: { color: "#fa755a" } };
        var card = elements.create("card", { style: style });

        // show payment elements
        $form.show();

        // Stripe injects an iframe into the DOM
        card.mount("#card-element");

        // Card "on change" event
        card.on("change", function (event) {
          $submitButton.attr('disabled', event.empty);
          $message.addClass('alert-danger').removeClass('alert-success').text(event.error ? event.error.message : '');
        });

        // Form "on submit" event
        $form.on("submit", function(event) {
          event.preventDefault();
          // Complete payment when the submit button is clicked
          payWithCard(stripe, card, response.client_secret);
        });
      }
    });

    // Calls stripe.confirmCardPayment
    // If the card requires authentication Stripe shows a pop-up modal to
    // prompt the user to enter authentication details without leaving your page.
    var payWithCard = function(stripe, card, client_secret) {
      loading(true);
      stripe.confirmCardPayment(client_secret, {
        payment_method: { card: card }
      }).then(function(result) {
        if (result.error) showError(result.error.message);
        else orderComplete(result.paymentIntent.id);
      });
    };

    // Shows a success message when the payment is complete
    var orderComplete = function(paymentIntentId) {
      loading(false);
      $message.addClass('alert-success').removeClass('alert-danger').text('El pago se ha realizado con éxito');
      $submitButton.attr('disabled', true);
      $form.hide();

      jQuery.ajax('/wp-json/wp/v2/payment_intents/' + paymentIntentId, {
        method: 'GET',
        success: console.log,
        error: console.log
      });
    };

    // Show the customer the error from Stripe if their card fails to charge
    var showError = function(errorMsgText) {
      loading(false);
      $message.addClass('alert-danger').removeClass('alert-success').text(errorMsgText);
    };

    // Show a spinner on payment submission
    var loading = function(isLoading) {
      $submitButton.attr('disabled', isLoading);
      $spinner[isLoading ? 'show' : 'hide']();
      $submitButtonText[isLoading ? 'hide' : 'show']();
    };
  });
  </script>
</body>
</html>
