(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

/* globals iban Stripe wcv_sc_params StripeCheckout */
jQuery(function ($) {
  'use strict';

  try {
    var stripe = Stripe(wcv_sc_params.key);
  } catch (error) {
    // eslint-disable-next-line
    console.log(error);
    return;
  }

  var stripe_elements_options = Object.keys(wcv_sc_params.elements_options).length ? wcv_sc_params.elements_options : {},
      elements = stripe.elements(stripe_elements_options),
      stripe_card,
      stripe_exp,
      stripe_cvc;
  /**
   * Object to handle Stripe elements payment form.
   */

  var wcv_stripe_connect_form = {
    /**
     * Initialize event handlers and UI state.
     */
    init: function init() {
      // if ( 'yes' === wcv_sc_params.is_change_payment_page || 'yes' === wcv_sc_params.is_pay_for_order_page ) {
      // 	$( document.body ).trigger( 'wc-credit-card-form-init' );
      // }
      // Stripe Checkout.
      this.stripe_checkout_submit = false; // checkout page

      if ($('form.woocommerce-checkout').length) {
        this.form = $('form.woocommerce-checkout');
      }

      $('form.woocommerce-checkout').on('checkout_place_order_stripe-connect', this.onSubmit); // // pay order page

      if ($('form#order_review').length) {
        this.form = $('form#order_review');
      }

      $('form#order_review, form#add_payment_method').on('submit', this.onSubmit); // // add payment method page

      if ($('form#add_payment_method').length) {
        this.form = $('form#add_payment_method');
      }

      $('form.woocommerce-checkout').on('change', this.reset);
      $(document).on('stripeConnectError', this.onError).on('checkout_error', this.reset);
      wcv_stripe_connect_form.createElements();
      window.addEventListener('hashchange', wcv_stripe_connect_form.onHashChange); // Stripe Checkout

      if ('yes' === wcv_sc_params.is_stripe_checkout) {
        $(document.body).on('click', '#place_order', function () {
          wcv_stripe_connect_form.openModal();
          return false;
        });
      }
    },
    unmountElements: function unmountElements() {
      stripe_card.unmount('#stripe-connect-card-element');
      stripe_exp.unmount('#stripe-connect-exp-element');
      stripe_cvc.unmount('#stripe-connect-cvc-element');
    },
    mountElements: function mountElements() {
      if (!$('#stripe-connect-card-element').length) {
        return;
      }

      stripe_card.mount('#stripe-connect-card-element');
      stripe_exp.mount('#stripe-connect-exp-element');
      stripe_cvc.mount('#stripe-connect-cvc-element');
    },
    createElements: function createElements() {
      var elementStyles = {
        base: {
          iconColor: '#666EE8',
          color: '#31325F',
          fontSize: '15px',
          '::placeholder': {
            color: '#CFD7E0'
          }
        }
      };
      var elementClasses = {
        focus: 'focused',
        empty: 'empty',
        invalid: 'invalid'
      };
      stripe_card = elements.create('cardNumber', {
        style: elementStyles,
        classes: elementClasses
      });
      stripe_exp = elements.create('cardExpiry', {
        style: elementStyles,
        classes: elementClasses
      });
      stripe_cvc = elements.create('cardCvc', {
        style: elementStyles,
        classes: elementClasses
      });
      stripe_card.addEventListener('change', function (event) {
        wcv_stripe_connect_form.onCCFormChange();
        wcv_stripe_connect_form.updateCardBrand(event.brand);
        $('input.stripe-connect-source').remove();

        if (event.error) {
          $(document.body).trigger('stripeConnectError', event);
        }
      });
      stripe_exp.addEventListener('change', function (event) {
        wcv_stripe_connect_form.onCCFormChange();
        $('input.stripe-connect-source').remove();

        if (event.error) {
          $(document.body).trigger('stripeConnectError', event);
        }
      });
      stripe_cvc.addEventListener('change', function (event) {
        wcv_stripe_connect_form.onCCFormChange();
        $('input.stripe-connect-source').remove();

        if (event.error) {
          $(document.body).trigger('stripeConnectError', event);
        }
      });
      /**
       * Only in checkout page we need to delay the mounting of the
       * card as some AJAX process needs to happen before we do.
       */

      if ('yes' === wcv_sc_params.is_checkout) {
        $(document.body).on('updated_checkout', function () {
          // Don't mount elements a second time.
          if (stripe_card) {
            wcv_stripe_connect_form.unmountElements();
          }

          wcv_stripe_connect_form.mountElements();

          if ($('#stripe-iban-element').length) {
            iban.mount('#stripe-iban-element');
          }
        });
      } else if ($('form#add_payment_method').length || $('form#order_review').length) {
        wcv_stripe_connect_form.mountElements();

        if ($('#stripe-iban-element').length) {
          iban.mount('#stripe-iban-element');
        }
      }
    },
    onCCFormChange: function onCCFormChange() {
      wcv_stripe_connect_form.reset();
    },
    updateCardBrand: function updateCardBrand(brand) {
      var brandClass = {
        visa: 'stripe-visa-brand',
        mastercard: 'stripe-mastercard-brand',
        amex: 'stripe-amex-brand',
        discover: 'stripe-discover-brand',
        diners: 'stripe-diners-brand',
        jcb: 'stripe-jcb-brand',
        unknown: 'stripe-credit-card-brand'
      };
      var imageElement = $('.stripe-card-brand'),
          imageClass = 'stripe-credit-card-brand';

      if (brand in brandClass) {
        imageClass = brandClass[brand];
      } // Remove existing card brand class.


      $.each(brandClass, function (index, el) {
        imageElement.removeClass(el);
      });
      imageElement.addClass(imageClass);
    },
    // Stripe Checkout.
    openModal: function openModal() {
      // eslint-disable-next-line
      console.log('openModal'); // Capture submittal and open stripecheckout

      var $form = wcv_stripe_connect_form.form,
          $data = $('#stripe-connect-payment-data');
      wcv_stripe_connect_form.reset();

      var token_action = function token_action(res) {
        $form.find('input.stripe_source').remove();

        if ('token' === res.object) {
          stripe.createSource({
            type: 'card',
            token: res.id
          }).then(wcv_stripe_connect_form.sourceResponse);
        } else if ('source' === res.object) {
          var response = {
            source: res
          };
          wcv_stripe_connect_form.sourceResponse(response);
        }
      };

      StripeCheckout.open({
        key: wcv_sc_params.key,
        billingAddress: $data.data('billing-address'),
        zipCode: $data.data('verify-zip'),
        amount: $data.data('amount'),
        name: $data.data('name'),
        description: $data.data('description'),
        currency: $data.data('currency'),
        image: $data.data('image'),
        locale: $data.data('locale'),
        email: $('#billing_email').val() || $data.data('email'),
        panelLabel: $data.data('panel-label'),
        allowRememberMe: $data.data('allow-remember-me'),
        token: token_action,
        closed: wcv_stripe_connect_form.onClose()
      });
    },
    // Stripe Checkout.
    resetModal: function resetModal() {
      wcv_stripe_connect_form.reset();
      wcv_stripe_connect_form.stripe_checkout_submit = false;
    },
    // Stripe Checkout.
    onClose: function onClose() {
      wcv_stripe_connect_form.unblock();
    },
    unblock: function unblock() {
      wcv_stripe_connect_form.form.unblock();
    },
    // rest
    reset: function reset() {
      $('.wcv-stripe-connect-error, .stripeConnectError, .stripe_connect_token').remove(); // Stripe Checkout.

      if ('yes' === wcv_sc_params.is_stripe_checkout) {
        wcv_stripe_connect_form.stripe_submit = false;
      }
    },
    // Check to see if Stripe in general is being used for checkout.
    isStripeConnectChosen: function isStripeConnectChosen() {
      return $('#payment_method_stripe-connect').is(':checked') || $('#payment_method_stripe-connect').is(':checked') && 'new' === $('input[name="wc-stripe-payment-token"]:checked').val();
    },
    // Currently only support saved cards via credit cards and SEPA. No other payment method.
    isStripeSaveCardChosen: function isStripeSaveCardChosen() {
      return $('#payment_method_stripe-connect').is(':checked') && $('input[name="wc-stripe-connect-payment-token"]').is(':checked') && 'new' !== $('input[name="wc-stripe-connect-payment-token"]:checked').val();
    },
    // Stripe credit card used.
    isStripeCardChosen: function isStripeCardChosen() {
      return $('#payment_method_stripe').is(':checked');
    },
    hasSource: function hasSource() {
      return 0 < $('input.stripe-connect-source').length;
    },
    // Legacy
    hasToken: function hasToken() {
      return 0 < $('input.stripe_connect_token').length;
    },
    isMobile: function isMobile() {
      if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        return true;
      }

      return false;
    },
    isStripeModalNeeded: function isStripeModalNeeded() {
      var token = wcv_stripe_connect_form.form.find('input.stripe_connect_token'); // If this is a stripe submission (after modal) and token exists, allow submit.

      if (wcv_stripe_connect_form.stripe_submit && token) {
        return false;
      } // Don't affect submission if modal is not needed.


      if (!wcv_stripe_connect_form.isStripeChosen()) {
        return false;
      }

      return true;
    },
    block: function block() {
      if (!wcv_stripe_connect_form.isMobile()) {
        wcv_stripe_connect_form.form.block({
          message: null,
          overlayCSS: {
            background: '#fff',
            opacity: 0.6
          }
        });
      }
    },
    // Get the customer details
    getCustomerDetails: function getCustomerDetails() {
      var first_name = $('#billing_first_name').length ? $('#billing_first_name').val() : wcv_sc_params.billing_first_name,
          last_name = $('#billing_last_name').length ? $('#billing_last_name').val() : wcv_sc_params.billing_last_name,
          extra_details = {
        owner: {
          name: '',
          address: {},
          email: '',
          phone: ''
        }
      };
      extra_details.owner.name = first_name;

      if (first_name && last_name) {
        extra_details.owner.name = first_name + ' ' + last_name;
      } else {
        extra_details.owner.name = $('#stripe-payment-data').data('full-name');
      }

      extra_details.owner.email = $('#billing_email').val();
      extra_details.owner.phone = $('#billing_phone').val();
      /* Stripe does not like empty string values so
       * we need to remove the parameter if we're not
       * passing any value.
       */

      if ('undefined' === typeof extra_details.owner.phone || 0 >= extra_details.owner.phone.length) {
        delete extra_details.owner.phone;
      }

      if ('undefined' === typeof extra_details.owner.email || 0 >= extra_details.owner.email.length) {
        if ($('#stripe-connect-payment-data').data('email').length) {
          extra_details.owner.email = $('#stripe-connect-payment-data').data('email');
        } else {
          delete extra_details.owner.email;
        }
      }

      if ('undefined' === typeof extra_details.owner.name || 0 >= extra_details.owner.name.length) {
        delete extra_details.owner.name;
      }

      if (0 < $('#billing_address_1').length) {
        extra_details.owner.address.line1 = $('#billing_address_1').val();
        extra_details.owner.address.line2 = $('#billing_address_2').val();
        extra_details.owner.address.state = $('#billing_state').val();
        extra_details.owner.address.city = $('#billing_city').val();
        extra_details.owner.address.postal_code = $('#billing_postcode').val();
        extra_details.owner.address.country = $('#billing_country').val();
      } else if (wcv_sc_params.billing_address_1) {
        extra_details.owner.address.line1 = wcv_sc_params.billing_address_1;
        extra_details.owner.address.line2 = wcv_sc_params.billing_address_2;
        extra_details.owner.address.state = wcv_sc_params.billing_state;
        extra_details.owner.address.city = wcv_sc_params.billing_city;
        extra_details.owner.address.postal_code = wcv_sc_params.billing_postcode;
        extra_details.owner.address.country = wcv_sc_params.billing_country;
      }

      return extra_details;
    },
    // Source Support
    createSource: function createSource() {
      var extra_details = wcv_stripe_connect_form.getCustomerDetails(); // Create the Stripe source

      stripe.createSource(stripe_card, extra_details).then(wcv_stripe_connect_form.sourceResponse);
    },
    sourceResponse: function sourceResponse(response) {
      if (response.error) {
        $(document.body).trigger('stripeConnectError', response);
      } else if ('no' === wcv_sc_params.allow_prepaid_card && 'card' === response.source.type && 'prepaid' === response.source.card.funding) {
        response.error = {
          message: wcv_sc_params.no_prepaid_card_msg
        };

        if ('yes' === wcv_sc_params.is_stripe_checkout) {
          wcv_stripe_connect_form.submitError('<ul class="woocommerce-error"><li>' + wcv_sc_params.no_prepaid_card_msg + '</li></ul>');
        } else {
          $(document.body).trigger('stripeConnectError', response);
        }
      } else {
        wcv_stripe_connect_form.processStripeResponse(response.source);
      }
    },
    processStripeResponse: function processStripeResponse(source) {
      wcv_stripe_connect_form.reset(); // Insert the Source into the form so it gets submitted to the server.

      wcv_stripe_connect_form.form.append("<input type='hidden' class='stripe-connect-source' name='stripe_connect_source' value='" + source.id + "'/>");

      if ($('form#add_payment_method').length) {
        $(wcv_stripe_connect_form.form).off('submit', wcv_stripe_connect_form.form.onSubmit);
      }

      wcv_stripe_connect_form.form.submit();
    },
    onSubmit: function onSubmit(e) {
      if (!wcv_stripe_connect_form.isStripeConnectChosen()) {
        return;
      }

      if (!wcv_stripe_connect_form.isStripeSaveCardChosen() && !wcv_stripe_connect_form.hasSource() && !wcv_stripe_connect_form.hasToken()) {
        e.preventDefault();
        wcv_stripe_connect_form.block(); // Stripe Checkout.

        if ('yes' === wcv_sc_params.is_stripe_checkout && wcv_stripe_connect_form.isStripeModalNeeded() && wcv_stripe_connect_form.isStripeCardChosen()) {
          if ('yes' === wcv_sc_params.is_checkout) {
            return true;
          } else {
            wcv_stripe_connect_form.openModal();
            return false;
          }
        }

        wcv_stripe_connect_form.createSource(); // Prevent form submitting

        return false;
      }
    },
    getSelectedPaymentElement: function getSelectedPaymentElement() {
      return $('.payment_methods input[name="payment_method"]:checked');
    },
    onError: function onError(e, result) {
      var message = result.error.message;
      var selectedMethodElement = wcv_stripe_connect_form.getSelectedPaymentElement().closest('li');
      var savedTokens = selectedMethodElement.find('.woocommerce-SavedPaymentMethods-tokenInput');
      var errorContainer;

      if (savedTokens.length) {
        // In case there are saved cards too, display the message next to the correct one.
        var selectedToken = savedTokens.filter(':checked');

        if (selectedToken.closest('.woocommerce-SavedPaymentMethods-new').length) {
          // Display the error next to the CC fields if a new card is being entered.
          errorContainer = $('#wcv-stripe-connect-cc-form .stripe-connect-source-errors');
        } else {
          // Display the error next to the chosen saved card.
          errorContainer = selectedToken.closest('li').find('.stripe-connect-source-errors');
        }
      } else {
        // When no saved cards are available, display the error next to CC fields.
        errorContainer = selectedMethodElement.find('.stripe-connect-source-errors');
      }
      /*
       * Customers do not need to know the specifics of the below type of errors
       * therefore return a generic localizable error message.
       */


      if ('invalid_request_error' === result.error.type || 'api_connection_error' === result.error.type || 'api_error' === result.error.type || 'authentication_error' === result.error.type || 'rate_limit_error' === result.error.type) {
        message = wcv_sc_params.invalid_request_error;
      }

      if ('card_error' === result.error.type && wcv_sc_params.hasOwnProperty(result.error.code)) {
        message = wcv_sc_params[result.error.code];
      }

      if ('validation_error' === result.error.type && wcv_sc_params.hasOwnProperty(result.error.code)) {
        message = wcv_sc_params[result.error.code];
      }

      wcv_stripe_connect_form.reset();
      $('.woocommerce-NoticeGroup-checkout').remove(); // eslint-disable-next-line

      console.log(result.error.message); // Leave for troubleshooting.

      errorContainer.html('<ul class="woocommerce_error woocommerce-error wcv-stripe-connect-error"><li>' + message + '</li></ul>');

      if ($('.wcv-stripe-connect-error').length) {
        $('html, body').animate({
          scrollTop: $('.wcv-stripe-connect-error').offset().top - 200
        }, 200);
      }

      wcv_stripe_connect_form.unblock();
    },
    submitError: function submitError(error_message) {
      $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
      wcv_stripe_connect_form.form.prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>');
      wcv_stripe_connect_form.form.removeClass('processing').unblock();
      wcv_stripe_connect_form.form.find('.input-text, select, input:checkbox').blur();
      var selector = '';

      if ($('#add_payment_method').length) {
        selector = $('#add_payment_method');
      }

      if ($('#order_review').length) {
        selector = $('#order_review');
      }

      if ($('form.checkout').length) {
        selector = $('form.checkout');
      }

      if (selector.length) {
        $('html, body').animate({
          scrollTop: selector.offset().top - 100
        }, 500);
      }

      $(document.body).trigger('checkout_error');
      wcv_stripe_connect_form.unblock();
    },

    /**
     * Handles changes in the hash in order to show a modal for PaymentIntent confirmations.
     *
     * Listens for `hashchange` events and checks for a hash in the following format:
     * #confirm-pi-<intentClientSecret>:<successRedirectURL>
     *
     * If such a hash appears, the partials will be used to call `stripe.handleCardAction`
     * in order to allow customers to confirm an 3DS/SCA authorization.
     *
     * Those redirects/hashes are generated in `WCV_SC_Payment_Gateway::generate_charges_transfers_payment`.
     */
    onHashChange: function onHashChange() {
      var partials = window.location.hash.match(/^#?confirm-pi-([^:]+):(.+)$/);

      if (!partials || 3 > partials.length) {
        return;
      }

      var intentClientSecret = partials[1];
      var redirectURL = decodeURIComponent(partials[2]); // Cleanup the URL

      window.location.hash = '';
      wcv_stripe_connect_form.openIntentModal(intentClientSecret, redirectURL);
    },

    /**
     * Opens the modal for PaymentIntent authorizations.
     *
     * @param {string}  intentClientSecret The client secret of the intent.
     * @param {string}  redirectURL        The URL to ping on fail or redirect to on success.
     * @param {boolean} alwaysRedirect     If set to true, an immediate redirect will happen no matter the result.
     *                                     If not, an error will be displayed on failure.
     */
    openIntentModal: function openIntentModal(intentClientSecret, redirectURL, alwaysRedirect) {
      stripe.handleCardAction(intentClientSecret).then(function (response) {
        if (response.error) {
          throw response.error;
        }

        if ('requires_confirmation' !== response.paymentIntent.status) {
          return;
        }

        window.location = redirectURL;
      }).catch(function (error) {
        if (alwaysRedirect) {
          return window.location = redirectURL;
        }

        $(document.body).trigger('stripeConnectError', {
          error: error
        });
        wcv_stripe_connect_form.form.removeClass('processing'); // Report back to the server.

        $.get(redirectURL + '&is_ajax');
      });
    }
  };
  wcv_stripe_connect_form.init();
});

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJhc3NldHMvc3JjL2pzL3N0cmlwZS1jaGVja291dC5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUE7QUFFQSxNQUFNLENBQUMsVUFBUyxDQUFULEVBQVk7QUFDbEI7O0FBRUEsTUFBSTtBQUNILFFBQUksTUFBTSxHQUFHLE1BQU0sQ0FBQyxhQUFhLENBQUMsR0FBZixDQUFuQjtBQUNBLEdBRkQsQ0FFRSxPQUFPLEtBQVAsRUFBYztBQUNmO0FBQ0EsSUFBQSxPQUFPLENBQUMsR0FBUixDQUFZLEtBQVo7QUFDQTtBQUNBOztBQUVELE1BQUksdUJBQXVCLEdBQUcsTUFBTSxDQUFDLElBQVAsQ0FBWSxhQUFhLENBQUMsZ0JBQTFCLEVBQzNCLE1BRDJCLEdBRTFCLGFBQWEsQ0FBQyxnQkFGWSxHQUcxQixFQUhKO0FBQUEsTUFJQyxRQUFRLEdBQUcsTUFBTSxDQUFDLFFBQVAsQ0FBZ0IsdUJBQWhCLENBSlo7QUFBQSxNQUtDLFdBTEQ7QUFBQSxNQU1DLFVBTkQ7QUFBQSxNQU9DLFVBUEQ7QUFTQTs7OztBQUdBLE1BQUksdUJBQXVCLEdBQUc7QUFDN0I7OztBQUdBLElBQUEsSUFBSSxFQUFFLGdCQUFXO0FBQ2hCO0FBQ0E7QUFDQTtBQUVBO0FBQ0EsV0FBSyxzQkFBTCxHQUE4QixLQUE5QixDQU5nQixDQVFoQjs7QUFDQSxVQUFJLENBQUMsQ0FBQywyQkFBRCxDQUFELENBQStCLE1BQW5DLEVBQTJDO0FBQzFDLGFBQUssSUFBTCxHQUFZLENBQUMsQ0FBQywyQkFBRCxDQUFiO0FBQ0E7O0FBRUQsTUFBQSxDQUFDLENBQUMsMkJBQUQsQ0FBRCxDQUErQixFQUEvQixDQUNDLHFDQURELEVBRUMsS0FBSyxRQUZOLEVBYmdCLENBa0JoQjs7QUFDQSxVQUFJLENBQUMsQ0FBQyxtQkFBRCxDQUFELENBQXVCLE1BQTNCLEVBQW1DO0FBQ2xDLGFBQUssSUFBTCxHQUFZLENBQUMsQ0FB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QUVFLElBRkYsQ0FFTyxVQUFTLFFBQVQsRUFBbUI7QUFDeEIsWUFBSSxRQUFRLENBQUMsS0FBYixFQUFvQjtBQUNuQixnQkFBTSxRQUFRLENBQUMsS0FBZjtBQUNBOztBQUVELFlBQ0MsNEJBQ0EsUUFBUSxDQUFDLGFBQVQsQ0FBdUIsTUFGeEIsRUFHRTtBQUNEO0FBQ0E7O0FBRUQsUUFBQSxNQUFNLENBQUMsUUFBUCxHQUFrQixXQUFsQjtBQUNBLE9BZkYsRUFnQkUsS0FoQkYsQ0FnQlEsVUFBUyxLQUFULEVBQWdCO0FBQ3RCLFlBQUksY0FBSixFQUFvQjtBQUNuQixpQkFBUSxNQUFNLENBQUMsUUFBUCxHQUFrQixXQUExQjtBQUNBOztBQUVELFFBQUEsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxJQUFWLENBQUQsQ0FBaUIsT0FBakIsQ0FBeUIsb0JBQXpCLEVBQStDO0FBQzlDLFVBQUEsS0FBSyxFQUFFO0FBRHVDLFNBQS9DO0FBR0EsUUFBQSx1QkFBdUIsQ0FBQyxJQUF4QixDQUE2QixXQUE3QixDQUF5QyxZQUF6QyxFQVJzQixDQVV0Qjs7QUFDQSxRQUFBLENBQUMsQ0FBQyxHQUFGLENBQU0sV0FBVyxHQUFHLFVBQXBCO0FBQ0EsT0E1QkY7QUE2QkE7QUF6dEI0QixHQUE5QjtBQTR0QkEsRUFBQSx1QkFBdUIsQ0FBQyxJQUF4QjtBQUNBLENBcHZCSyxDQUFOIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24oKXtmdW5jdGlvbiByKGUsbix0KXtmdW5jdGlvbiBvKGksZil7aWYoIW5baV0pe2lmKCFlW2ldKXt2YXIgYz1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlO2lmKCFmJiZjKXJldHVybiBjKGksITApO2lmKHUpcmV0dXJuIHUoaSwhMCk7dmFyIGE9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitpK1wiJ1wiKTt0aHJvdyBhLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsYX12YXIgcD1uW2ldPXtleHBvcnRzOnt9fTtlW2ldWzBdLmNhbGwocC5leHBvcnRzLGZ1bmN0aW9uKHIpe3ZhciBuPWVbaV1bMV1bcl07cmV0dXJuIG8obnx8cil9LHAscC5leHBvcnRzLHIsZSxuLHQpfXJldHVybiBuW2ldLmV4cG9ydHN9Zm9yKHZhciB1PVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmUsaT0wO2k8dC5sZW5ndGg7aSsrKW8odFtpXSk7cmV0dXJuIG99cmV0dXJuIHJ9KSgpIiwiLyogZ2xvYmFscyBpYmFuIFN0cmlwZSB3Y3Zfc2NfcGFyYW1zIFN0cmlwZUNoZWNrb3V0ICovXG5cbmpRdWVyeShmdW5jdGlvbigkKSB7XG5cdCd1c2Ugc3RyaWN0JztcblxuXHR0cnkge1xuXHRcdHZhciBzdHJpcGUgPSBTdHJpcGUod2N2X3NjX3BhcmFtcy5rZXkpO1xuXHR9IGNhdGNoIChlcnJvcikge1xuXHRcdC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZVxuXHRcdGNvbnNvbGUubG9nKGVycm9yKTtcblx0XHRyZXR1cm47XG5cdH1cblxuXHR2YXIgc3RyaXBlX2VsZW1lbnRzX29wdGlvbnMgPSBPYmplY3Qua2V5cyh3Y3Zfc2NfcGFyYW1zLmVsZW1lbnRzX29wdGlvbnMpXG5cdFx0XHQubGVuZ3RoXG5cdFx0XHQ/IHdjdl9zY19wYXJhbXMuZWxlbWVudHNfb3B0aW9uc1xuXHRcdFx0OiB7fSxcblx0XHRlbGVtZW50cyA9IHN0cmlwZS5lbGVtZW50cyhzdHJpcGVfZWxlbWVudHNfb3B0aW9ucyksXG5cdFx0c3RyaXBlX2NhcmQsXG5cdFx0c3RyaXBlX2V4cCxcblx0XHRzdHJpcGVfY3ZjO1xuXG5cdC8qKlxuXHQgKiBPYmplY3QgdG8gaGFuZGxlIFN0cmlwZSBlbGVtZW50cyBwYXltZW50IGZvcm0uXG5cdCAqL1xuXHR2YXIgd2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0gPSB7XG5cdFx0LyoqXG5cdFx0ICogSW5pdGlhbGl6ZSBldmVudCBoYW5kbGVycyBhbmQgVUkgc3RhdGUuXG5cdFx0ICovXG5cdFx0aW5pdDogZnVuY3Rpb24oKSB7XG5cdFx0XHQvLyBpZiAoICd5ZXMnID09PSB3Y3Zfc2NfcGFyYW1zLmlzX2NoYW5nZV9wYXltZW50X3BhZ2UgfHwgJ3llcycgPT09IHdjdl9zY19wYXJhbXMuaXNfcGF5X2Zvcl9vcmRlcl9wYWdlICkge1xuXHRcdFx0Ly8gXHQkKCBkb2N1bWVudC5ib2R5ICkudHJpZ2dlciggJ3djLWNyZWRpdC1jYXJkLWZvcm0taW5pdCcgKTtcblx0XHRcdC8vIH1cblxuXHRcdFx0Ly8gU3RyaXBlIENoZWNrb3V0LlxuXHRcdFx0dGhpcy5zdHJpcGVfY2hlY2tvdXRfc3VibWl0ID0gZmFsc2U7XG5cblx0XHRcdC8vIGNoZWNrb3V0IHBhZ2Vcblx0XHRcdGlmICgkKCdmb3JtLndvb2NvbW1lcmNlLWNoZWNrb3V0JykubGVuZ3RoKSB7XG5cdFx0XHRcdHRoaXMuZm9ybSA9ICQoJ2Zvcm0ud29vY29tbWVyY2UtY2hlY2tvdXQnKTtcblx0XHRcdH1cblxuXHRcdFx0JCgnZm9ybS53b29jb21tZXJjZS1jaGVja291dCcpLm9uKFxuXHRcdFx0XHQnY2hlY2tvdXRfcGxhY2Vfb3JkZXJfc3RyaXBlLWNvbm5lY3QnLFxuXHRcdFx0XHR0aGlzLm9uU3VibWl0XG5cdFx0XHQpO1xuXG5cdFx0XHQvLyAvLyBwYXkgb3JkZXIgcGFnZVxuXHRcdFx0aWYgKCQoJ2Zvcm0jb3JkZXJfcmV2aWV3JykubGVuZ3RoKSB7XG5cdFx0XHRcdHRoaXMuZm9ybSA9ICQoJ2Zvcm0jb3JkZXJfcmV2aWV3Jyk7XG5cdFx0XHR9XG5cblx0XHRcdCQoJ2Zvcm0jb3JkZXJfcmV2aWV3LCBmb3JtI2FkZF9wYXltZW50X21ldGhvZCcpLm9uKFxuXHRcdFx0XHQnc3VibWl0Jyxcblx0XHRcdFx0dGhpcy5vblN1Ym1pdFxuXHRcdFx0KTtcblxuXHRcdFx0Ly8gLy8gYWRkIHBheW1lbnQgbWV0aG9kIHBhZ2Vcblx0XHRcdGlmICgkKCdmb3JtI2FkZF9wYXltZW50X21ldGhvZCcpLmxlbmd0aCkge1xuXHRcdFx0XHR0aGlzLmZvcm0gPSAkKCdmb3JtI2FkZF9wYXltZW50X21ldGhvZCcpO1xuXHRcdFx0fVxuXG5cdFx0XHQkKCdmb3JtLndvb2NvbW1lcmNlLWNoZWNrb3V0Jykub24oJ2NoYW5nZScsIHRoaXMucmVzZXQpO1xuXG5cdFx0XHQkKGRvY3VtZW50KVxuXHRcdFx0XHQub24oJ3N0cmlwZUNvbm5lY3RFcnJvcicsIHRoaXMub25FcnJvcilcblx0XHRcdFx0Lm9uKCdjaGVja291dF9lcnJvcicsIHRoaXMucmVzZXQpO1xuXG5cdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5jcmVhdGVFbGVtZW50cygpO1xuXG5cdFx0XHR3aW5kb3cuYWRkRXZlbnRMaXN0ZW5lcihcblx0XHRcdFx0J2hhc2hjaGFuZ2UnLFxuXHRcdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5vbkhhc2hDaGFuZ2Vcblx0XHRcdCk7XG5cblx0XHRcdC8vIFN0cmlwZSBDaGVja291dFxuXHRcdFx0aWYgKCd5ZXMnID09PSB3Y3Zfc2NfcGFyYW1zLmlzX3N0cmlwZV9jaGVja291dCkge1xuXHRcdFx0XHQkKGRvY3VtZW50LmJvZHkpLm9uKCdjbGljaycsICcjcGxhY2Vfb3JkZXInLCBmdW5jdGlvbigpIHtcblx0XHRcdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5vcGVuTW9kYWwoKTtcblx0XHRcdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0XHRcdH0pO1xuXHRcdFx0fVxuXHRcdH0sXG5cblx0XHR1bm1vdW50RWxlbWVudHM6IGZ1bmN0aW9uKCkge1xuXHRcdFx0c3RyaXBlX2NhcmQudW5tb3VudCgnI3N0cmlwZS1jb25uZWN0LWNhcmQtZWxlbWVudCcpO1xuXHRcdFx0c3RyaXBlX2V4cC51bm1vdW50KCcjc3RyaXBlLWNvbm5lY3QtZXhwLWVsZW1lbnQnKTtcblx0XHRcdHN0cmlwZV9jdmMudW5tb3VudCgnI3N0cmlwZS1jb25uZWN0LWN2Yy1lbGVtZW50Jyk7XG5cdFx0fSxcblxuXHRcdG1vdW50RWxlbWVudHM6IGZ1bmN0aW9uKCkge1xuXHRcdFx0aWYgKCEkKCcjc3RyaXBlLWNvbm5lY3QtY2FyZC1lbGVtZW50JykubGVuZ3RoKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0c3RyaXBlX2NhcmQubW91bnQoJyNzdHJpcGUtY29ubmVjdC1jYXJkLWVsZW1lbnQnKTtcblx0XHRcdHN0cmlwZV9leHAubW91bnQoJyNzdHJpcGUtY29ubmVjdC1leHAtZWxlbWVudCcpO1xuXHRcdFx0c3RyaXBlX2N2Yy5tb3VudCgnI3N0cmlwZS1jb25uZWN0LWN2Yy1lbGVtZW50Jyk7XG5cdFx0fSxcblxuXHRcdGNyZWF0ZUVsZW1lbnRzOiBmdW5jdGlvbigpIHtcblx0XHRcdHZhciBlbGVtZW50U3R5bGVzID0ge1xuXHRcdFx0XHRiYXNlOiB7XG5cdFx0XHRcdFx0aWNvbkNvbG9yOiAnIzY2NkVFOCcsXG5cdFx0XHRcdFx0Y29sb3I6ICcjMzEzMjVGJyxcblx0XHRcdFx0XHRmb250U2l6ZTogJzE1cHgnLFxuXHRcdFx0XHRcdCc6OnBsYWNlaG9sZGVyJzoge1xuXHRcdFx0XHRcdFx0Y29sb3I6ICcjQ0ZEN0UwJ1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fVxuXHRcdFx0fTtcblxuXHRcdFx0dmFyIGVsZW1lbnRDbGFzc2VzID0ge1xuXHRcdFx0XHRmb2N1czogJ2ZvY3VzZWQnLFxuXHRcdFx0XHRlbXB0eTogJ2VtcHR5Jyxcblx0XHRcdFx0aW52YWxpZDogJ2ludmFsaWQnXG5cdFx0XHR9O1xuXG5cdFx0XHRzdHJpcGVfY2FyZCA9IGVsZW1lbnRzLmNyZWF0ZSgnY2FyZE51bWJlcicsIHtcblx0XHRcdFx0c3R5bGU6IGVsZW1lbnRTdHlsZXMsXG5cdFx0XHRcdGNsYXNzZXM6IGVsZW1lbnRDbGFzc2VzXG5cdFx0XHR9KTtcblx0XHRcdHN0cmlwZV9leHAgPSBlbGVtZW50cy5jcmVhdGUoJ2NhcmRFeHBpcnknLCB7XG5cdFx0XHRcdHN0eWxlOiBlbGVtZW50U3R5bGVzLFxuXHRcdFx0XHRjbGFzc2VzOiBlbGVtZW50Q2xhc3Nlc1xuXHRcdFx0fSk7XG5cdFx0XHRzdHJpcGVfY3ZjID0gZWxlbWVudHMuY3JlYXRlKCdjYXJkQ3ZjJywge1xuXHRcdFx0XHRzdHlsZTogZWxlbWVudFN0eWxlcyxcblx0XHRcdFx0Y2xhc3NlczogZWxlbWVudENsYXNzZXNcblx0XHRcdH0pO1xuXG5cdFx0XHRzdHJpcGVfY2FyZC5hZGRFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCBmdW5jdGlvbihldmVudCkge1xuXHRcdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5vbkNDRm9ybUNoYW5nZSgpO1xuXG5cdFx0XHRcdHdjdl9zdHJpcGVfY29ubmVjdF9mb3JtLnVwZGF0ZUNhcmRCcmFuZChldmVudC5icmFuZCk7XG5cdFx0XHRcdCQoJ2lucHV0LnN0cmlwZS1jb25uZWN0LXNvdXJjZScpLnJlbW92ZSgpO1xuXG5cdFx0XHRcdGlmIChldmVudC5lcnJvcikge1xuXHRcdFx0XHRcdCQoZG9jdW1lbnQuYm9keSkudHJpZ2dlcignc3RyaXBlQ29ubmVjdEVycm9yJywgZXZlbnQpO1xuXHRcdFx0XHR9XG5cdFx0XHR9KTtcblxuXHRcdFx0c3RyaXBlX2V4cC5hZGRFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCBmdW5jdGlvbihldmVudCkge1xuXHRcdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5vbkNDRm9ybUNoYW5nZSgpO1xuXHRcdFx0XHQkKCdpbnB1dC5zdHJpcGUtY29ubmVjdC1zb3VyY2UnKS5yZW1vdmUoKTtcblxuXHRcdFx0XHRpZiAoZXZlbnQuZXJyb3IpIHtcblx0XHRcdFx0XHQkKGRvY3VtZW50LmJvZHkpLnRyaWdnZXIoJ3N0cmlwZUNvbm5lY3RFcnJvcicsIGV2ZW50KTtcblx0XHRcdFx0fVxuXHRcdFx0fSk7XG5cblx0XHRcdHN0cmlwZV9jdmMuYWRkRXZlbnRMaXN0ZW5lcignY2hhbmdlJywgZnVuY3Rpb24oZXZlbnQpIHtcblx0XHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0ub25DQ0Zvcm1DaGFuZ2UoKTtcblx0XHRcdFx0JCgnaW5wdXQuc3RyaXBlLWNvbm5lY3Qtc291cmNlJykucmVtb3ZlKCk7XG5cblx0XHRcdFx0aWYgKGV2ZW50LmVycm9yKSB7XG5cdFx0XHRcdFx0JChkb2N1bWVudC5ib2R5KS50cmlnZ2VyKCdzdHJpcGVDb25uZWN0RXJyb3InLCBldmVudCk7XG5cdFx0XHRcdH1cblx0XHRcdH0pO1xuXG5cdFx0XHQvKipcblx0XHRcdCAqIE9ubHkgaW4gY2hlY2tvdXQgcGFnZSB3ZSBuZWVkIHRvIGRlbGF5IHRoZSBtb3VudGluZyBvZiB0aGVcblx0XHRcdCAqIGNhcmQgYXMgc29tZSBBSkFYIHByb2Nlc3MgbmVlZHMgdG8gaGFwcGVuIGJlZm9yZSB3ZSBkby5cblx0XHRcdCAqL1xuXHRcdFx0aWYgKCd5ZXMnID09PSB3Y3Zfc2NfcGFyYW1zLmlzX2NoZWNrb3V0KSB7XG5cdFx0XHRcdCQoZG9jdW1lbnQuYm9keSkub24oJ3VwZGF0ZWRfY2hlY2tvdXQnLCBmdW5jdGlvbigpIHtcblx0XHRcdFx0XHQvLyBEb24ndCBtb3VudCBlbGVtZW50cyBhIHNlY29uZCB0aW1lLlxuXHRcdFx0XHRcdGlmIChzdHJpcGVfY2FyZCkge1xuXHRcdFx0XHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0udW5tb3VudEVsZW1lbnRzKCk7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0ubW91bnRFbGVtZW50cygpO1xuXG5cdFx0XHRcdFx0aWYgKCQoJyNzdHJpcGUtaWJhbi1lbGVtZW50JykubGVuZ3RoKSB7XG5cdFx0XHRcdFx0XHRpYmFuLm1vdW50KCcjc3RyaXBlLWliYW4tZWxlbWVudCcpO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0fSk7XG5cdFx0XHR9IGVsc2UgaWYgKFxuXHRcdFx0XHQkKCdmb3JtI2FkZF9wYXltZW50X21ldGhvZCcpLmxlbmd0aCB8fFxuXHRcdFx0XHQkKCdmb3JtI29yZGVyX3JldmlldycpLmxlbmd0aFxuXHRcdFx0KSB7XG5cdFx0XHRcdHdjdl9zdHJpcGVfY29ubmVjdF9mb3JtLm1vdW50RWxlbWVudHMoKTtcblxuXHRcdFx0XHRpZiAoJCgnI3N0cmlwZS1pYmFuLWVsZW1lbnQnKS5sZW5ndGgpIHtcblx0XHRcdFx0XHRpYmFuLm1vdW50KCcjc3RyaXBlLWliYW4tZWxlbWVudCcpO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fSxcblxuXHRcdG9uQ0NGb3JtQ2hhbmdlOiBmdW5jdGlvbigpIHtcblx0XHRcdHdjdl9zdHJpcGVfY29ubmVjdF9mb3JtLnJlc2V0KCk7XG5cdFx0fSxcblxuXHRcdHVwZGF0ZUNhcmRCcmFuZDogZnVuY3Rpb24oYnJhbmQpIHtcblx0XHRcdHZhciBicmFuZENsYXNzID0ge1xuXHRcdFx0XHR2aXNhOiAnc3RyaXBlLXZpc2EtYnJhbmQnLFxuXHRcdFx0XHRtYXN0ZXJjYXJkOiAnc3RyaXBlLW1hc3RlcmNhcmQtYnJhbmQnLFxuXHRcdFx0XHRhbWV4OiAnc3RyaXBlLWFtZXgtYnJhbmQnLFxuXHRcdFx0XHRkaXNjb3ZlcjogJ3N0cmlwZS1kaXNjb3Zlci1icmFuZCcsXG5cdFx0XHRcdGRpbmVyczogJ3N0cmlwZS1kaW5lcnMtYnJhbmQnLFxuXHRcdFx0XHRqY2I6ICdzdHJpcGUtamNiLWJyYW5kJyxcblx0XHRcdFx0dW5rbm93bjogJ3N0cmlwZS1jcmVkaXQtY2FyZC1icmFuZCdcblx0XHRcdH07XG5cblx0XHRcdHZhciBpbWFnZUVsZW1lbnQgPSAkKCcuc3RyaXBlLWNhcmQtYnJhbmQnKSxcblx0XHRcdFx0aW1hZ2VDbGFzcyA9ICdzdHJpcGUtY3JlZGl0LWNhcmQtYnJhbmQnO1xuXG5cdFx0XHRpZiAoYnJhbmQgaW4gYnJhbmRDbGFzcykge1xuXHRcdFx0XHRpbWFnZUNsYXNzID0gYnJhbmRDbGFzc1ticmFuZF07XG5cdFx0XHR9XG5cblx0XHRcdC8vIFJlbW92ZSBleGlzdGluZyBjYXJkIGJyYW5kIGNsYXNzLlxuXHRcdFx0JC5lYWNoKGJyYW5kQ2xhc3MsIGZ1bmN0aW9uKGluZGV4LCBlbCkge1xuXHRcdFx0XHRpbWFnZUVsZW1lbnQucmVtb3ZlQ2xhc3MoZWwpO1xuXHRcdFx0fSk7XG5cblx0XHRcdGltYWdlRWxlbWVudC5hZGRDbGFzcyhpbWFnZUNsYXNzKTtcblx0XHR9LFxuXG5cdFx0Ly8gU3RyaXBlIENoZWNrb3V0LlxuXHRcdG9wZW5Nb2RhbDogZnVuY3Rpb24oKSB7XG5cdFx0XHQvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmVcblx0XHRcdGNvbnNvbGUubG9nKCdvcGVuTW9kYWwnKTtcblxuXHRcdFx0Ly8gQ2FwdHVyZSBzdWJtaXR0YWwgYW5kIG9wZW4gc3RyaXBlY2hlY2tvdXRcblx0XHRcdHZhciAkZm9ybSA9IHdjdl9zdHJpcGVfY29ubmVjdF9mb3JtLmZvcm0sXG5cdFx0XHRcdCRkYXRhID0gJCgnI3N0cmlwZS1jb25uZWN0LXBheW1lbnQtZGF0YScpO1xuXG5cdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5yZXNldCgpO1xuXG5cdFx0XHR2YXIgdG9rZW5fYWN0aW9uID0gZnVuY3Rpb24ocmVzKSB7XG5cdFx0XHRcdCRmb3JtLmZpbmQoJ2lucHV0LnN0cmlwZV9zb3VyY2UnKS5yZW1vdmUoKTtcblxuXHRcdFx0XHRpZiAoJ3Rva2VuJyA9PT0gcmVzLm9iamVjdCkge1xuXHRcdFx0XHRcdHN0cmlwZVxuXHRcdFx0XHRcdFx0LmNyZWF0ZVNvdXJjZSh7XG5cdFx0XHRcdFx0XHRcdHR5cGU6ICdjYXJkJyxcblx0XHRcdFx0XHRcdFx0dG9rZW46IHJlcy5pZFxuXHRcdFx0XHRcdFx0fSlcblx0XHRcdFx0XHRcdC50aGVuKHdjdl9zdHJpcGVfY29ubmVjdF9mb3JtLnNvdXJjZVJlc3BvbnNlKTtcblx0XHRcdFx0fSBlbHNlIGlmICgnc291cmNlJyA9PT0gcmVzLm9iamVjdCkge1xuXHRcdFx0XHRcdHZhciByZXNwb25zZSA9IHsgc291cmNlOiByZXMgfTtcblx0XHRcdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5zb3VyY2VSZXNwb25zZShyZXNwb25zZSk7XG5cdFx0XHRcdH1cblx0XHRcdH07XG5cblx0XHRcdFN0cmlwZUNoZWNrb3V0Lm9wZW4oe1xuXHRcdFx0XHRrZXk6IHdjdl9zY19wYXJhbXMua2V5LFxuXHRcdFx0XHRiaWxsaW5nQWRkcmVzczogJGRhdGEuZGF0YSgnYmlsbGluZy1hZGRyZXNzJyksXG5cdFx0XHRcdHppcENvZGU6ICRkYXRhLmRhdGEoJ3ZlcmlmeS16aXAnKSxcblx0XHRcdFx0YW1vdW50OiAkZGF0YS5kYXRhKCdhbW91bnQnKSxcblx0XHRcdFx0bmFtZTogJGRhdGEuZGF0YSgnbmFtZScpLFxuXHRcdFx0XHRkZXNjcmlwdGlvbjogJGRhdGEuZGF0YSgnZGVzY3JpcHRpb24nKSxcblx0XHRcdFx0Y3VycmVuY3k6ICRkYXRhLmRhdGEoJ2N1cnJlbmN5JyksXG5cdFx0XHRcdGltYWdlOiAkZGF0YS5kYXRhKCdpbWFnZScpLFxuXHRcdFx0XHRsb2NhbGU6ICRkYXRhLmRhdGEoJ2xvY2FsZScpLFxuXHRcdFx0XHRlbWFpbDogJCgnI2JpbGxpbmdfZW1haWwnKS52YWwoKSB8fCAkZGF0YS5kYXRhKCdlbWFpbCcpLFxuXHRcdFx0XHRwYW5lbExhYmVsOiAkZGF0YS5kYXRhKCdwYW5lbC1sYWJlbCcpLFxuXHRcdFx0XHRhbGxvd1JlbWVtYmVyTWU6ICRkYXRhLmRhdGEoJ2FsbG93LXJlbWVtYmVyLW1lJyksXG5cdFx0XHRcdHRva2VuOiB0b2tlbl9hY3Rpb24sXG5cdFx0XHRcdGNsb3NlZDogd2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0ub25DbG9zZSgpXG5cdFx0XHR9KTtcblx0XHR9LFxuXG5cdFx0Ly8gU3RyaXBlIENoZWNrb3V0LlxuXHRcdHJlc2V0TW9kYWw6IGZ1bmN0aW9uKCkge1xuXHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0ucmVzZXQoKTtcblx0XHRcdHdjdl9zdHJpcGVfY29ubmVjdF9mb3JtLnN0cmlwZV9jaGVja291dF9zdWJtaXQgPSBmYWxzZTtcblx0XHR9LFxuXG5cdFx0Ly8gU3RyaXBlIENoZWNrb3V0LlxuXHRcdG9uQ2xvc2U6IGZ1bmN0aW9uKCkge1xuXHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0udW5ibG9jaygpO1xuXHRcdH0sXG5cblx0XHR1bmJsb2NrOiBmdW5jdGlvbigpIHtcblx0XHRcdHdjdl9zdHJpcGVfY29ubmVjdF9mb3JtLmZvcm0udW5ibG9jaygpO1xuXHRcdH0sXG5cblx0XHQvLyByZXN0XG5cdFx0cmVzZXQ6IGZ1bmN0aW9uKCkge1xuXHRcdFx0JChcblx0XHRcdFx0Jy53Y3Ytc3RyaXBlLWNvbm5lY3QtZXJyb3IsIC5zdHJpcGVDb25uZWN0RXJyb3IsIC5zdHJpcGVfY29ubmVjdF90b2tlbidcblx0XHRcdCkucmVtb3ZlKCk7XG5cblx0XHRcdC8vIFN0cmlwZSBDaGVja291dC5cblx0XHRcdGlmICgneWVzJyA9PT0gd2N2X3NjX3BhcmFtcy5pc19zdHJpcGVfY2hlY2tvdXQpIHtcblx0XHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0uc3RyaXBlX3N1Ym1pdCA9IGZhbHNlO1xuXHRcdFx0fVxuXHRcdH0sXG5cblx0XHQvLyBDaGVjayB0byBzZWUgaWYgU3RyaXBlIGluIGdlbmVyYWwgaXMgYmVpbmcgdXNlZCBmb3IgY2hlY2tvdXQuXG5cdFx0aXNTdHJpcGVDb25uZWN0Q2hvc2VuOiBmdW5jdGlvbigpIHtcblx0XHRcdHJldHVybiAoXG5cdFx0XHRcdCQoJyNwYXltZW50X21ldGhvZF9zdHJpcGUtY29ubmVjdCcpLmlzKCc6Y2hlY2tlZCcpIHx8XG5cdFx0XHRcdCgkKCcjcGF5bWVudF9tZXRob2Rfc3RyaXBlLWNvbm5lY3QnKS5pcygnOmNoZWNrZWQnKSAmJlxuXHRcdFx0XHRcdCduZXcnID09PVxuXHRcdFx0XHRcdFx0JChcblx0XHRcdFx0XHRcdFx0J2lucHV0W25hbWU9XCJ3Yy1zdHJpcGUtcGF5bWVudC10b2tlblwiXTpjaGVja2VkJ1xuXHRcdFx0XHRcdFx0KS52YWwoKSlcblx0XHRcdCk7XG5cdFx0fSxcblxuXHRcdC8vIEN1cnJlbnRseSBvbmx5IHN1cHBvcnQgc2F2ZWQgY2FyZHMgdmlhIGNyZWRpdCBjYXJkcyBhbmQgU0VQQS4gTm8gb3RoZXIgcGF5bWVudCBtZXRob2QuXG5cdFx0aXNTdHJpcGVTYXZlQ2FyZENob3NlbjogZnVuY3Rpb24oKSB7XG5cdFx0XHRyZXR1cm4gKFxuXHRcdFx0XHQkKCcjcGF5bWVudF9tZXRob2Rfc3RyaXBlLWNvbm5lY3QnKS5pcygnOmNoZWNrZWQnKSAmJlxuXHRcdFx0XHQkKCdpbnB1dFtuYW1lPVwid2Mtc3RyaXBlLWNvbm5lY3QtcGF5bWVudC10b2tlblwiXScpLmlzKFxuXHRcdFx0XHRcdCc6Y2hlY2tlZCdcblx0XHRcdFx0KSAmJlxuXHRcdFx0XHQnbmV3JyAhPT1cblx0XHRcdFx0XHQkKFxuXHRcdFx0XHRcdFx0J2lucHV0W25hbWU9XCJ3Yy1zdHJpcGUtY29ubmVjdC1wYXltZW50LXRva2VuXCJdOmNoZWNrZWQnXG5cdFx0XHRcdFx0KS52YWwoKVxuXHRcdFx0KTtcblx0XHR9LFxuXG5cdFx0Ly8gU3RyaXBlIGNyZWRpdCBjYXJkIHVzZWQuXG5cdFx0aXNTdHJpcGVDYXJkQ2hvc2VuOiBmdW5jdGlvbigpIHtcblx0XHRcdHJldHVybiAkKCcjcGF5bWVudF9tZXRob2Rfc3RyaXBlJykuaXMoJzpjaGVja2VkJyk7XG5cdFx0fSxcblxuXHRcdGhhc1NvdXJjZTogZnVuY3Rpb24oKSB7XG5cdFx0XHRyZXR1cm4gMCA8ICQoJ2lucHV0LnN0cmlwZS1jb25uZWN0LXNvdXJjZScpLmxlbmd0aDtcblx0XHR9LFxuXG5cdFx0Ly8gTGVnYWN5XG5cdFx0aGFzVG9rZW46IGZ1bmN0aW9uKCkge1xuXHRcdFx0cmV0dXJuIDAgPCAkKCdpbnB1dC5zdHJpcGVfY29ubmVjdF90b2tlbicpLmxlbmd0aDtcblx0XHR9LFxuXG5cdFx0aXNNb2JpbGU6IGZ1bmN0aW9uKCkge1xuXHRcdFx0aWYgKFxuXHRcdFx0XHQvQW5kcm9pZHx3ZWJPU3xpUGhvbmV8aVBhZHxpUG9kfEJsYWNrQmVycnl8SUVNb2JpbGV8T3BlcmEgTWluaS9pLnRlc3QoXG5cdFx0XHRcdFx0bmF2aWdhdG9yLnVzZXJBZ2VudFxuXHRcdFx0XHQpXG5cdFx0XHQpIHtcblx0XHRcdFx0cmV0dXJuIHRydWU7XG5cdFx0XHR9XG5cblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9LFxuXG5cdFx0aXNTdHJpcGVNb2RhbE5lZWRlZDogZnVuY3Rpb24oKSB7XG5cdFx0XHR2YXIgdG9rZW4gPSB3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5mb3JtLmZpbmQoXG5cdFx0XHRcdCdpbnB1dC5zdHJpcGVfY29ubmVjdF90b2tlbidcblx0XHRcdCk7XG5cblx0XHRcdC8vIElmIHRoaXMgaXMgYSBzdHJpcGUgc3VibWlzc2lvbiAoYWZ0ZXIgbW9kYWwpIGFuZCB0b2tlbiBleGlzdHMsIGFsbG93IHN1Ym1pdC5cblx0XHRcdGlmICh3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5zdHJpcGVfc3VibWl0ICYmIHRva2VuKSB7XG5cdFx0XHRcdHJldHVybiBmYWxzZTtcblx0XHRcdH1cblxuXHRcdFx0Ly8gRG9uJ3QgYWZmZWN0IHN1Ym1pc3Npb24gaWYgbW9kYWwgaXMgbm90IG5lZWRlZC5cblx0XHRcdGlmICghd2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0uaXNTdHJpcGVDaG9zZW4oKSkge1xuXHRcdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0XHR9XG5cblx0XHRcdHJldHVybiB0cnVlO1xuXHRcdH0sXG5cblx0XHRibG9jazogZnVuY3Rpb24oKSB7XG5cdFx0XHRpZiAoIXdjdl9zdHJpcGVfY29ubmVjdF9mb3JtLmlzTW9iaWxlKCkpIHtcblx0XHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0uZm9ybS5ibG9jayh7XG5cdFx0XHRcdFx0bWVzc2FnZTogbnVsbCxcblx0XHRcdFx0XHRvdmVybGF5Q1NTOiB7XG5cdFx0XHRcdFx0XHRiYWNrZ3JvdW5kOiAnI2ZmZicsXG5cdFx0XHRcdFx0XHRvcGFjaXR5OiAwLjZcblx0XHRcdFx0XHR9XG5cdFx0XHRcdH0pO1xuXHRcdFx0fVxuXHRcdH0sXG5cblx0XHQvLyBHZXQgdGhlIGN1c3RvbWVyIGRldGFpbHNcblx0XHRnZXRDdXN0b21lckRldGFpbHM6IGZ1bmN0aW9uKCkge1xuXHRcdFx0dmFyIGZpcnN0X25hbWUgPSAkKCcjYmlsbGluZ19maXJzdF9uYW1lJykubGVuZ3RoXG5cdFx0XHRcdFx0PyAkKCcjYmlsbGluZ19maXJzdF9uYW1lJykudmFsKClcblx0XHRcdFx0XHQ6IHdjdl9zY19wYXJhbXMuYmlsbGluZ19maXJzdF9uYW1lLFxuXHRcdFx0XHRsYXN0X25hbWUgPSAkKCcjYmlsbGluZ19sYXN0X25hbWUnKS5sZW5ndGhcblx0XHRcdFx0XHQ/ICQoJyNiaWxsaW5nX2xhc3RfbmFtZScpLnZhbCgpXG5cdFx0XHRcdFx0OiB3Y3Zfc2NfcGFyYW1zLmJpbGxpbmdfbGFzdF9uYW1lLFxuXHRcdFx0XHRleHRyYV9kZXRhaWxzID0ge1xuXHRcdFx0XHRcdG93bmVyOiB7IG5hbWU6ICcnLCBhZGRyZXNzOiB7fSwgZW1haWw6ICcnLCBwaG9uZTogJycgfVxuXHRcdFx0XHR9O1xuXG5cdFx0XHRleHRyYV9kZXRhaWxzLm93bmVyLm5hbWUgPSBmaXJzdF9uYW1lO1xuXG5cdFx0XHRpZiAoZmlyc3RfbmFtZSAmJiBsYXN0X25hbWUpIHtcblx0XHRcdFx0ZXh0cmFfZGV0YWlscy5vd25lci5uYW1lID0gZmlyc3RfbmFtZSArICcgJyArIGxhc3RfbmFtZTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdGV4dHJhX2RldGFpbHMub3duZXIubmFtZSA9ICQoJyNzdHJpcGUtcGF5bWVudC1kYXRhJykuZGF0YShcblx0XHRcdFx0XHQnZnVsbC1uYW1lJ1xuXHRcdFx0XHQpO1xuXHRcdFx0fVxuXG5cdFx0XHRleHRyYV9kZXRhaWxzLm93bmVyLmVtYWlsID0gJCgnI2JpbGxpbmdfZW1haWwnKS52YWwoKTtcblx0XHRcdGV4dHJhX2RldGFpbHMub3duZXIucGhvbmUgPSAkKCcjYmlsbGluZ19waG9uZScpLnZhbCgpO1xuXG5cdFx0XHQvKiBTdHJpcGUgZG9lcyBub3QgbGlrZSBlbXB0eSBzdHJpbmcgdmFsdWVzIHNvXG5cdFx0XHQgKiB3ZSBuZWVkIHRvIHJlbW92ZSB0aGUgcGFyYW1ldGVyIGlmIHdlJ3JlIG5vdFxuXHRcdFx0ICogcGFzc2luZyBhbnkgdmFsdWUuXG5cdFx0XHQgKi9cblx0XHRcdGlmIChcblx0XHRcdFx0J3VuZGVmaW5lZCcgPT09IHR5cGVvZiBleHRyYV9kZXRhaWxzLm93bmVyLnBob25lIHx8XG5cdFx0XHRcdDAgPj0gZXh0cmFfZGV0YWlscy5vd25lci5waG9uZS5sZW5ndGhcblx0XHRcdCkge1xuXHRcdFx0XHRkZWxldGUgZXh0cmFfZGV0YWlscy5vd25lci5waG9uZTtcblx0XHRcdH1cblxuXHRcdFx0aWYgKFxuXHRcdFx0XHQndW5kZWZpbmVkJyA9PT0gdHlwZW9mIGV4dHJhX2RldGFpbHMub3duZXIuZW1haWwgfHxcblx0XHRcdFx0MCA+PSBleHRyYV9kZXRhaWxzLm93bmVyLmVtYWlsLmxlbmd0aFxuXHRcdFx0KSB7XG5cdFx0XHRcdGlmICgkKCcjc3RyaXBlLWNvbm5lY3QtcGF5bWVudC1kYXRhJykuZGF0YSgnZW1haWwnKS5sZW5ndGgpIHtcblx0XHRcdFx0XHRleHRyYV9kZXRhaWxzLm93bmVyLmVtYWlsID0gJChcblx0XHRcdFx0XHRcdCcjc3RyaXBlLWNvbm5lY3QtcGF5bWVudC1kYXRhJ1xuXHRcdFx0XHRcdCkuZGF0YSgnZW1haWwnKTtcblx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHRkZWxldGUgZXh0cmFfZGV0YWlscy5vd25lci5lbWFpbDtcblx0XHRcdFx0fVxuXHRcdFx0fVxuXG5cdFx0XHRpZiAoXG5cdFx0XHRcdCd1bmRlZmluZWQnID09PSB0eXBlb2YgZXh0cmFfZGV0YWlscy5vd25lci5uYW1lIHx8XG5cdFx0XHRcdDAgPj0gZXh0cmFfZGV0YWlscy5vd25lci5uYW1lLmxlbmd0aFxuXHRcdFx0KSB7XG5cdFx0XHRcdGRlbGV0ZSBleHRyYV9kZXRhaWxzLm93bmVyLm5hbWU7XG5cdFx0XHR9XG5cblx0XHRcdGlmICgwIDwgJCgnI2JpbGxpbmdfYWRkcmVzc18xJykubGVuZ3RoKSB7XG5cdFx0XHRcdGV4dHJhX2RldGFpbHMub3duZXIuYWRkcmVzcy5saW5lMSA9ICQoXG5cdFx0XHRcdFx0JyNiaWxsaW5nX2FkZHJlc3NfMSdcblx0XHRcdFx0KS52YWwoKTtcblx0XHRcdFx0ZXh0cmFfZGV0YWlscy5vd25lci5hZGRyZXNzLmxpbmUyID0gJChcblx0XHRcdFx0XHQnI2JpbGxpbmdfYWRkcmVzc18yJ1xuXHRcdFx0XHQpLnZhbCgpO1xuXHRcdFx0XHRleHRyYV9kZXRhaWxzLm93bmVyLmFkZHJlc3Muc3RhdGUgPSAkKCcjYmlsbGluZ19zdGF0ZScpLnZhbCgpO1xuXHRcdFx0XHRleHRyYV9kZXRhaWxzLm93bmVyLmFkZHJlc3MuY2l0eSA9ICQoJyNiaWxsaW5nX2NpdHknKS52YWwoKTtcblx0XHRcdFx0ZXh0cmFfZGV0YWlscy5vd25lci5hZGRyZXNzLnBvc3RhbF9jb2RlID0gJChcblx0XHRcdFx0XHQnI2JpbGxpbmdfcG9zdGNvZGUnXG5cdFx0XHRcdCkudmFsKCk7XG5cdFx0XHRcdGV4dHJhX2RldGFpbHMub3duZXIuYWRkcmVzcy5jb3VudHJ5ID0gJChcblx0XHRcdFx0XHQnI2JpbGxpbmdfY291bnRyeSdcblx0XHRcdFx0KS52YWwoKTtcblx0XHRcdH0gZWxzZSBpZiAod2N2X3NjX3BhcmFtcy5iaWxsaW5nX2FkZHJlc3NfMSkge1xuXHRcdFx0XHRleHRyYV9kZXRhaWxzLm93bmVyLmFkZHJlc3MubGluZTEgPVxuXHRcdFx0XHRcdHdjdl9zY19wYXJhbXMuYmlsbGluZ19hZGRyZXNzXzE7XG5cdFx0XHRcdGV4dHJhX2RldGFpbHMub3duZXIuYWRkcmVzcy5saW5lMiA9XG5cdFx0XHRcdFx0d2N2X3NjX3BhcmFtcy5iaWxsaW5nX2FkZHJlc3NfMjtcblx0XHRcdFx0ZXh0cmFfZGV0YWlscy5vd25lci5hZGRyZXNzLnN0YXRlID0gd2N2X3NjX3BhcmFtcy5iaWxsaW5nX3N0YXRlO1xuXHRcdFx0XHRleHRyYV9kZXRhaWxzLm93bmVyLmFkZHJlc3MuY2l0eSA9IHdjdl9zY19wYXJhbXMuYmlsbGluZ19jaXR5O1xuXHRcdFx0XHRleHRyYV9kZXRhaWxzLm93bmVyLmFkZHJlc3MucG9zdGFsX2NvZGUgPVxuXHRcdFx0XHRcdHdjdl9zY19wYXJhbXMuYmlsbGluZ19wb3N0Y29kZTtcblx0XHRcdFx0ZXh0cmFfZGV0YWlscy5vd25lci5hZGRyZXNzLmNvdW50cnkgPVxuXHRcdFx0XHRcdHdjdl9zY19wYXJhbXMuYmlsbGluZ19jb3VudHJ5O1xuXHRcdFx0fVxuXG5cdFx0XHRyZXR1cm4gZXh0cmFfZGV0YWlscztcblx0XHR9LFxuXG5cdFx0Ly8gU291cmNlIFN1cHBvcnRcblx0XHRjcmVhdGVTb3VyY2U6IGZ1bmN0aW9uKCkge1xuXHRcdFx0dmFyIGV4dHJhX2RldGFpbHMgPSB3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5nZXRDdXN0b21lckRldGFpbHMoKTtcblxuXHRcdFx0Ly8gQ3JlYXRlIHRoZSBTdHJpcGUgc291cmNlXG5cdFx0XHRzdHJpcGVcblx0XHRcdFx0LmNyZWF0ZVNvdXJjZShzdHJpcGVfY2FyZCwgZXh0cmFfZGV0YWlscylcblx0XHRcdFx0LnRoZW4od2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0uc291cmNlUmVzcG9uc2UpO1xuXHRcdH0sXG5cblx0XHRzb3VyY2VSZXNwb25zZTogZnVuY3Rpb24ocmVzcG9uc2UpIHtcblx0XHRcdGlmIChyZXNwb25zZS5lcnJvcikge1xuXHRcdFx0XHQkKGRvY3VtZW50LmJvZHkpLnRyaWdnZXIoJ3N0cmlwZUNvbm5lY3RFcnJvcicsIHJlc3BvbnNlKTtcblx0XHRcdH0gZWxzZSBpZiAoXG5cdFx0XHRcdCdubycgPT09IHdjdl9zY19wYXJhbXMuYWxsb3dfcHJlcGFpZF9jYXJkICYmXG5cdFx0XHRcdCdjYXJkJyA9PT0gcmVzcG9uc2Uuc291cmNlLnR5cGUgJiZcblx0XHRcdFx0J3ByZXBhaWQnID09PSByZXNwb25zZS5zb3VyY2UuY2FyZC5mdW5kaW5nXG5cdFx0XHQpIHtcblx0XHRcdFx0cmVzcG9uc2UuZXJyb3IgPSB7IG1lc3NhZ2U6IHdjdl9zY19wYXJhbXMubm9fcHJlcGFpZF9jYXJkX21zZyB9O1xuXG5cdFx0XHRcdGlmICgneWVzJyA9PT0gd2N2X3NjX3BhcmFtcy5pc19zdHJpcGVfY2hlY2tvdXQpIHtcblx0XHRcdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5zdWJtaXRFcnJvcihcblx0XHRcdFx0XHRcdCc8dWwgY2xhc3M9XCJ3b29jb21tZXJjZS1lcnJvclwiPjxsaT4nICtcblx0XHRcdFx0XHRcdFx0d2N2X3NjX3BhcmFtcy5ub19wcmVwYWlkX2NhcmRfbXNnICtcblx0XHRcdFx0XHRcdFx0JzwvbGk+PC91bD4nXG5cdFx0XHRcdFx0KTtcblx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHQkKGRvY3VtZW50LmJvZHkpLnRyaWdnZXIoJ3N0cmlwZUNvbm5lY3RFcnJvcicsIHJlc3BvbnNlKTtcblx0XHRcdFx0fVxuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0ucHJvY2Vzc1N0cmlwZVJlc3BvbnNlKHJlc3BvbnNlLnNvdXJjZSk7XG5cdFx0XHR9XG5cdFx0fSxcblxuXHRcdHByb2Nlc3NTdHJpcGVSZXNwb25zZTogZnVuY3Rpb24oc291cmNlKSB7XG5cdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5yZXNldCgpO1xuXG5cdFx0XHQvLyBJbnNlcnQgdGhlIFNvdXJjZSBpbnRvIHRoZSBmb3JtIHNvIGl0IGdldHMgc3VibWl0dGVkIHRvIHRoZSBzZXJ2ZXIuXG5cdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5mb3JtLmFwcGVuZChcblx0XHRcdFx0XCI8aW5wdXQgdHlwZT0naGlkZGVuJyBjbGFzcz0nc3RyaXBlLWNvbm5lY3Qtc291cmNlJyBuYW1lPSdzdHJpcGVfY29ubmVjdF9zb3VyY2UnIHZhbHVlPSdcIiArXG5cdFx0XHRcdFx0c291cmNlLmlkICtcblx0XHRcdFx0XHRcIicvPlwiXG5cdFx0XHQpO1xuXG5cdFx0XHRpZiAoJCgnZm9ybSNhZGRfcGF5bWVudF9tZXRob2QnKS5sZW5ndGgpIHtcblx0XHRcdFx0JCh3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5mb3JtKS5vZmYoXG5cdFx0XHRcdFx0J3N1Ym1pdCcsXG5cdFx0XHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0uZm9ybS5vblN1Ym1pdFxuXHRcdFx0XHQpO1xuXHRcdFx0fVxuXG5cdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5mb3JtLnN1Ym1pdCgpO1xuXHRcdH0sXG5cblx0XHRvblN1Ym1pdDogZnVuY3Rpb24oZSkge1xuXHRcdFx0aWYgKCF3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5pc1N0cmlwZUNvbm5lY3RDaG9zZW4oKSkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGlmIChcblx0XHRcdFx0IXdjdl9zdHJpcGVfY29ubmVjdF9mb3JtLmlzU3RyaXBlU2F2ZUNhcmRDaG9zZW4oKSAmJlxuXHRcdFx0XHQhd2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0uaGFzU291cmNlKCkgJiZcblx0XHRcdFx0IXdjdl9zdHJpcGVfY29ubmVjdF9mb3JtLmhhc1Rva2VuKClcblx0XHRcdCkge1xuXHRcdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0uYmxvY2soKTtcblxuXHRcdFx0XHQvLyBTdHJpcGUgQ2hlY2tvdXQuXG5cdFx0XHRcdGlmIChcblx0XHRcdFx0XHQneWVzJyA9PT0gd2N2X3NjX3BhcmFtcy5pc19zdHJpcGVfY2hlY2tvdXQgJiZcblx0XHRcdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5pc1N0cmlwZU1vZGFsTmVlZGVkKCkgJiZcblx0XHRcdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5pc1N0cmlwZUNhcmRDaG9zZW4oKVxuXHRcdFx0XHQpIHtcblx0XHRcdFx0XHRpZiAoJ3llcycgPT09IHdjdl9zY19wYXJhbXMuaXNfY2hlY2tvdXQpIHtcblx0XHRcdFx0XHRcdHJldHVybiB0cnVlO1xuXHRcdFx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5vcGVuTW9kYWwoKTtcblx0XHRcdFx0XHRcdHJldHVybiBmYWxzZTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdH1cblxuXHRcdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5jcmVhdGVTb3VyY2UoKTtcblxuXHRcdFx0XHQvLyBQcmV2ZW50IGZvcm0gc3VibWl0dGluZ1xuXHRcdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0XHR9XG5cdFx0fSxcblxuXHRcdGdldFNlbGVjdGVkUGF5bWVudEVsZW1lbnQ6IGZ1bmN0aW9uKCkge1xuXHRcdFx0cmV0dXJuICQoJy5wYXltZW50X21ldGhvZHMgaW5wdXRbbmFtZT1cInBheW1lbnRfbWV0aG9kXCJdOmNoZWNrZWQnKTtcblx0XHR9LFxuXG5cdFx0b25FcnJvcjogZnVuY3Rpb24oZSwgcmVzdWx0KSB7XG5cdFx0XHR2YXIgbWVzc2FnZSA9IHJlc3VsdC5lcnJvci5tZXNzYWdlO1xuXHRcdFx0dmFyIHNlbGVjdGVkTWV0aG9kRWxlbWVudCA9IHdjdl9zdHJpcGVfY29ubmVjdF9mb3JtXG5cdFx0XHRcdC5nZXRTZWxlY3RlZFBheW1lbnRFbGVtZW50KClcblx0XHRcdFx0LmNsb3Nlc3QoJ2xpJyk7XG5cdFx0XHR2YXIgc2F2ZWRUb2tlbnMgPSBzZWxlY3RlZE1ldGhvZEVsZW1lbnQuZmluZChcblx0XHRcdFx0Jy53b29jb21tZXJjZS1TYXZlZFBheW1lbnRNZXRob2RzLXRva2VuSW5wdXQnXG5cdFx0XHQpO1xuXHRcdFx0dmFyIGVycm9yQ29udGFpbmVyO1xuXG5cdFx0XHRpZiAoc2F2ZWRUb2tlbnMubGVuZ3RoKSB7XG5cdFx0XHRcdC8vIEluIGNhc2UgdGhlcmUgYXJlIHNhdmVkIGNhcmRzIHRvbywgZGlzcGxheSB0aGUgbWVzc2FnZSBuZXh0IHRvIHRoZSBjb3JyZWN0IG9uZS5cblx0XHRcdFx0dmFyIHNlbGVjdGVkVG9rZW4gPSBzYXZlZFRva2Vucy5maWx0ZXIoJzpjaGVja2VkJyk7XG5cblx0XHRcdFx0aWYgKFxuXHRcdFx0XHRcdHNlbGVjdGVkVG9rZW4uY2xvc2VzdChcblx0XHRcdFx0XHRcdCcud29vY29tbWVyY2UtU2F2ZWRQYXltZW50TWV0aG9kcy1uZXcnXG5cdFx0XHRcdFx0KS5sZW5ndGhcblx0XHRcdFx0KSB7XG5cdFx0XHRcdFx0Ly8gRGlzcGxheSB0aGUgZXJyb3IgbmV4dCB0byB0aGUgQ0MgZmllbGRzIGlmIGEgbmV3IGNhcmQgaXMgYmVpbmcgZW50ZXJlZC5cblx0XHRcdFx0XHRlcnJvckNvbnRhaW5lciA9ICQoXG5cdFx0XHRcdFx0XHQnI3djdi1zdHJpcGUtY29ubmVjdC1jYy1mb3JtIC5zdHJpcGUtY29ubmVjdC1zb3VyY2UtZXJyb3JzJ1xuXHRcdFx0XHRcdCk7XG5cdFx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdFx0Ly8gRGlzcGxheSB0aGUgZXJyb3IgbmV4dCB0byB0aGUgY2hvc2VuIHNhdmVkIGNhcmQuXG5cdFx0XHRcdFx0ZXJyb3JDb250YWluZXIgPSBzZWxlY3RlZFRva2VuXG5cdFx0XHRcdFx0XHQuY2xvc2VzdCgnbGknKVxuXHRcdFx0XHRcdFx0LmZpbmQoJy5zdHJpcGUtY29ubmVjdC1zb3VyY2UtZXJyb3JzJyk7XG5cdFx0XHRcdH1cblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdC8vIFdoZW4gbm8gc2F2ZWQgY2FyZHMgYXJlIGF2YWlsYWJsZSwgZGlzcGxheSB0aGUgZXJyb3IgbmV4dCB0byBDQyBmaWVsZHMuXG5cdFx0XHRcdGVycm9yQ29udGFpbmVyID0gc2VsZWN0ZWRNZXRob2RFbGVtZW50LmZpbmQoXG5cdFx0XHRcdFx0Jy5zdHJpcGUtY29ubmVjdC1zb3VyY2UtZXJyb3JzJ1xuXHRcdFx0XHQpO1xuXHRcdFx0fVxuXG5cdFx0XHQvKlxuXHRcdFx0ICogQ3VzdG9tZXJzIGRvIG5vdCBuZWVkIHRvIGtub3cgdGhlIHNwZWNpZmljcyBvZiB0aGUgYmVsb3cgdHlwZSBvZiBlcnJvcnNcblx0XHRcdCAqIHRoZXJlZm9yZSByZXR1cm4gYSBnZW5lcmljIGxvY2FsaXphYmxlIGVycm9yIG1lc3NhZ2UuXG5cdFx0XHQgKi9cblx0XHRcdGlmIChcblx0XHRcdFx0J2ludmFsaWRfcmVxdWVzdF9lcnJvcicgPT09IHJlc3VsdC5lcnJvci50eXBlIHx8XG5cdFx0XHRcdCdhcGlfY29ubmVjdGlvbl9lcnJvcicgPT09IHJlc3VsdC5lcnJvci50eXBlIHx8XG5cdFx0XHRcdCdhcGlfZXJyb3InID09PSByZXN1bHQuZXJyb3IudHlwZSB8fFxuXHRcdFx0XHQnYXV0aGVudGljYXRpb25fZXJyb3InID09PSByZXN1bHQuZXJyb3IudHlwZSB8fFxuXHRcdFx0XHQncmF0ZV9saW1pdF9lcnJvcicgPT09IHJlc3VsdC5lcnJvci50eXBlXG5cdFx0XHQpIHtcblx0XHRcdFx0bWVzc2FnZSA9IHdjdl9zY19wYXJhbXMuaW52YWxpZF9yZXF1ZXN0X2Vycm9yO1xuXHRcdFx0fVxuXG5cdFx0XHRpZiAoXG5cdFx0XHRcdCdjYXJkX2Vycm9yJyA9PT0gcmVzdWx0LmVycm9yLnR5cGUgJiZcblx0XHRcdFx0d2N2X3NjX3BhcmFtcy5oYXNPd25Qcm9wZXJ0eShyZXN1bHQuZXJyb3IuY29kZSlcblx0XHRcdCkge1xuXHRcdFx0XHRtZXNzYWdlID0gd2N2X3NjX3BhcmFtc1tyZXN1bHQuZXJyb3IuY29kZV07XG5cdFx0XHR9XG5cblx0XHRcdGlmIChcblx0XHRcdFx0J3ZhbGlkYXRpb25fZXJyb3InID09PSByZXN1bHQuZXJyb3IudHlwZSAmJlxuXHRcdFx0XHR3Y3Zfc2NfcGFyYW1zLmhhc093blByb3BlcnR5KHJlc3VsdC5lcnJvci5jb2RlKVxuXHRcdFx0KSB7XG5cdFx0XHRcdG1lc3NhZ2UgPSB3Y3Zfc2NfcGFyYW1zW3Jlc3VsdC5lcnJvci5jb2RlXTtcblx0XHRcdH1cblxuXHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0ucmVzZXQoKTtcblx0XHRcdCQoJy53b29jb21tZXJjZS1Ob3RpY2VHcm91cC1jaGVja291dCcpLnJlbW92ZSgpO1xuXHRcdFx0Ly8gZXNsaW50LWRpc2FibGUtbmV4dC1saW5lXG5cdFx0XHRjb25zb2xlLmxvZyhyZXN1bHQuZXJyb3IubWVzc2FnZSk7IC8vIExlYXZlIGZvciB0cm91Ymxlc2hvb3RpbmcuXG5cdFx0XHRlcnJvckNvbnRhaW5lci5odG1sKFxuXHRcdFx0XHQnPHVsIGNsYXNzPVwid29vY29tbWVyY2VfZXJyb3Igd29vY29tbWVyY2UtZXJyb3Igd2N2LXN0cmlwZS1jb25uZWN0LWVycm9yXCI+PGxpPicgK1xuXHRcdFx0XHRcdG1lc3NhZ2UgK1xuXHRcdFx0XHRcdCc8L2xpPjwvdWw+J1xuXHRcdFx0KTtcblxuXHRcdFx0aWYgKCQoJy53Y3Ytc3RyaXBlLWNvbm5lY3QtZXJyb3InKS5sZW5ndGgpIHtcblx0XHRcdFx0JCgnaHRtbCwgYm9keScpLmFuaW1hdGUoXG5cdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0c2Nyb2xsVG9wOlxuXHRcdFx0XHRcdFx0XHQkKCcud2N2LXN0cmlwZS1jb25uZWN0LWVycm9yJykub2Zmc2V0KCkudG9wIC0gMjAwXG5cdFx0XHRcdFx0fSxcblx0XHRcdFx0XHQyMDBcblx0XHRcdFx0KTtcblx0XHRcdH1cblx0XHRcdHdjdl9zdHJpcGVfY29ubmVjdF9mb3JtLnVuYmxvY2soKTtcblx0XHR9LFxuXG5cdFx0c3VibWl0RXJyb3I6IGZ1bmN0aW9uKGVycm9yX21lc3NhZ2UpIHtcblx0XHRcdCQoXG5cdFx0XHRcdCcud29vY29tbWVyY2UtTm90aWNlR3JvdXAtY2hlY2tvdXQsIC53b29jb21tZXJjZS1lcnJvciwgLndvb2NvbW1lcmNlLW1lc3NhZ2UnXG5cdFx0XHQpLnJlbW92ZSgpO1xuXHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0uZm9ybS5wcmVwZW5kKFxuXHRcdFx0XHQnPGRpdiBjbGFzcz1cIndvb2NvbW1lcmNlLU5vdGljZUdyb3VwIHdvb2NvbW1lcmNlLU5vdGljZUdyb3VwLWNoZWNrb3V0XCI+JyArXG5cdFx0XHRcdFx0ZXJyb3JfbWVzc2FnZSArXG5cdFx0XHRcdFx0JzwvZGl2Pidcblx0XHRcdCk7XG5cdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfZm9ybS5mb3JtLnJlbW92ZUNsYXNzKCdwcm9jZXNzaW5nJykudW5ibG9jaygpO1xuXHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0uZm9ybVxuXHRcdFx0XHQuZmluZCgnLmlucHV0LXRleHQsIHNlbGVjdCwgaW5wdXQ6Y2hlY2tib3gnKVxuXHRcdFx0XHQuYmx1cigpO1xuXG5cdFx0XHR2YXIgc2VsZWN0b3IgPSAnJztcblxuXHRcdFx0aWYgKCQoJyNhZGRfcGF5bWVudF9tZXRob2QnKS5sZW5ndGgpIHtcblx0XHRcdFx0c2VsZWN0b3IgPSAkKCcjYWRkX3BheW1lbnRfbWV0aG9kJyk7XG5cdFx0XHR9XG5cblx0XHRcdGlmICgkKCcjb3JkZXJfcmV2aWV3JykubGVuZ3RoKSB7XG5cdFx0XHRcdHNlbGVjdG9yID0gJCgnI29yZGVyX3JldmlldycpO1xuXHRcdFx0fVxuXG5cdFx0XHRpZiAoJCgnZm9ybS5jaGVja291dCcpLmxlbmd0aCkge1xuXHRcdFx0XHRzZWxlY3RvciA9ICQoJ2Zvcm0uY2hlY2tvdXQnKTtcblx0XHRcdH1cblxuXHRcdFx0aWYgKHNlbGVjdG9yLmxlbmd0aCkge1xuXHRcdFx0XHQkKCdodG1sLCBib2R5JykuYW5pbWF0ZShcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHRzY3JvbGxUb3A6IHNlbGVjdG9yLm9mZnNldCgpLnRvcCAtIDEwMFxuXHRcdFx0XHRcdH0sXG5cdFx0XHRcdFx0NTAwXG5cdFx0XHRcdCk7XG5cdFx0XHR9XG5cblx0XHRcdCQoZG9jdW1lbnQuYm9keSkudHJpZ2dlcignY2hlY2tvdXRfZXJyb3InKTtcblx0XHRcdHdjdl9zdHJpcGVfY29ubmVjdF9mb3JtLnVuYmxvY2soKTtcblx0XHR9LFxuXG5cdFx0LyoqXG5cdFx0ICogSGFuZGxlcyBjaGFuZ2VzIGluIHRoZSBoYXNoIGluIG9yZGVyIHRvIHNob3cgYSBtb2RhbCBmb3IgUGF5bWVudEludGVudCBjb25maXJtYXRpb25zLlxuXHRcdCAqXG5cdFx0ICogTGlzdGVucyBmb3IgYGhhc2hjaGFuZ2VgIGV2ZW50cyBhbmQgY2hlY2tzIGZvciBhIGhhc2ggaW4gdGhlIGZvbGxvd2luZyBmb3JtYXQ6XG5cdFx0ICogI2NvbmZpcm0tcGktPGludGVudENsaWVudFNlY3JldD46PHN1Y2Nlc3NSZWRpcmVjdFVSTD5cblx0XHQgKlxuXHRcdCAqIElmIHN1Y2ggYSBoYXNoIGFwcGVhcnMsIHRoZSBwYXJ0aWFscyB3aWxsIGJlIHVzZWQgdG8gY2FsbCBgc3RyaXBlLmhhbmRsZUNhcmRBY3Rpb25gXG5cdFx0ICogaW4gb3JkZXIgdG8gYWxsb3cgY3VzdG9tZXJzIHRvIGNvbmZpcm0gYW4gM0RTL1NDQSBhdXRob3JpemF0aW9uLlxuXHRcdCAqXG5cdFx0ICogVGhvc2UgcmVkaXJlY3RzL2hhc2hlcyBhcmUgZ2VuZXJhdGVkIGluIGBXQ1ZfU0NfUGF5bWVudF9HYXRld2F5OjpnZW5lcmF0ZV9jaGFyZ2VzX3RyYW5zZmVyc19wYXltZW50YC5cblx0XHQgKi9cblx0XHRvbkhhc2hDaGFuZ2U6IGZ1bmN0aW9uKCkge1xuXHRcdFx0dmFyIHBhcnRpYWxzID0gd2luZG93LmxvY2F0aW9uLmhhc2gubWF0Y2goXG5cdFx0XHRcdC9eIz9jb25maXJtLXBpLShbXjpdKyk6KC4rKSQvXG5cdFx0XHQpO1xuXG5cdFx0XHRpZiAoIXBhcnRpYWxzIHx8IDMgPiBwYXJ0aWFscy5sZW5ndGgpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHR2YXIgaW50ZW50Q2xpZW50U2VjcmV0ID0gcGFydGlhbHNbMV07XG5cdFx0XHR2YXIgcmVkaXJlY3RVUkwgPSBkZWNvZGVVUklDb21wb25lbnQocGFydGlhbHNbMl0pO1xuXG5cdFx0XHQvLyBDbGVhbnVwIHRoZSBVUkxcblx0XHRcdHdpbmRvdy5sb2NhdGlvbi5oYXNoID0gJyc7XG5cblx0XHRcdHdjdl9zdHJpcGVfY29ubmVjdF9mb3JtLm9wZW5JbnRlbnRNb2RhbChcblx0XHRcdFx0aW50ZW50Q2xpZW50U2VjcmV0LFxuXHRcdFx0XHRyZWRpcmVjdFVSTFxuXHRcdFx0KTtcblx0XHR9LFxuXG5cdFx0LyoqXG5cdFx0ICogT3BlbnMgdGhlIG1vZGFsIGZvciBQYXltZW50SW50ZW50IGF1dGhvcml6YXRpb25zLlxuXHRcdCAqXG5cdFx0ICogQHBhcmFtIHtzdHJpbmd9ICBpbnRlbnRDbGllbnRTZWNyZXQgVGhlIGNsaWVudCBzZWNyZXQgb2YgdGhlIGludGVudC5cblx0XHQgKiBAcGFyYW0ge3N0cmluZ30gIHJlZGlyZWN0VVJMICAgICAgICBUaGUgVVJMIHRvIHBpbmcgb24gZmFpbCBvciByZWRpcmVjdCB0byBvbiBzdWNjZXNzLlxuXHRcdCAqIEBwYXJhbSB7Ym9vbGVhbn0gYWx3YXlzUmVkaXJlY3QgICAgIElmIHNldCB0byB0cnVlLCBhbiBpbW1lZGlhdGUgcmVkaXJlY3Qgd2lsbCBoYXBwZW4gbm8gbWF0dGVyIHRoZSByZXN1bHQuXG5cdFx0ICogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgSWYgbm90LCBhbiBlcnJvciB3aWxsIGJlIGRpc3BsYXllZCBvbiBmYWlsdXJlLlxuXHRcdCAqL1xuXHRcdG9wZW5JbnRlbnRNb2RhbDogZnVuY3Rpb24oXG5cdFx0XHRpbnRlbnRDbGllbnRTZWNyZXQsXG5cdFx0XHRyZWRpcmVjdFVSTCxcblx0XHRcdGFsd2F5c1JlZGlyZWN0XG5cdFx0KSB7XG5cdFx0XHRzdHJpcGVcblx0XHRcdFx0LmhhbmRsZUNhcmRBY3Rpb24oaW50ZW50Q2xpZW50U2VjcmV0KVxuXHRcdFx0XHQudGhlbihmdW5jdGlvbihyZXNwb25zZSkge1xuXHRcdFx0XHRcdGlmIChyZXNwb25zZS5lcnJvcikge1xuXHRcdFx0XHRcdFx0dGhyb3cgcmVzcG9uc2UuZXJyb3I7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0aWYgKFxuXHRcdFx0XHRcdFx0J3JlcXVpcmVzX2NvbmZpcm1hdGlvbicgIT09XG5cdFx0XHRcdFx0XHRyZXNwb25zZS5wYXltZW50SW50ZW50LnN0YXR1c1xuXHRcdFx0XHRcdCkge1xuXHRcdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHRcdH1cblxuXHRcdFx0XHRcdHdpbmRvdy5sb2NhdGlvbiA9IHJlZGlyZWN0VVJMO1xuXHRcdFx0XHR9KVxuXHRcdFx0XHQuY2F0Y2goZnVuY3Rpb24oZXJyb3IpIHtcblx0XHRcdFx0XHRpZiAoYWx3YXlzUmVkaXJlY3QpIHtcblx0XHRcdFx0XHRcdHJldHVybiAod2luZG93LmxvY2F0aW9uID0gcmVkaXJlY3RVUkwpO1xuXHRcdFx0XHRcdH1cblxuXHRcdFx0XHRcdCQoZG9jdW1lbnQuYm9keSkudHJpZ2dlcignc3RyaXBlQ29ubmVjdEVycm9yJywge1xuXHRcdFx0XHRcdFx0ZXJyb3I6IGVycm9yXG5cdFx0XHRcdFx0fSk7XG5cdFx0XHRcdFx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0uZm9ybS5yZW1vdmVDbGFzcygncHJvY2Vzc2luZycpO1xuXG5cdFx0XHRcdFx0Ly8gUmVwb3J0IGJhY2sgdG8gdGhlIHNlcnZlci5cblx0XHRcdFx0XHQkLmdldChyZWRpcmVjdFVSTCArICcmaXNfYWpheCcpO1xuXHRcdFx0XHR9KTtcblx0XHR9XG5cdH07XG5cblx0d2N2X3N0cmlwZV9jb25uZWN0X2Zvcm0uaW5pdCgpO1xufSk7XG4iXX0=

//# sourceMappingURL=stripe-checkout.js.map
