<?php
/**
 * WC Vendors - Stripe Commissions & Gateway Gateway Stripe Connect Subs.
 *
 * @since   2.0.0
 * @package WC_Vendors_Stripe_Commissions_Gateway
 */

/**
 * WC Vendors - Stripe Commissions & Gateway Gateway Stripe Connect Subs.
 *
 * @since 2.0.0
 * @todo replace text domain
 */
class WCV_SC_Gateway_Stripe_Connect_Subs extends WCV_SC_Gateway_Stripe_Connect {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {
			add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 2 );
			add_action( 'wcs_resubscribe_order_created', array( $this, 'delete_resubscribe_meta' ), 10 );
			add_action( 'wcs_renewal_order_created', array( $this, 'delete_renewal_meta' ), 10 );
			add_action( 'woocommerce_subscription_failing_payment_method_updated_stripe', array( $this, 'update_failing_payment_method' ), 10, 2 );
			add_action( 'wcv_sc_cards_payment_fields', array( $this, 'display_update_subs_payment_checkout' ) );
			add_action( 'wcv_sc_add_payment_method_' . $this->id . '_success', array( $this, 'handle_add_payment_method_success' ), 10, 2 );

			// display the credit card used for a subscription in the "My Subscriptions" table
			add_filter( 'woocommerce_my_subscriptions_payment_method', array( $this, 'maybe_render_subscription_payment_method' ), 10, 2 );

			// allow store managers to manually set Stripe as the payment method on a subscription
			add_filter( 'woocommerce_subscription_payment_meta', array( $this, 'add_subscription_payment_meta' ), 10, 2 );
			add_filter( 'woocommerce_subscription_validate_payment_meta', array( $this, 'validate_subscription_payment_meta' ), 10, 2 );
			add_filter( 'wcv_sc_display_save_payment_method_checkbox', array( $this, 'maybe_hide_save_checkbox' ) );
		}
	}

	/**
	 * Checks to see if we need to hide the save checkbox field.
	 * Because when cart contains a subs product, it will save regardless.
	 *
	 * @since 2.0.0
	 */
	public function maybe_hide_save_checkbox( $display_tokenization ) {
		if ( WC_Subscriptions_Cart::cart_contains_subscription() ) {
			return false;
		}

		return $display_tokenization;
	}

	/**
	 * Is $order_id a subscription?
	 *
	 * @param  int $order_id
	 * @return boolean
	 *
	 * @since 2.0.0
	 */
	public function has_subscription( $order_id ) {
		return ( function_exists( 'wcs_order_contains_subscription' ) && ( wcs_order_contains_subscription( $order_id ) || wcs_is_subscription( $order_id ) || wcs_order_contains_renewal( $order_id ) ) );
	}

	/**
	 * Checks if page is pay for order and change subs payment page.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_subs_change_payment() {
		return ( isset( $_GET['pay_for_order'] ) && isset( $_GET['change_payment_method'] ) );
	}

	/**
	 * Displays a checkbox to allow users to update all subs payments with new
	 * payment.
	 *
	 * @since 2.0.0
	 */
	public function display_update_subs_payment_checkout() {
		if (
			apply_filters( 'wcv_sc_display_update_subs_payment_method_card_checkbox', true ) &&
			wcs_user_has_subscription( get_current_user_id(), '', 'active' ) &&
			is_add_payment_method_page()
		) {
			printf(
				'<p class="form-row">
					<input id="wc-%1$s-update-subs-payment-method-card" name="wc-%1$s-update-subs-payment-method-card" type="checkbox" value="true" style="width:auto;" />
					<label for="wc-%1$s-update-subs-payment-method-card" style="display:inline;">%2$s</label>
				</p>',
				esc_attr( $this->id ),
				esc_html( apply_filters( 'wcv_sc_save_to_subs_text', __( 'Update the Payment Method used for all of my active subscriptions (optional).', 'wc-vendors-gateway-stripe-connect' ) ) )
			);
		}
	}

	/**
	 * Updates all active subscriptions payment method.
	 *
	 * @since 2.0.0
	 * @param string $source_id
	 * @param object $source_object
	 */
	public function handle_add_payment_method_success( $source_id, $source_object ) {
		if ( isset( $_POST[ 'wc-' . $this->id . '-update-subs-payment-method-card' ] ) ) {
			$all_subs = wcs_get_users_subscriptions();

			if ( ! empty( $all_subs ) ) {
				foreach ( $all_subs as $sub ) {
					if ( 'active' === $sub->get_status() ) {
						update_post_meta( $sub->get_id(), '_stripe_source_id', $source_id );
						update_post_meta( $sub->get_id(), '_payment_method', $this->id );
						update_post_meta( $sub->get_id(), '_payment_method_title', $this->method_title );
					}
				}
			}
		}
	}

	/**
	 * Process the payment method change for subscriptions.
	 *
	 * @since 2.0.0
	 * @param int $order_id
	 */
	public function change_subs_payment_method( $order_id ) {
		try {
			$subscription    = wc_get_order( $order_id );
			$prepared_source = $this->prepare_source( get_current_user_id(), true );
			$source_object   = $prepared_source->source_object;

			// Check if we don't allow prepaid credit cards.
			if ( ! apply_filters( 'wcv_sc_allow_prepaid_card', true ) && $this->is_prepaid_card( $source_object ) ) {
				$localized_message = __( 'Sorry, we\'re not accepting prepaid cards at this time. Your credit card has not been charge. Please try with alternative payment method.', 'wc-vendors-gateway-stripe-connect' );
				throw new WCV_SC_Exception( print_r( $source_object, true ), $localized_message );
			}

			if ( empty( $prepared_source->source ) ) {
				$localized_message = __( 'Payment processing failed. Please retry.', 'wc-vendors-gateway-stripe-connect' );
				throw new WCV_SC_Exception( print_r( $prepared_source, true ), $localized_message );
			}

			$this->save_source_to_order( $subscription, $prepared_source );

			do_action( 'wcv_sc_change_subs_payment_method_success', $prepared_source->source, $prepared_source );

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $subscription ),
			);
		} catch ( WCV_SC_Exception $e ) {
			wc_add_notice( $e->getLocalizedMessage(), 'error' );
			WCV_SC_Logger::log( 'Error: ' . $e->getMessage() );
		}
	}

	/**
	 * Process the payment based on type.
	 *
	 * @param  int $order_id
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public function process_payment( $order_id, $retry = true, $force_save_source = false, $previous_error = false ) {
		if ( $this->has_subscription( $order_id ) ) {
			if ( $this->is_subs_change_payment() ) {
				return $this->change_subs_payment_method( $order_id );
			}

			// Regular payment with force customer enabled
			return parent::process_payment( $order_id, $retry, true, $previous_error );
		} else {
			return parent::process_payment( $order_id, $retry, $force_save_source, $previous_error );
		}
	}

	/**
	 * Scheduled_subscription_payment function.
	 *
	 * @param $amount_to_charge float The amount to charge.
	 * @param $renewal_order WC_Order A WC_Order object created to record the renewal payment.
	 *
	 * @since 2.0.0
	 */
	public function scheduled_subscription_payment( $amount_to_charge, $renewal_order ) {
		$this->process_subscription_payment( $amount_to_charge, $renewal_order, true, false );
	}

	/**
	 * Process_subscription_payment function.
	 *
	 * @param float  $amount
	 * @param mixed  $renewal_order
	 * @param bool   $retry Should we retry the process?
	 * @param object $previous_error
	 *
	 * @since 2.0.0
	 */
	public function process_subscription_payment( $amount = 0.0, $renewal_order, $retry = true, $previous_error ) {
		try {
			if ( $amount * 100 < WCV_SC_Helper::get_minimum_amount() ) {
				/* translators: minimum amount */
				return new WP_Error( 'stripe_connect_error', sprintf( __( 'Sorry, the minimum allowed order total is %1$s to use this payment method.', 'wc-vendors-gateway-stripe-connect' ), wc_price( WCV_SC_Helper::get_minimum_amount() / 100 ) ) );
			}

			$order_id = $renewal_order->get_id();

			// Get source from order
			$prepared_source = $this->prepare_order_source( $renewal_order );
			$source_object   = $prepared_source->source_object;

			if ( ! $prepared_source->customer ) {
				return new WP_Error( 'stripe_error', __( 'Customer not found', 'wc-vendors-gateway-stripe-connect' ) );
			}

			WCV_SC_Logger::log( "Info: Begin processing subscription payment for order {$order_id} for the amount of {$amount}" );

			/*
			 If we're doing a retry and source is chargeable, we need to pass
			 * a different idempotency key and retry for success.
			 */
			if ( is_object( $source_object ) && empty( $source_object->error ) && $this->need_update_idempotency_key( $source_object, $previous_error ) ) {
				add_filter( 'wcv_sc_idempotency_key', array( $this, 'change_idempotency_key' ), 10, 2 );
			}

			if ( ( $this->is_no_such_source_error( $previous_error ) || $this->is_no_linked_source_error( $previous_error ) ) && apply_filters( 'wcv_sc_use_default_customer_source', true ) ) {
				// Passing empty source will charge customer default.
				$prepared_source->source = '';
			}

			$response = $this->generate_payment( $renewal_order, $prepared_source, $retry, true, $previous_error );

			if ( ! empty( $response->error ) ) {
				// We want to retry.
				if ( $this->is_retryable_error( $response->error ) ) {
					if ( $retry ) {
						// Don't do anymore retries after this.
						if ( 5 <= $this->retry_interval ) {
							return $this->process_subscription_payment( $amount, $renewal_order, false, $response->error );
						}

						sleep( $this->retry_interval );

						$this->retry_interval++;

						return $this->process_subscription_payment( $amount, $renewal_order, true, $response->error );
					} else {
						$localized_message = __( 'Sorry, we are unable to process your payment at this time. Please retry later.', 'wc-vendors-gateway-stripe-connect' );
						$renewal_order->add_order_note( $localized_message );
						throw new WCV_SC_Exception( print_r( $response, true ), $localized_message );
					}
				}

				$localized_messages = WCV_SC_Helper::get_localized_messages();

				if ( 'card_error' === $response->error->type ) {
					$localized_message = isset( $localized_messages[ $response->error->code ] ) ? $localized_messages[ $response->error->code ] : $response->error->message;
				} else {
					$localized_message = isset( $localized_messages[ $response->error->type ] ) ? $localized_messages[ $response->error->type ] : $response->error->message;
				}

				$renewal_order->add_order_note( $localized_message );

				throw new WCV_SC_Exception( print_r( $response, true ), $localized_message );
			}

			do_action( 'wc_gateway_stripe_process_payment', $response, $renewal_order );

			$this->process_response( $response, $renewal_order );
		} catch ( WCV_SC_Exception $e ) {
			WCV_SC_Logger::log( 'Error: ' . $e->getMessage() );

			do_action( 'wc_gateway_stripe_process_payment_error', $e, $renewal_order );

			/* translators: error message */
			$renewal_order->update_status( 'failed' );
		}
	}

	/**
	 * Updates other subscription sources.
	 *
	 * @since 2.0.0
	 */
	public function save_source_to_order( $order, $source ) {
		parent::save_source_to_order( $order, $source );

		$order_id = $order->get_id();

		// Also store it on the subscriptions being purchased or paid for in the order
		if ( function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order_id ) ) {
			$subscriptions = wcs_get_subscriptions_for_order( $order_id );
		} elseif ( function_exists( 'wcs_order_contains_renewal' ) && wcs_order_contains_renewal( $order_id ) ) {
			$subscriptions = wcs_get_subscriptions_for_renewal_order( $order_id );
		} else {
			$subscriptions = array();
		}

		foreach ( $subscriptions as $subscription ) {
			$subscription_id = $subscription->get_id();
			update_post_meta( $subscription_id, '_wcv_sc_customer_id', $source->customer );
			update_post_meta( $subscription_id, '_stripe_source_id', $source->source );
		}
	}

	/**
	 * Don't transfer Stripe customer/token meta to resubscribe orders.
	 *
	 * @param int $resubscribe_order The order created for the customer to resubscribe to the old expired/cancelled subscription
	 *
	 * @since 2.0.0
	 */
	public function delete_resubscribe_meta( $resubscribe_order ) {
		delete_post_meta( $resubscribe_order->get_id(), '_wcv_sc_customer_id' );
		delete_post_meta( $resubscribe_order->get_id(), '_stripe_source_id' );
		// For BW compat will remove in future
		delete_post_meta( $resubscribe_order->get_id(), '_stripe_card_id' );
		$this->delete_renewal_meta( $resubscribe_order );
	}

	/**
	 * Don't transfer Stripe fee/ID meta to renewal orders.
	 *
	 * @param int $resubscribe_order The order created for the customer to resubscribe to the old expired/cancelled subscription
	 *
	 * @since 2.0.0
	 */
	public function delete_renewal_meta( $renewal_order ) {
		WCV_SC_Helper::delete_stripe_fee( $renewal_order );
		WCV_SC_Helper::delete_stripe_net( $renewal_order );

		return $renewal_order;
	}

	/**
	 * Update the customer_id for a subscription after using Stripe to complete a payment to make up for
	 * an automatic renewal payment which previously failed.
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 * @param WC_Subscription $subscription The subscription for which the failing payment method relates.
	 * @param WC_Order        $renewal_order The order which recorded the successful payment (to make up for the failed automatic payment).
	 * @return void
	 */
	public function update_failing_payment_method( $subscription, $renewal_order ) {
		update_post_meta( $subscription->get_id(), '_wcv_sc_customer_id', $renewal_order->get_meta( '_wcv_sc_customer_id', true ) );
		update_post_meta( $subscription->get_id(), '_stripe_source_id', $renewal_order->get_meta( '_stripe_source_id', true ) );
	}

	/**
	 * Include the payment meta data required to process automatic recurring payments so that store managers can
	 * manually set up automatic recurring payments for a customer via the Edit Subscriptions screen in 2.0+.
	 *
	 * @since 2.0.0
	 * @param array           $payment_meta associative array of meta data required for automatic payments
	 * @param WC_Subscription $subscription An instance of a subscription object
	 * @return array
	 */
	public function add_subscription_payment_meta( $payment_meta, $subscription ) {
		$subscription_id = $subscription->get_id();
		$source_id       = get_post_meta( $subscription_id, '_stripe_source_id', true );

		// For BW compat will remove in future.
		if ( empty( $source_id ) ) {
			$source_id = get_post_meta( $subscription_id, '_stripe_card_id', true );

			// Take this opportunity to update the key name.
			update_post_meta( $subscription_id, '_stripe_source_id', $source_id );
			delete_post_meta( $subscription_id, '_stripe_card_id', $source_id );
		}

		$payment_meta[ $this->id ] = array(
			'post_meta' => array(
				'_wcv_sc_customer_id' => array(
					'value' => get_post_meta( $subscription_id, '_wcv_sc_customer_id', true ),
					'label' => 'Stripe Customer ID',
				),
				'_stripe_source_id'   => array(
					'value' => $source_id,
					'label' => 'Stripe Source ID',
				),
			),
		);

		return $payment_meta;
	}

	/**
	 * Validate the payment meta data required to process automatic recurring payments so that store managers can
	 * manually set up automatic recurring payments for a customer via the Edit Subscriptions screen in 2.0+.
	 *
	 * @since 2.0.0
	 * @param string $payment_method_id The ID of the payment method to validate
	 * @param array  $payment_meta associative array of meta data required for automatic payments
	 * @return array
	 */
	public function validate_subscription_payment_meta( $payment_method_id, $payment_meta ) {
		if ( $this->id === $payment_method_id ) {

			if ( ! isset( $payment_meta['post_meta']['_wcv_sc_customer_id']['value'] ) || empty( $payment_meta['post_meta']['_wcv_sc_customer_id']['value'] ) ) {
				throw new Exception( __( 'A "Stripe Customer ID" value is required.', 'wc-vendors-gateway-stripe-connect' ) );
			} elseif ( 0 !== strpos( $payment_meta['post_meta']['_wcv_sc_customer_id']['value'], 'cus_' ) ) {
				throw new Exception( __( 'Invalid customer ID. A valid "Stripe Customer ID" must begin with "cus_".', 'wc-vendors-gateway-stripe-connect' ) );
			}

			if (
				( ! empty( $payment_meta['post_meta']['_stripe_source_id']['value'] )
				  && 0 !== strpos( $payment_meta['post_meta']['_stripe_source_id']['value'], 'card_' ) )
				&& ( ! empty( $payment_meta['post_meta']['_stripe_source_id']['value'] )
					 && 0 !== strpos( $payment_meta['post_meta']['_stripe_source_id']['value'], 'src_' ) ) ) {

				throw new Exception( __( 'Invalid source ID. A valid source "Stripe Source ID" must begin with "src_" or "card_".', 'wc-vendors-gateway-stripe-connect' ) );
			}
		}
	}

	/**
	 * Render the payment method used for a subscription in the "My Subscriptions" table
	 *
	 * @since 1.7.5
	 * @param string          $payment_method_to_display the default payment method text to display
	 * @param WC_Subscription $subscription the subscription details
	 * @return string the subscription payment method
	 */
	public function maybe_render_subscription_payment_method( $payment_method_to_display, $subscription ) {
		$customer_user = $subscription->get_customer_id();

		// bail for other payment methods
		if ( $subscription->get_payment_method() !== $this->id || ! $customer_user ) {
			return $payment_method_to_display;
		}

		$stripe_source_id = get_post_meta( $subscription->get_id(), '_stripe_source_id', true );

		// For BW compat will remove in future.
		if ( empty( $stripe_source_id ) ) {
			$stripe_source_id = get_post_meta( $subscription->get_id(), '_stripe_card_id', true );

			// Take this opportunity to update the key name.
			update_post_meta( $subscription->get_id(), '_stripe_source_id', $stripe_source_id );
		}

		$stripe_customer    = new WCV_SC_Customer();
		$stripe_customer_id = get_post_meta( $subscription->get_id(), '_wcv_sc_customer_id', true );

		// If we couldn't find a Stripe customer linked to the subscription, fallback to the user meta data.
		if ( ! $stripe_customer_id || ! is_string( $stripe_customer_id ) ) {
			$user_id            = $customer_user;
			$stripe_customer_id = get_user_meta( $user_id, '_wcv_sc_customer_id', true );
			$stripe_source_id   = get_user_meta( $user_id, '_stripe_source_id', true );

			// For BW compat will remove in future.
			if ( empty( $stripe_source_id ) ) {
				$stripe_source_id = get_user_meta( $user_id, '_stripe_card_id', true );

				// Take this opportunity to update the key name.
				update_user_meta( $user_id, '_stripe_source_id', $stripe_source_id );
			}
		}

		// If we couldn't find a Stripe customer linked to the account, fallback to the order meta data.
		if ( ( ! $stripe_customer_id || ! is_string( $stripe_customer_id ) ) && false !== $subscription->order ) {
			$stripe_customer_id = get_post_meta( $subscription->get_parent_id(), '_wcv_sc_customer_id', true );
			$stripe_source_id   = get_post_meta( $subscription->get_parent_id(), '_stripe_source_id', true );

			// For BW compat will remove in future.
			if ( empty( $stripe_source_id ) ) {
				$stripe_source_id = get_post_meta( $subscription->get_parent_id(), '_stripe_card_id', true );

				// Take this opportunity to update the key name.
				update_post_meta( $subscription->get_parent_id(), '_stripe_source_id', $stripe_source_id );
			}
		}

		$stripe_customer->set_id( $stripe_customer_id );
		$stripe_customer->set_stripe_customer();

		$sources                   = $stripe_customer->get_sources();
		$payment_method_to_display = __( 'N/A', 'wc-vendors-gateway-stripe-connect' );

		if ( $sources ) {
			$card = false;

			foreach ( $sources as $source ) {
				if ( isset( $source->type ) && 'card' === $source->type ) {
					$card = $source->card;
				} elseif ( isset( $source->object ) && 'card' === $source->object ) {
					$card = $source;
				}

				if ( $source->id === $stripe_source_id ) {
					if ( $card ) {
						/* translators: 1) card brand 2) last 4 digits */
						$payment_method_to_display = sprintf( __( 'Via %1$s card ending in %2$s', 'wc-vendors-gateway-stripe-connect' ), ( isset( $card->brand ) ? $card->brand : __( 'N/A', 'wc-vendors-gateway-stripe-connect' ) ), $card->last4 );
					}

					break;
				}
			}
		}

		return $payment_method_to_display;
	}
}