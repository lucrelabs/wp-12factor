<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * https://woocommerce.com/products/table-rate-shipping/
 *
 * @class    Iconic_Compat_Table_Rate_Shipping
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WDS_Compat_Table_Rate_Shipping {
	/**
	 * Run.
	 */
	public static function run() {
		add_filter( 'iconic_wds_zone_based_shipping_method', array( __CLASS__, 'shipping_method_options' ), 10, 3 );
	}

	/**
	 * Add shipping method options.
	 *
	 * @param array            $shipping_method_options
	 * @param object           $method
	 * @param WC_Shipping_Zone $shipping_zone
	 *
	 * @return array
	 */
	public static function shipping_method_options( $shipping_method_options, $method, $shipping_zone ) {
		if ( ! function_exists( 'woocommerce_get_shipping_method_table_rate' ) ) {
			return $shipping_method_options;
		}

		$class = str_replace( 'wc_shipping_', '', strtolower( get_class( $method ) ) );

		if ( $class !== 'table_rate' ) {
			return $shipping_method_options;
		}

		$rates = $method->get_shipping_rates();

		if ( empty( $rates ) ) {
			return $shipping_method_options;
		}

		foreach ( $rates as $rate ) {
			$id = sprintf( 'table_rate:%s:%s', $method->get_instance_id(), $rate->rate_id );
			$shipping_method_options[ $id ] = esc_html( sprintf( '%s: %s - %s', $shipping_zone->get_zone_name(), $method->title, $rate->rate_label ) );
		}

		return $shipping_method_options;
	}
}