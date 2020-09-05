<?php
global $post;

$meta = array_map(function($item) {
  if (count($item) === 1):
    $item = $item[0];
  endif;
  return $item;
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
    <!-- TODO: race details -->
    <div class="card mb-1">
      <div class="d-flex">
        <img src="https://fakeimg.pl/100x100/?text=<?= $meta['price']; ?>€&font=Roboto">
        <div class="card-body">
          <h5 class="card-title"><?= $post->post_title; ?></h5>
          <p class="card-text"><?= $meta['description']; ?></p>
        </div>
      </div>
    </div>
    <!-- end TODO: race details -->

    <!-- Payment form -->
    <form id="payment-form">
      <div id="payment-elements">
        <div id="card-element" class="form-control"></div>
        <button id="submit" class="btn btn-outline-primary w-100 mt-1">
          <div id="spinner" class="spinner-border text-primary" role="status"></div>
          <span id="button-text">Pagar</span>
        </button>
      </div>
      <p id="card-error" role="alert" class="alert"></p>
      <p class="alert alert-success result-message hidden">Pago completado correctamente</p>
    </form>
    <!-- Payment form -->
  </main>

  <script type="text/javascript">
  jQuery(document).ready(function() {
    var stripe = Stripe('<?= get_option('stripe_settings')['STRIPE_PUBLIC_KEY']; ?>');
    var $form = jQuery("#payment-form");
    var $submitButton = jQuery('button').attr('disabled', true);
    var $submitButtonText = $submitButton.find('#button-text');
    var $resultMessage = jQuery(".result-message").hide();
    var $spinner = jQuery("#spinner").hide();
    var $cardError = jQuery('#card-error');
    var $paymentElements = jQuery('#payment-elements').hide();

    /**
     * Create payment intent
     * based on user data
     */
    jQuery.ajax('/wp-json/wp/v2/payment_intents', {
      method: 'POST',
      data: JSON.stringify({ user_id: 1, race_id: <?= $post->ID; ?> }),
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
        $paymentElements.show();

        // Stripe injects an iframe into the DOM
        card.mount("#card-element");

        // Card "on change" event
        card.on("change", function (event) {
          $submitButton.attr('disabled', event.empty);
          $cardError.text(event.error ? event.error.message : '');
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
      $resultMessage.show();
      $submitButton.attr('disabled', true);
      $paymentElements.hide();

      jQuery.ajax('/wp-json/wp/v2/payment_intents/' + paymentIntentId, {
        method: 'GET',
        success: console.log,
        error: console.log
      });
    };

    // Show the customer the error from Stripe if their card fails to charge
    var showError = function(errorMsgText) {
      loading(false);
      $cardError.text(errorMsgText);
      setTimeout($cardError.empty.bind($cardError), 4000);
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
