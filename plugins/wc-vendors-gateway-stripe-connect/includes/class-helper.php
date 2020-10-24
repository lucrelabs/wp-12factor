<?php
/**
 * WC Vendors - Stripe Commissions & Gateway Helper.
 *
 * @since   2.0.0
 * @package WC_Vendors_Stripe_Commissions_Gateway
 *
 * Based on class WC_Stripe_Helper from WooCommerce Stripe plugin - https://wordpress.org/plugins/woocommerce-gateway-stripe/
 */

class WCV_SC_Helper {

	const META_NAME_FEE                  = '_stripe_connect_fee';
	const META_NAME_NET                  = '_stripe_connect_net';
	const META_NAME_STRIPE_CURRENCY      = '_stripe_connect_currency';
	const META_NAME_STRIPE_TRANSFER_DATA = '_stripe_connect_transfer_data';

	/**
	 * Check if the payment gateway is enabled.
	 *
	 * @return bool
	 */
	public static function is_configured() {
		return wc_string_to_bool( self::get_settings( 'enabled' ) );
	}

	/**
	 * Gets the Stripe currency for order.
	 *
	 * @since 2.0.0
	 * @param object $order
	 * @return string $currency
	 */
	public static function get_stripe_currency( $order = null ) {
		if ( is_null( $order ) ) {
			return false;
		}

		return $order->get_meta( self::META_NAME_STRIPE_CURRENCY, true );
	}

	/**
	 * Updates the Stripe currency for order.
	 *
	 * @since 2.0.0
	 * @param object $order
	 * @param string $currency
	 */
	public static function update_stripe_currency( $order = null, $currency ) {
		if ( is_null( $order ) ) {
			return false;
		}

		$order->update_meta_data( self::META_NAME_STRIPE_CURRENCY, strtoupper( $currency ) );
	}

	/**
	 * Gets the Stripe fee for order. With legacy check.
	 *
	 * @since 2.0.0
	 * @param object $order
	 * @return string $amount
	 */
	public static function get_stripe_fee( $order = null ) {
		if ( is_null( $order ) ) {
			return false;
		}

		return $order->get_meta( self::META_NAME_FEE, true );
	}

	/**
	 * Updates the Stripe fee for order.
	 *
	 * @since 2.0.0
	 * @param object $order
	 * @param float  $amount
	 */
	public static function update_stripe_fee( $order = null, $amount = 0.0 ) {
		if ( is_null( $order ) ) {
			return false;
		}
		$order->update_meta_data( self::META_NAME_FEE, $amount );
	}

	/**
	 * Deletes the Stripe fee for order.
	 *
	 * @since 2.0.0
	 * @param object $order
	 */
	public static function delete_stripe_fee( $order = null ) {
		if ( is_null( $order ) ) {
			return false;
		}
		$order->delete_meta_data( self::META_NAME_FEE );
	}

	/**
	 * Gets the Stripe net for order. With legacy check.
	 *
	 * @since 2.0.0
	 * @param object $order
	 * @return string $amount
	 */
	public static function get_stripe_net( $order = null ) {
		if ( is_null( $order ) ) {
			return false;
		}

		$amount = $order->get_meta( self::META_NAME_NET, true );

		return $amount;
	}

	/**
	 * Updates the Stripe net for order.
	 *
	 * @since 2.0.0
	 * @param object $order
	 * @param float  $amount
	 */
	public static function update_stripe_net( $order = null, $amount = 0.0 ) {
		if ( is_null( $order ) ) {
			return false;
		}

		$order->update_meta_data( self::META_NAME_NET, $amount );
	}

	/**
	 * Deletes the Stripe net for order.
	 *
	 * @since 2.0.0
	 * @param object $order
	 */
	public static function delete_stripe_net( $order = null ) {
		if ( is_null( $order ) ) {
			return false;
		}

		$order_id = $order->get_id();
		delete_post_meta( $order_id, self::META_NAME_NET );
	}

	/**
	 * Get Stripe amount to pay
	 *
	 * @param float  $total Amount due.
	 * @param string $currency Accepted currency.
	 *
	 * @return float|int
	 */
	public static function get_stripe_amount( $total, $currency = '' ) {
		if ( ! $currency ) {
			$currency = get_woocommerce_currency();
		}

		if ( in_array( strtolower( $currency ), self::no_decimal_currencies() ) ) {
			return absint( $total );
		} else {
			return absint( wc_format_decimal( ( (float) $total * 100 ), wc_get_price_decimals() ) ); // In cents.
		}
	}

	/**
	 * Localize Stripe messages based on code
	 *
	 * @since 3.0.6
	 * @version 3.0.6
	 * @return array
	 */
	public static function get_localized_messages() {
		return apply_filters(
			'wcv_sc_localized_messages',
			array(
				'invalid_number'           => __( 'The card number is not a valid credit card number.', 'wc-vendors-gateway-stripe-connect' ),
				'invalid_expiry_month'     => __( 'The card\'s expiration month is invalid.', 'wc-vendors-gateway-stripe-connect' ),
				'invalid_expiry_year'      => __( 'The card\'s expiration year is invalid.', 'wc-vendors-gateway-stripe-connect' ),
				'invalid_cvc'              => __( 'The card\'s security code is invalid.', 'wc-vendors-gateway-stripe-connect' ),
				'incorrect_number'         => __( 'The card number is incorrect.', 'wc-vendors-gateway-stripe-connect' ),
				'incomplete_number'        => __( 'The card number is incomplete.', 'wc-vendors-gateway-stripe-connect' ),
				'incomplete_cvc'           => __( 'The card\'s security code is incomplete.', 'wc-vendors-gateway-stripe-connect' ),
				'incomplete_expiry'        => __( 'The card\'s expiration date is incomplete.', 'wc-vendors-gateway-stripe-connect' ),
				'expired_card'             => __( 'The card has expired.', 'wc-vendors-gateway-stripe-connect' ),
				'incorrect_cvc'            => __( 'The card\'s security code is incorrect.', 'wc-vendors-gateway-stripe-connect' ),
				'incorrect_zip'            => __( 'The card\'s zip code failed validation.', 'wc-vendors-gateway-stripe-connect' ),
				'invalid_expiry_year_past' => __( 'The card\'s expiration year is in the past', 'wc-vendors-gateway-stripe-connect' ),
				'card_declined'            => __( 'The card was declined.', 'wc-vendors-gateway-stripe-connect' ),
				'missing'                  => __( 'There is no card on a customer that is being charged.', 'wc-vendors-gateway-stripe-connect' ),
				'processing_error'         => __( 'An error occurred while processing the card.', 'wc-vendors-gateway-stripe-connect' ),
				'invalid_request_error'    => __( 'Unable to process this payment, please try again or use alternative method.', 'wc-vendors-gateway-stripe-connect' ),
				'invalid_sofort_country'   => __( 'The billing country is not accepted by SOFORT. Please try another country.', 'wc-vendors-gateway-stripe-connect' ),
			)
		);
	}

	/**
	 * List of currencies supported by Stripe that has no decimals.
	 * find the full list here : https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
	 *
	 * @return array $currencies
	 */
	public static function no_decimal_currencies() {
		return array(
			'bif', // Burundian Franc
			'djf', // Djiboutian Franc
			'jpy', // Japanese Yen
			'krw', // South Korean Won
			'pyg', // Paraguayan Guaraní
			'vnd', // Vietnamese Đồng
			'xaf', // Central African Cfa Franc
			'xpf', // Cfp Franc
			'clp', // Chilean Peso
			'gnf', // Guinean Franc
			'kmf', // Comorian Franc
			'mga', // Malagasy Ariary
			'rwf', // Rwandan Franc
			'vuv', // Vanuatu Vatu
			'xof', // West African Cfa Franc
		);
	}

	/**
	 * Checks Stripe minimum order value authorized per currency
	 */
	public static function get_minimum_amount() {
		// Check order amount
		switch ( get_woocommerce_currency() ) {
			case 'USD':
			case 'CAD':
			case 'EUR':
			case 'CHF':
			case 'AUD':
			case 'SGD':
				$minimum_amount = 50;
				break;
			case 'GBP':
				$minimum_amount = 30;
				break;
			case 'DKK':
				$minimum_amount = 250;
				break;
			case 'NOK':
			case 'SEK':
				$minimum_amount = 300;
				break;
			case 'JPY':
				$minimum_amount = 5000;
				break;
			case 'MXN':
				$minimum_amount = 1000;
				break;
			case 'HKD':
				$minimum_amount = 400;
				break;
			default:
				$minimum_amount = 50;
				break;
		}

		return $minimum_amount;
	}

	/**
	 * Gets all the saved setting options from a specific method.
	 * If specific setting is passed, only return that.
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param string $method The payment method to get the settings from.
	 * @param string $setting The name of the setting to get.
	 */
	public static function get_settings( $setting = null, $method = 'stripe-connect' ) {
		$all_settings = get_option( 'woocommerce_' . $method . '_settings', array() );

		if ( null === $setting ) {
			return $all_settings;
		}

		return isset( $all_settings[ $setting ] ) ? $all_settings[ $setting ] : '';
	}

	/**
	 * Checks if Pre Orders is available.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public static function is_pre_orders_exists() {
		return class_exists( 'WC_Pre_Orders_Order' );
	}

	/**
	 * Gets the webhook URL for Stripe triggers. Used mainly for
	 * asyncronous redirect payment methods in which statuses are
	 * not immediately chargeable.
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return string
	 */
	public static function get_webhook_url() {
		return add_query_arg( 'wc-api', 'wcv_stripe_connect', trailingslashit( get_home_url() ) );
	}

	/**
	 * Gets the order by Stripe source ID.
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param string $source_id
	 */
	public static function get_order_by_source_id( $source_id ) {
		global $wpdb;

		$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT ID FROM $wpdb->posts as posts LEFT JOIN $wpdb->postmeta as meta ON posts.ID = meta.post_id WHERE meta.meta_value = %s AND meta.meta_key = %s", $source_id, '_stripe_source_id' ) );

		if ( ! empty( $order_id ) ) {
			return wc_get_order( $order_id );
		}

		return false;
	}

	/**
	 * Gets the order by Stripe charge ID.
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param string $charge_id
	 */
	public static function get_order_by_charge_id( $charge_id ) {
		global $wpdb;

		$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT ID FROM $wpdb->posts as posts LEFT JOIN $wpdb->postmeta as meta ON posts.ID = meta.post_id WHERE meta.meta_value = %s AND meta.meta_key = %s", $charge_id, '_transaction_id' ) );

		if ( ! empty( $order_id ) ) {
			return wc_get_order( $order_id );
		}

		return false;
	}

	/**
	 * Sanitize statement descriptor text.
	 *
	 * Stripe requires max of 22 characters and no
	 * special characters with ><"'.
	 *
	 * @since 2.0.0
	 * @param string $statement_descriptor
	 * @return string $statement_descriptor Sanitized statement descriptor
	 */
	public static function clean_statement_descriptor( $statement_descriptor = '' ) {
		$disallowed_characters = array( '<', '>', '"', "'" );

		// Remove special characters.
		$statement_descriptor = str_replace( $disallowed_characters, '', $statement_descriptor );

		$statement_descriptor = substr( trim( $statement_descriptor ), 0, 22 );

		return $statement_descriptor;
	}

	/**
	 * Get Stripe settings - Maybe not used
	 */
	public static function get_stripe_connect_settings() {
		$stripe_connect_settings = get_option( 'woocommerce_stripe-connect_settings', array() );
		$testmode                = wc_string_to_bool( $stripe_connect_settings['testmode'] );
		$secret_key              = $testmode ? $stripe_connect_settings['test_secret_key'] : $stripe_connect_settings['secret_key'];
		$publishable_key         = $testmode ? $stripe_connect_settings['test_publishable_key'] : $stripe_connect_settings['publishable_key'];
		$client_id               = $testmode ? $stripe_connect_settings['test_client_id'] : $stripe_connect_settings['client_id'];
		$connect_button_theme    = $stripe_connect_settings['connect_button_theme'];
		$redirect_uri_page_id    = $stripe_connect_settings['redirect_uri'];
		$redirect_uri            = get_permalink( $redirect_uri_page_id );
	}

	/**
	 * Check if current vendor can add new product or not.
	 *
	 * @param int $vendor_id
	 *
	 * @return bool
	 */
	public static function vendor_can_add_new_product( $vendor_id = 0 ) {
		$user = wp_get_current_user();

		if ( ! $user ) {
			return false;
		}

		if ( array_intersect( array( 'administrator', 'editor' ), $user->roles ) ) {
			return true;
		}

		if ( ! self::is_configured() ) {
			return true;
		}

		if ( ! $vendor_id ) {
			$vendor_id = get_current_user_id();
		}

		// Check if option is enabled.
		if ( ! wc_string_to_bool( self::get_settings( 'require_connected_account' ) ) ) {
			return true;
		}

		$connected_account_id = get_user_meta( $vendor_id, '_stripe_connect_user_id', true );

		if ( $connected_account_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if current vendor can edit product or not.
	 *
	 * @param int $vendor_id
	 *
	 * @return bool
	 */
	public static function vendor_can_edit_product( $vendor_id = 0 ) {
		$user = wp_get_current_user();

		if ( ! $user ) {
			return false;
		}

		if ( array_intersect( array( 'administrator', 'editor' ), $user->roles ) ) {
			return true;
		}

		if ( self::vendor_can_add_new_product( $vendor_id ) ) {
			return true;
		}

		// Check if option is enabled.
		if ( ! wc_string_to_bool( self::get_settings( 'disable_product_edit' ) ) ) {
			return true;
		}

		return false;
	}

	public static function get_order_commission_for_vendor( $order_id, $vendor_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		$sql = 'SELECT SUM(total_due + total_shipping + tax)';

		$sql
			.= "
				FROM `{$table_name}`
				WHERE vendor_id = {$vendor_id}
				AND order_id = '{$order_id}'
			";

		return $wpdb->get_var( $sql );
	}

	public static function get_commission_for_vendor( $order_id, $vendor_id, $field = 'total_due' ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		$sql = 'SELECT SUM(' . $field . ')';

		$sql .= "
				FROM `{$table_name}`
				WHERE vendor_id = {$vendor_id}
				AND order_id = '{$order_id}'
			";

		return $wpdb->get_var( $sql );
	}

	public static function format_amount_to_display( $amount, $currency ) {
		if ( ! $amount || ! $currency ) {
			return 0;
		}

		if ( in_array( $currency, self::no_decimal_currencies() ) ) {
			return $amount;
		}

		return number_format( $amount / 100, 2, '.', '' );
	}

	public static function update_stripe_transfer_data( $order = null, $transfer ) {
		if ( is_null( $order ) ) {
			return false;
		}
		$order->update_meta_data( self::META_NAME_STRIPE_TRANSFER_DATA, $transfer );
	}

	public static function get_stripe_transfer_data( $order = null ) {
		if ( is_null( $order ) ) {
			return false;
		}
		return $order->get_meta( self::META_NAME_STRIPE_TRANSFER_DATA );
	}

	public static function is_eiglible_for_separate_charges_transfers( $country_code ) {
		$country_code = strtoupper( $country_code );
		// phpcs:disable
		$valid_countrycodes = array(
			'US', 'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DE', 'DK', 'EE', 'EL',
			'ES', 'FI', 'FR', 'GB', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
			'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
		);
		// phpcs:enable

		return( in_array( $country_code, $valid_countrycodes ) );
	}

	/**
	 * We use Direct charge by default to prevent error after upgrading from v1.
	 *
	 * US/EU marketplace can choose between Direct Charges or Separate Charges
	 * and Transfers in the gateway settings.
	 */
	public static function use_charges_transfers() {
		$country = self::get_stripe_account_country();
		if ( ! self::is_eiglible_for_separate_charges_transfers( $country ) ) {
			return false;
		}

		$settings = get_option( 'woocommerce_stripe-connect_settings', array() );
		if ( wc_string_to_bool( $settings['enable_charges_transfers'] ) ) {
			return true;
		}

		return false;
	}

	public static function get_stripe_account_country() {
		$country = get_transient( 'wcv_stripe_account_country' );
		if ( ! $country ) {
			$country = self::cache_stripe_account_country();
		}

		return strtoupper( $country );
	}

	private static function cache_stripe_account_country() {
		try {
			$stripe_connect_settings = get_option( 'woocommerce_stripe-connect_settings', array() );
			$secret_key              = wc_string_to_bool( $stripe_connect_settings['testmode'] ) ? $stripe_connect_settings['test_secret_key'] : $stripe_connect_settings['secret_key'];
			\Stripe\Stripe::setApiKey( $secret_key );
			// Set the Stripe API version.
			\Stripe\Stripe::setApiVersion( WCV_SC_STRIPE_API_VER );
			$response = \Stripe\Account::retrieve();
			set_transient( 'wcv_stripe_account_country', $response->country, DAY_IN_SECONDS * 30 );
			return $response->country;
		} catch ( \Stripe\Error\Authentication $e ) {
			WCV_SC_Logger::log( 'Error: ' . $e->getMessage() );
		} catch ( WCV_SC_Exception $e ) {
			WCV_SC_Logger::log( 'Error: ' . $e->getMessage() );
		}
	}
}
