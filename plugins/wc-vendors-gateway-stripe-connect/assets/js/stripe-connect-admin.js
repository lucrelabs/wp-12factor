(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

/**	global wcv_stripe_admin_args **/
jQuery(function ($) {
  'use strict';
  /**
   * Object to handle Stripe admin functions.
   */

  var wcv_stripe_connect_admin = {
    isTestMode: function isTestMode() {
      return $('#woocommerce_stripe-connect_testmode').is(':checked');
    },
    getSecretKey: function getSecretKey() {
      if (wcv_stripe_connect_admin.isTestMode()) {
        return $('#woocommerce_stripe-connect_test_secret_key').val();
      } else {
        return $('#woocommerce_stripe-connect_secret_key').val();
      }
    },
    checkKeys: function checkKeys() {
      var test_secret_key = $('#woocommerce_stripe-connect_test_secret_key'),
          test_publishable_key = $('#woocommerce_stripe-connect_test_publishable_key'),
          test_client_id = $('#woocommerce_stripe-connect_test_client_id'),
          live_client_id = $('#woocommerce_stripe-connect_client_id'),
          live_secret_key = $('#woocommerce_stripe-connect_secret_key'),
          live_publishable_key = $('#woocommerce_stripe-connect_publishable_key');

      if (wcv_stripe_connect_admin.isTestMode()) {
        if (test_secret_key.val() == "" || test_publishable_key.val() === "") return;

        if (test_secret_key.val() === test_publishable_key.val()) {
          alert(wcv_stripe_admin_args.keys_match_error);
        }
      } else {
        if (live_secret_key.val() == "" || live_publishable_key.val() === "") return;

        if (live_secret_key.val() === live_publishable_key.val()) {
          alert(wcv_stripe_admin_args.keys_match_error);
        }
      }
    },

    /**
     * Initialize.
     */
    init: function init() {
      $(document.body).on('change', '#woocommerce_stripe-connect_testmode', function () {
        var test_secret_key = $('#woocommerce_stripe-connect_test_secret_key').parents('tr').eq(0),
            test_publishable_key = $('#woocommerce_stripe-connect_test_publishable_key').parents('tr').eq(0),
            test_client_id = $('#woocommerce_stripe-connect_test_client_id').parents('tr').eq(0),
            live_client_id = $('#woocommerce_stripe-connect_client_id').parents('tr').eq(0),
            live_secret_key = $('#woocommerce_stripe-connect_secret_key').parents('tr').eq(0),
            live_publishable_key = $('#woocommerce_stripe-connect_publishable_key').parents('tr').eq(0);

        if ($(this).is(':checked')) {
          test_client_id.show();
          test_secret_key.show();
          test_publishable_key.show();
          live_client_id.hide();
          live_secret_key.hide();
          live_publishable_key.hide();
        } else {
          test_client_id.hide();
          test_secret_key.hide();
          test_publishable_key.hide();
          live_client_id.show();
          live_secret_key.show();
          live_publishable_key.show();
        }
      });
      $('#woocommerce_stripe-connect_testmode').change(); // Toggle Stripe Checkout settings.

      $('#woocommerce_stripe-connect_stripe_checkout').change(function () {
        if ($(this).is(':checked')) {
          $('#woocommerce_stripe-connect_stripe_checkout_image, #woocommerce_stripe-connect_stripe_checkout_description').closest('tr').show();
        } else {
          $('#woocommerce_stripe-connect_stripe_checkout_image, #woocommerce_stripe-connect_stripe_checkout_description').closest('tr').hide();
        }
      }).change(); // Toggle Payment Request buttons settings.

      $('#woocommerce_stripe-connect_payment_request').change(function () {
        if ($(this).is(':checked')) {
          $('#woocommerce_stripe-connect_payment_request_button_theme, #woocommerce_stripe-connect_payment_request_button_type, #woocommerce_stripe-connect_payment_request_button_height').closest('tr').show();
        } else {
          $('#woocommerce_stripe-connect_payment_request_button_theme, #woocommerce_stripe-connect_payment_request_button_type, #woocommerce_stripe-connect_payment_request_button_height').closest('tr').hide();
        }
      }).change(); // Check the keys to make sure they are not the same.

      $(document.body).on('change', '.stripe-connect-key', function () {
        wcv_stripe_connect_admin.checkKeys();
      });
    }
  };
  wcv_stripe_connect_admin.init();
});

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJhc3NldHMvc3JjL2pzL3N0cmlwZS1jb25uZWN0LWFkbWluLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7QUNBQTtBQUNBLE1BQU0sQ0FBRSxVQUFVLENBQVYsRUFBYztBQUNyQjtBQUVBOzs7O0FBR0EsTUFBSSx3QkFBd0IsR0FBRztBQUM5QixJQUFBLFVBQVUsRUFBRSxzQkFBVztBQUN0QixhQUFPLENBQUMsQ0FBRSxzQ0FBRixDQUFELENBQTRDLEVBQTVDLENBQWdELFVBQWhELENBQVA7QUFDQSxLQUg2QjtBQUs5QixJQUFBLFlBQVksRUFBRSx3QkFBVztBQUN4QixVQUFLLHdCQUF3QixDQUFDLFVBQXpCLEVBQUwsRUFBNkM7QUFDNUMsZUFBTyxDQUFDLENBQUUsNkNBQUYsQ0FBRCxDQUFtRCxHQUFuRCxFQUFQO0FBQ0EsT0FGRCxNQUVPO0FBQ04sZUFBTyxDQUFDLENBQUUsd0NBQUYsQ0FBRCxDQUE4QyxHQUE5QyxFQUFQO0FBQ0E7QUFDRCxLQVg2QjtBQWE5QixJQUFBLFNBQVMsRUFBRSxxQkFBVTtBQUVwQixVQUFJLGVBQWUsR0FBRyxDQUFDLENBQUUsNkNBQUYsQ0FBdkI7QUFBQSxVQUNDLG9CQUFvQixHQUFHLENBQUMsQ0FBRSxrREFBRixDQUR6QjtBQUFBLFVBRUMsY0FBYyxHQUFJLENBQUMsQ0FBRSw0Q0FBRixDQUZwQjtBQUFBLFVBR0MsY0FBYyxHQUFHLENBQUMsQ0FBRSx1Q0FBRixDQUhuQjtBQUFBLFVBSUMsZUFBZSxHQUFHLENBQUMsQ0FBRSx3Q0FBRixDQUpwQjtBQUFBLFVBS0Msb0JBQW9CLEdBQUcsQ0FBQyxDQUFFLDZDQUFGLENBTHpCOztBQU9BLFVBQUssd0JBQXdCLENBQUMsVUFBekIsRUFBTCxFQUE2QztBQUU1QyxZQUFLLGVBQWUsQ0FBQyxHQUFoQixNQUF5QixFQUF6QixJQUErQixvQkFBb0IsQ0FBQyxHQUFyQixPQUErQixFQUFuRSxF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TUFOSixHQTlCZ0IsQ0FzQ2hCOztBQUNBLE1BQUEsQ0FBQyxDQUFFLDZDQUFGLENBQUQsQ0FBbUQsTUFBbkQsQ0FBMkQsWUFBVztBQUNyRSxZQUFLLENBQUMsQ0FBRSxJQUFGLENBQUQsQ0FBVSxFQUFWLENBQWMsVUFBZCxDQUFMLEVBQWtDO0FBQ2pDLFVBQUEsQ0FBQyxDQUFFLDhLQUFGLENBQUQsQ0FBb0wsT0FBcEwsQ0FBNkwsSUFBN0wsRUFBb00sSUFBcE07QUFDQSxTQUZELE1BRU87QUFDTixVQUFBLENBQUMsQ0FBRSw4S0FBRixDQUFELENBQW9MLE9BQXBMLENBQTZMLElBQTdMLEVBQW9NLElBQXBNO0FBQ0E7QUFDRCxPQU5ELEVBTUksTUFOSixHQXZDZ0IsQ0ErQ2hCOztBQUNBLE1BQUEsQ0FBQyxDQUFFLFFBQVEsQ0FBQyxJQUFYLENBQUQsQ0FBbUIsRUFBbkIsQ0FBdUIsUUFBdkIsRUFBaUMscUJBQWpDLEVBQXdELFlBQVc7QUFDbEUsUUFBQSx3QkFBd0IsQ0FBQyxTQUF6QjtBQUNBLE9BRkQ7QUFHQTtBQTlGNkIsR0FBL0I7QUFpR0EsRUFBQSx3QkFBd0IsQ0FBQyxJQUF6QjtBQUNBLENBeEdLLENBQU4iLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbigpe2Z1bmN0aW9uIHIoZSxuLHQpe2Z1bmN0aW9uIG8oaSxmKXtpZighbltpXSl7aWYoIWVbaV0pe3ZhciBjPVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmU7aWYoIWYmJmMpcmV0dXJuIGMoaSwhMCk7aWYodSlyZXR1cm4gdShpLCEwKTt2YXIgYT1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK2krXCInXCIpO3Rocm93IGEuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixhfXZhciBwPW5baV09e2V4cG9ydHM6e319O2VbaV1bMF0uY2FsbChwLmV4cG9ydHMsZnVuY3Rpb24ocil7dmFyIG49ZVtpXVsxXVtyXTtyZXR1cm4gbyhufHxyKX0scCxwLmV4cG9ydHMscixlLG4sdCl9cmV0dXJuIG5baV0uZXhwb3J0c31mb3IodmFyIHU9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZSxpPTA7aTx0Lmxlbmd0aDtpKyspbyh0W2ldKTtyZXR1cm4gb31yZXR1cm4gcn0pKCkiLCIvKipcdGdsb2JhbCB3Y3Zfc3RyaXBlX2FkbWluX2FyZ3MgKiovXG5qUXVlcnkoIGZ1bmN0aW9uKCAkICkge1xuXHQndXNlIHN0cmljdCc7XG5cblx0LyoqXG5cdCAqIE9iamVjdCB0byBoYW5kbGUgU3RyaXBlIGFkbWluIGZ1bmN0aW9ucy5cblx0ICovXG5cdHZhciB3Y3Zfc3RyaXBlX2Nvbm5lY3RfYWRtaW4gPSB7XG5cdFx0aXNUZXN0TW9kZTogZnVuY3Rpb24oKSB7XG5cdFx0XHRyZXR1cm4gJCggJyN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF90ZXN0bW9kZScgKS5pcyggJzpjaGVja2VkJyApO1xuXHRcdH0sXG5cblx0XHRnZXRTZWNyZXRLZXk6IGZ1bmN0aW9uKCkge1xuXHRcdFx0aWYgKCB3Y3Zfc3RyaXBlX2Nvbm5lY3RfYWRtaW4uaXNUZXN0TW9kZSgpICkge1xuXHRcdFx0XHRyZXR1cm4gJCggJyN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF90ZXN0X3NlY3JldF9rZXknICkudmFsKCk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRyZXR1cm4gJCggJyN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF9zZWNyZXRfa2V5JyApLnZhbCgpO1xuXHRcdFx0fVxuXHRcdH0sXG5cblx0XHRjaGVja0tleXM6IGZ1bmN0aW9uKCl7XG5cblx0XHRcdHZhciB0ZXN0X3NlY3JldF9rZXkgPSAkKCAnI3dvb2NvbW1lcmNlX3N0cmlwZS1jb25uZWN0X3Rlc3Rfc2VjcmV0X2tleScgKSxcblx0XHRcdFx0dGVzdF9wdWJsaXNoYWJsZV9rZXkgPSAkKCAnI3dvb2NvbW1lcmNlX3N0cmlwZS1jb25uZWN0X3Rlc3RfcHVibGlzaGFibGVfa2V5JyApLFxuXHRcdFx0XHR0ZXN0X2NsaWVudF9pZCAgPSAkKCAnI3dvb2NvbW1lcmNlX3N0cmlwZS1jb25uZWN0X3Rlc3RfY2xpZW50X2lkJyApLFxuXHRcdFx0XHRsaXZlX2NsaWVudF9pZCA9ICQoICcjd29vY29tbWVyY2Vfc3RyaXBlLWNvbm5lY3RfY2xpZW50X2lkJyApLFxuXHRcdFx0XHRsaXZlX3NlY3JldF9rZXkgPSAkKCAnI3dvb2NvbW1lcmNlX3N0cmlwZS1jb25uZWN0X3NlY3JldF9rZXknICksXG5cdFx0XHRcdGxpdmVfcHVibGlzaGFibGVfa2V5ID0gJCggJyN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF9wdWJsaXNoYWJsZV9rZXknICk7XG5cblx0XHRcdGlmICggd2N2X3N0cmlwZV9jb25uZWN0X2FkbWluLmlzVGVzdE1vZGUoKSApIHtcblxuXHRcdFx0XHRpZiAoIHRlc3Rfc2VjcmV0X2tleS52YWwoKSA9PSBcIlwiIHx8IHRlc3RfcHVibGlzaGFibGVfa2V5LnZhbCgpID09PSBcIlwiICkgcmV0dXJuO1xuXG5cdFx0XHRcdGlmICggdGVzdF9zZWNyZXRfa2V5LnZhbCgpID09PSB0ZXN0X3B1Ymxpc2hhYmxlX2tleS52YWwoKSApe1xuXHRcdFx0XHRcdGFsZXJ0KCB3Y3Zfc3RyaXBlX2FkbWluX2FyZ3Mua2V5c19tYXRjaF9lcnJvciApO1xuXHRcdFx0XHR9XG5cblx0XHRcdH0gZWxzZSB7XG5cblx0XHRcdFx0aWYgKCBsaXZlX3NlY3JldF9rZXkudmFsKCkgPT0gXCJcIiB8fCBsaXZlX3B1Ymxpc2hhYmxlX2tleS52YWwoKSA9PT0gXCJcIiApIHJldHVybjtcblxuXHRcdFx0XHRpZiAgKCBsaXZlX3NlY3JldF9rZXkudmFsKCkgPT09IGxpdmVfcHVibGlzaGFibGVfa2V5LnZhbCgpICkge1xuXHRcdFx0XHRcdGFsZXJ0KCB3Y3Zfc3RyaXBlX2FkbWluX2FyZ3Mua2V5c19tYXRjaF9lcnJvciApO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fSxcblxuXHRcdC8qKlxuXHRcdCAqIEluaXRpYWxpemUuXG5cdFx0ICovXG5cdFx0aW5pdDogZnVuY3Rpb24oKSB7XG5cblx0XHRcdCQoIGRvY3VtZW50LmJvZHkgKS5vbiggJ2NoYW5nZScsICcjd29vY29tbWVyY2Vfc3RyaXBlLWNvbm5lY3RfdGVzdG1vZGUnLCBmdW5jdGlvbigpIHtcblx0XHRcdFx0dmFyIHRlc3Rfc2VjcmV0X2tleSA9ICQoICcjd29vY29tbWVyY2Vfc3RyaXBlLWNvbm5lY3RfdGVzdF9zZWNyZXRfa2V5JyApLnBhcmVudHMoICd0cicgKS5lcSggMCApLFxuXHRcdFx0XHRcdHRlc3RfcHVibGlzaGFibGVfa2V5ID0gJCggJyN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF90ZXN0X3B1Ymxpc2hhYmxlX2tleScgKS5wYXJlbnRzKCAndHInICkuZXEoIDAgKSxcblx0XHRcdFx0XHR0ZXN0X2NsaWVudF9pZCAgPSAkKCAnI3dvb2NvbW1lcmNlX3N0cmlwZS1jb25uZWN0X3Rlc3RfY2xpZW50X2lkJyApLnBhcmVudHMoICd0cicgKS5lcSggMCApLFxuXHRcdFx0XHRcdGxpdmVfY2xpZW50X2lkID0gJCggJyN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF9jbGllbnRfaWQnICkucGFyZW50cyggJ3RyJyApLmVxKCAwICksXG5cdFx0XHRcdFx0bGl2ZV9zZWNyZXRfa2V5ID0gJCggJyN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF9zZWNyZXRfa2V5JyApLnBhcmVudHMoICd0cicgKS5lcSggMCApLFxuXHRcdFx0XHRcdGxpdmVfcHVibGlzaGFibGVfa2V5ID0gJCggJyN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF9wdWJsaXNoYWJsZV9rZXknICkucGFyZW50cyggJ3RyJyApLmVxKCAwICk7XG5cblx0XHRcdFx0aWYgKCAkKCB0aGlzICkuaXMoICc6Y2hlY2tlZCcgKSApIHtcblx0XHRcdFx0XHR0ZXN0X2NsaWVudF9pZC5zaG93KCk7XG5cdFx0XHRcdFx0dGVzdF9zZWNyZXRfa2V5LnNob3coKTtcblx0XHRcdFx0XHR0ZXN0X3B1Ymxpc2hhYmxlX2tleS5zaG93KCk7XG5cdFx0XHRcdFx0bGl2ZV9jbGllbnRfaWQuaGlkZSgpO1xuXHRcdFx0XHRcdGxpdmVfc2VjcmV0X2tleS5oaWRlKCk7XG5cdFx0XHRcdFx0bGl2ZV9wdWJsaXNoYWJsZV9rZXkuaGlkZSgpO1xuXHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdHRlc3RfY2xpZW50X2lkLmhpZGUoKTtcblx0XHRcdFx0XHR0ZXN0X3NlY3JldF9rZXkuaGlkZSgpO1xuXHRcdFx0XHRcdHRlc3RfcHVibGlzaGFibGVfa2V5LmhpZGUoKTtcblx0XHRcdFx0XHRsaXZlX2NsaWVudF9pZC5zaG93KCk7XG5cdFx0XHRcdFx0bGl2ZV9zZWNyZXRfa2V5LnNob3coKTtcblx0XHRcdFx0XHRsaXZlX3B1Ymxpc2hhYmxlX2tleS5zaG93KCk7XG5cdFx0XHRcdH1cblx0XHRcdH0gKTtcblxuXHRcdFx0JCggJyN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF90ZXN0bW9kZScgKS5jaGFuZ2UoKTtcblxuXHRcdFx0Ly8gVG9nZ2xlIFN0cmlwZSBDaGVja291dCBzZXR0aW5ncy5cblx0XHRcdCQoICcjd29vY29tbWVyY2Vfc3RyaXBlLWNvbm5lY3Rfc3RyaXBlX2NoZWNrb3V0JyApLmNoYW5nZSggZnVuY3Rpb24oKSB7XG5cdFx0XHRcdGlmICggJCggdGhpcyApLmlzKCAnOmNoZWNrZWQnICkgKSB7XG5cdFx0XHRcdFx0JCggJyN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF9zdHJpcGVfY2hlY2tvdXRfaW1hZ2UsICN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF9zdHJpcGVfY2hlY2tvdXRfZGVzY3JpcHRpb24nICkuY2xvc2VzdCggJ3RyJyApLnNob3coKTtcblx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHQkKCAnI3dvb2NvbW1lcmNlX3N0cmlwZS1jb25uZWN0X3N0cmlwZV9jaGVja291dF9pbWFnZSwgI3dvb2NvbW1lcmNlX3N0cmlwZS1jb25uZWN0X3N0cmlwZV9jaGVja291dF9kZXNjcmlwdGlvbicgKS5jbG9zZXN0KCAndHInICkuaGlkZSgpO1xuXHRcdFx0XHR9XG5cdFx0XHR9ICkuY2hhbmdlKCk7XG5cblx0XHRcdC8vIFRvZ2dsZSBQYXltZW50IFJlcXVlc3QgYnV0dG9ucyBzZXR0aW5ncy5cblx0XHRcdCQoICcjd29vY29tbWVyY2Vfc3RyaXBlLWNvbm5lY3RfcGF5bWVudF9yZXF1ZXN0JyApLmNoYW5nZSggZnVuY3Rpb24oKSB7XG5cdFx0XHRcdGlmICggJCggdGhpcyApLmlzKCAnOmNoZWNrZWQnICkgKSB7XG5cdFx0XHRcdFx0JCggJyN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF9wYXltZW50X3JlcXVlc3RfYnV0dG9uX3RoZW1lLCAjd29vY29tbWVyY2Vfc3RyaXBlLWNvbm5lY3RfcGF5bWVudF9yZXF1ZXN0X2J1dHRvbl90eXBlLCAjd29vY29tbWVyY2Vfc3RyaXBlLWNvbm5lY3RfcGF5bWVudF9yZXF1ZXN0X2J1dHRvbl9oZWlnaHQnICkuY2xvc2VzdCggJ3RyJyApLnNob3coKTtcblx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHQkKCAnI3dvb2NvbW1lcmNlX3N0cmlwZS1jb25uZWN0X3BheW1lbnRfcmVxdWVzdF9idXR0b25fdGhlbWUsICN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF9wYXltZW50X3JlcXVlc3RfYnV0dG9uX3R5cGUsICN3b29jb21tZXJjZV9zdHJpcGUtY29ubmVjdF9wYXltZW50X3JlcXVlc3RfYnV0dG9uX2hlaWdodCcgKS5jbG9zZXN0KCAndHInICkuaGlkZSgpO1xuXHRcdFx0XHR9XG5cdFx0XHR9ICkuY2hhbmdlKCk7XG5cblx0XHRcdC8vIENoZWNrIHRoZSBrZXlzIHRvIG1ha2Ugc3VyZSB0aGV5IGFyZSBub3QgdGhlIHNhbWUuXG5cdFx0XHQkKCBkb2N1bWVudC5ib2R5ICkub24oICdjaGFuZ2UnLCAnLnN0cmlwZS1jb25uZWN0LWtleScsIGZ1bmN0aW9uKCkge1xuXHRcdFx0XHR3Y3Zfc3RyaXBlX2Nvbm5lY3RfYWRtaW4uY2hlY2tLZXlzKCk7XG5cdFx0XHR9ICk7XG5cdFx0fVxuXHR9O1xuXG5cdHdjdl9zdHJpcGVfY29ubmVjdF9hZG1pbi5pbml0KCk7XG59ICk7XG4iXX0=

//# sourceMappingURL=stripe-connect-admin.js.map
