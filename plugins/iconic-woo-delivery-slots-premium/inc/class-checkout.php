<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WDS checkout class.
 */
class Iconic_WDS_Checkout {
	/**
	 * Run.
	 */
	public static function run() {
		add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'checkout_fields' ), 10, 1 );
		add_filter( 'woocommerce_checkout_posted_data', array( __CLASS__, 'checkout_posted_data' ) );
		add_action( 'woocommerce_checkout_process', array( __CLASS__, 'checkout_process' ), 10 );
		add_action( 'woocommerce_checkout_update_order_meta', array( 'Iconic_WDS_Order', 'update_order_meta' ) );
	}

	/**
	 * Register checkout fields for processing (not display).
	 *
	 * @param array $fields Fields.
	 *
	 * @return array
	 */
	public static function checkout_fields( $fields ) {
		global $jckwds;

		$checkout_fields_data = $jckwds->get_checkout_fields_data();

		if ( empty( $checkout_fields_data ) ) {
			return $fields;
		}

		$fields['jckwds'] = array();

		foreach ( $checkout_fields_data as $key => $data ) {
			$fields['jckwds'][ $key ] = array(
				'type'     => 'text',
				'label'    => $data['field_args']['label'],
				'required' => $data['field_args']['required'],
			);
		}

		return $fields;
	}

	/**
	 * Remove fields if they are hidden based on shipping method.
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function checkout_posted_data( $data ) {
		$fields_hidden = (bool) filter_input( INPUT_POST, 'iconic-wds-fields-hidden', FILTER_SANITIZE_NUMBER_INT );

		if ( $fields_hidden ) {
			unset( $data['jckwds-delivery-date'], $data['jckwds-delivery-date-ymd'], $data['jckwds-delivery-time'] );

			return $data;
		}

		// Remove 0 value so it is seen as empty.
		$data['jckwds-delivery-time'] = empty( $data['jckwds-delivery-time'] ) ? '' : $data['jckwds-delivery-time'];

		return $data;
	}

	/**
	 * Validate checkout fields.
	 */
	public static function checkout_process() {
		$date_fields_hidden = filter_input( INPUT_POST, 'iconic-wds-fields-hidden', FILTER_SANITIZE_NUMBER_INT );
		$ymd                = filter_input( INPUT_POST, 'jckwds-delivery-date-ymd' );
		$time               = filter_input( INPUT_POST, 'jckwds-delivery-time' );

		if ( empty( $ymd ) || false === $date_fields_hidden || ! empty( $date_fields_hidden ) ) {
			return;
		}

		global $jckwds;

		$expires = strtotime( '+10 minutes', current_time( 'timestamp', 1 ) );

		// Check if date is booked up.
		if ( $ymd && ! $jckwds->get_orders_remaining_for_day( $ymd ) ) {
			wc_add_notice(
				__( 'Sorry, the selected date is no longer available.', 'jckwds' ),
				'error',
				array(
					'iconic-wds-clear-date' => true,
					'iconic-wds-clear-time' => true,
				)
			);
		}

		// These conditions only apply if time slots are enabled and selected.
		if ( empty( $time ) || ! $jckwds->settings['timesettings_timesettings_setup_enable'] ) {
			// Add 10 minute reservation to prevent double booking.
			$jckwds->add_reservation( array(
				'datetimeid' => $ymd,
				'date'       => $jckwds->convert_date_for_database( $ymd ),
				'processed'  => 0,
				'expires'    => $expires,
			) );

			return;
		}

		// Check if date has any slots available.
		$available_slots = $jckwds->slots_available_on_date( $ymd );

		if ( empty( $available_slots ) ) {
			wc_add_notice(
				__( 'Sorry, there are no longer any slots available on the selected date.', 'jckwds' ),
				'error',
				array(
					'iconic-wds-clear-date' => true,
					'iconic-wds-clear-time' => true,
				)
			);
		} else {
			// Check if the time slot is still available on the selected date.
			$available_slot_values = wp_list_pluck( $available_slots, 'value' );

			if ( $time && ! in_array( $time, $available_slot_values, true ) ) {
				wc_add_notice(
					__( 'Sorry, that time slot is no longer available.', 'jckwds' ),
					'error',
					array(
						'iconic-wds-clear-time' => true,
					)
				);
			} else {
				$timeslot_id = $jckwds->extract_timeslot_id_from_option_value( $time );
				$slot_id     = sprintf( '%s_%s', $ymd, $timeslot_id );
				$timeslot    = $jckwds->get_timeslot_data( $timeslot_id );

				if ( $timeslot ) {
					// Add 10 minute reservation to prevent double booking.
					$jckwds->add_reservation( array(
						'datetimeid' => $slot_id,
						'date'       => $jckwds->convert_date_for_database( $ymd ),
						'starttime'  => $timeslot['timefrom']['stripped'],
						'endtime'    => $timeslot['timeto']['stripped'],
						'processed'  => 0,
						'expires'    => $expires,
					) );
				}
			}
		}
	}

	/**
	 * Is delivery slots allowed.
	 *
	 * @return bool
	 */
	public static function is_delivery_slots_allowed() {
		if ( ! self::is_delivery_slots_allowed_for_current_shipping_method() ) {
			return (bool) apply_filters( 'iconic_wds_delivery_slots_allowed', false );
		}

		if ( ! self::is_delivery_slots_allowed_for_category() ) {
			return (bool) apply_filters( 'iconic_wds_delivery_slots_allowed', false );
		}

		if ( ! self::is_delivery_slots_allowed_for_product() ) {
			return (bool) apply_filters( 'iconic_wds_delivery_slots_allowed', false );
		}

		return (bool) apply_filters( 'iconic_wds_delivery_slots_allowed', true );
	}

	/**
	 * Check if date/time fields should be active
	 * for the current shipping method
	 *
	 * @return bool
	 */
	public static function is_delivery_slots_allowed_for_current_shipping_method() {
		global $jckwds;

		$chosen_shipping = $jckwds::get_chosen_shipping_method();
		$allowed_methods = $jckwds->settings['general_setup_shipping_methods'];

		if ( $allowed_methods && ! empty( $allowed_methods ) ) {
			if ( in_array( 'any', $allowed_methods, true ) ) {
				return true;
			}

			foreach ( $allowed_methods as $allowed_method ) {
				$allowed_method = str_replace( 'wc_shipping_', '', $allowed_method );

				if ( $chosen_shipping == $allowed_method ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if date/time should be active based on
	 * categories of products in the cart.
	 *
	 * @return bool
	 */
	public static function is_delivery_slots_allowed_for_category() {
		global $jckwds;

		$exclude_categories           = $jckwds->settings['general_setup_exclude_categories'];
		$exclude_categories_condition = isset( $jckwds->settings['general_setup_exclude_categories_condition'] ) ? $jckwds->settings['general_setup_exclude_categories_condition'] : 'any';

		if ( empty( $exclude_categories ) ) {
			return true;
		}

		if ( 'any' === $exclude_categories_condition ) {
			return self::is_delivery_slots_allowed_for_category_any( $exclude_categories );
		} else {
			return self::is_delivery_slots_allowed_for_category_all( $exclude_categories );
		}
	}

	/**
	 * Checks if date/time should be enabled based on categories
	 * in the cart, and considering that the settings for Exclude
	 * Product Condition is set to "Any".
	 *
	 * @param array $exclude_categories List of categories for which datepicker will be disabled.
	 *
	 * @return bool
	 */
	public static function is_delivery_slots_allowed_for_category_any( $exclude_categories ) {
		$cart_contents = WC()->cart->get_cart_contents();

		foreach ( $cart_contents as $cart_key => $cart_item ) {
			if ( empty( $cart_item['data'] ) ) {
				continue;
			}

			$product           = $cart_item['data'];
			$product_parent_id = $cart_item['data']->get_parent_id();

			if ( $product_parent_id ) {
				$product = wc_get_product( $product_parent_id );
			}

			$category_ids = $product->get_category_ids();

			if ( empty( $category_ids ) ) {
				continue;
			}

			$compare = array_intersect( $exclude_categories, $category_ids );

			if ( empty( $compare ) ) {
				continue;
			}

			return false;
		}
		return true;
	}

	/**
	 * Checks if date/time should be enabled based on categories
	 * in the cart, and considering that the settings for Exclude
	 * Product Condition is set to "All".
	 *
	 * @param array $exclude_categories List of categories for which datepicker will be disabled.
	 *
	 * @return bool
	 */
	public static function is_delivery_slots_allowed_for_category_all( $exclude_categories ) {
		$cart_contents      = WC()->cart->get_cart_contents();
		$cart_categories    = array();
		$exclude_categories = array_map( 'absint', (array) $exclude_categories );

		foreach ( $cart_contents as $cart_key => $cart_item ) {
			if ( empty( $cart_item['data'] ) ) {
				continue;
			}

			$product           = $cart_item['data'];
			$product_parent_id = $cart_item['data']->get_parent_id();

			if ( $product_parent_id ) {
				$product = wc_get_product( $product_parent_id );
			}

			$cart_categories = array_merge( $cart_categories, $product->get_category_ids() );
		}

		if ( empty( $cart_categories ) ) {
			return true;
		}

		$cart_categories = array_unique( $cart_categories );
		$diff            = array_diff( $exclude_categories, $cart_categories );

		// Hide timeslot fields if $exclude_categories is a subset of $cart_categories.
		if ( 0 === count( $diff ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Check if date/time should be active based on
	 * products in the cart.
	 *
	 * @return bool
	 */
	public static function is_delivery_slots_allowed_for_product() {
		global $jckwds;
		$exclude_condition = isset( $jckwds->settings['general_setup_exclude_products_condition'] ) ? $jckwds->settings['general_setup_exclude_products_condition'] : 'any';
		$exclude_products  = $jckwds->settings['general_setup_exclude_products'];

		if ( ! is_array( $exclude_products ) || empty( $exclude_products ) ) {
			return true;
		}

		$exclude_products = array_map( 'absint', $exclude_products );
		$cart_contents    = WC()->cart->get_cart_contents();
		$cart_product_ids = array();
		$hide_timeslot    = false;

		foreach ( $cart_contents as $cart_key => $cart_item ) {
			if ( empty( $cart_item['data'] ) ) {
				continue;
			}

			$cart_product_ids[] = $cart_item['data']->get_parent_id() ? $cart_item['data']->get_parent_id() : $cart_item['data']->get_ID();
		}

		$cart_product_ids = array_map( 'absint', $cart_product_ids );

		if ( 'all' === $exclude_condition ) {
			// Hide timeslots when all products from exclusion list are in the cart.
			$diff = array_diff( $exclude_products, $cart_product_ids );

			if ( 0 === count( $diff ) ) {
				$hide_timeslot = true;
			}
		} else {
			// Hide timeslots even if there is one common product between cart and exclsion list.
			$common = array_intersect( $exclude_products, $cart_product_ids );
			if ( count( $common ) > 0 ) {
				$hide_timeslot = true;
			}
		}

		return ! $hide_timeslot;
	}
}
