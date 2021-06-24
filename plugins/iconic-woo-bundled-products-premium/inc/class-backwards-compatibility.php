<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WBP_Backwards_Compatibility.
 *
 * @class    Iconic_WBP_Backwards_Compatibility
 * @version  1.0.0
 * @package  Iconic_Woo_Bundled_Products
 * @category Class
 * @author   Iconic
 */
class Iconic_WBP_Backwards_Compatibility {
	/**
	 * Get short description
	 *
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	public static function get_short_description( $product ) {
		return method_exists( $product, 'get_short_description' ) ? $product->get_short_description() : get_the_excerpt( $product );
	}

	/**
	 * Get parent ID.
	 *
	 * @param $product
	 *
	 * @return int
	 */
	public static function get_parent_id( $product ) {
		return method_exists( $product, 'get_parent_id' ) ? $product->get_parent_id() : $product->get_parent();
	}
}