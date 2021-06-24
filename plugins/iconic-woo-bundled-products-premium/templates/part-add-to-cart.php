<?php
/**
 * The template for displaying the add-to-cart button in content-shop-the-look.php
 *
 * @author James Kemp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( $stl_product->is_type( 'bundled' ) ) {
	return;
}

global $iconic_woo_bundled_products;

if ( $stl_product->is_type( 'variable' ) ) {
	woocommerce_variable_add_to_cart();
} elseif ( $stl_product->is_type( 'variation' ) ) {
	include( $iconic_woo_bundled_products->templates->locate_template( 'part-add-to-cart-variation.php' ) );
} else {
	$args = array();

	if ( $stl_product->is_type( 'external' ) ) {
		$args['product_url'] = $stl_product->get_product_url();
		$args['button_text'] = $stl_product->get_button_text();
	}

	wc_get_template( 'single-product/add-to-cart/' . $stl_product->get_type() . '.php', $args );
}