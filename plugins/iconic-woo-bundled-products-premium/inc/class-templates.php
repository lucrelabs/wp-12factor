<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WBP_Templates.
 *
 * @class    Iconic_WBP_Templates
 * @version  1.0.0
 * @author   Iconic
 */
class Iconic_WBP_Templates {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'iconic_wbp_content_product_image', array( __CLASS__, 'output_product_image' ), 10, 2 );

		add_action( 'iconic_wbp_content_product_summary', array( __CLASS__, 'output_product_title' ), 10, 2 );
		add_action( 'iconic_wbp_content_product_summary', array( __CLASS__, 'output_product_price' ), 20, 2 );
		add_action( 'iconic_wbp_content_product_summary', array( __CLASS__, 'output_product_description' ), 30, 2 );
		add_action( 'iconic_wbp_content_product_summary', array( __CLASS__, 'output_product_variation_attributes' ), 40, 2 );
		add_action( 'iconic_wbp_content_product_summary', array( __CLASS__, 'output_product_add_to_cart' ), 50, 2 );

		add_filter( 'woocommerce_add_to_cart_form_action', array( __CLASS__, 'add_to_cart_form_action' ), 10 );
	}

	/**
	 * Output product image.
	 *
	 * @param $stl_product
	 * @param $product
	 */
	public static function output_product_image( $stl_product, $product ) {
		global $iconic_woo_bundled_products;

		include( $iconic_woo_bundled_products->templates->locate_template( 'part-image.php' ) );
	}

	/**
	 * Output product title.
	 *
	 * @param $stl_product
	 * @param $product
	 */
	public static function output_product_title( $stl_product, $product ) {
		global $iconic_woo_bundled_products;

		include( $iconic_woo_bundled_products->templates->locate_template( 'part-title.php' ) );
	}

	/**
	 * Output product price.
	 *
	 * @param $stl_product
	 * @param $product
	 */
	public static function output_product_price( $stl_product, $product ) {
		global $iconic_woo_bundled_products;

		include( $iconic_woo_bundled_products->templates->locate_template( 'part-price.php' ) );
	}

	/**
	 * Output product description.
	 *
	 * @param $stl_product
	 * @param $product
	 */
	public static function output_product_description( $stl_product, $product ) {
		global $iconic_woo_bundled_products;

		include( $iconic_woo_bundled_products->templates->locate_template( 'part-description.php' ) );
	}

	/**
	 * Output product variation attributes.
	 *
	 * @param $stl_product
	 * @param $product
	 */
	public static function output_product_variation_attributes( $stl_product, $product ) {
		global $iconic_woo_bundled_products;

		include( $iconic_woo_bundled_products->templates->locate_template( 'part-variation-attributes.php' ) );
	}

	/**
	 * Output product add to cart.
	 *
	 * @param $stl_product
	 * @param $product
	 */
	public static function output_product_add_to_cart( $stl_product, $product ) {
		global $iconic_woo_bundled_products;

		include( $iconic_woo_bundled_products->templates->locate_template( 'part-add-to-cart.php' ) );
	}

	/**
	 * Change add to cart form action.
	 *
	 * @param $action
	 *
	 * @return string
	 */
	public static function add_to_cart_form_action( $action ) {
		global $wp_query, $product;

		if ( empty( $wp_query->post ) ) {
			return $action;
		}

		$main_product = wc_get_product( $wp_query->post->ID );

		if ( ! $main_product ) {
			return $action;
		}

		if ( ! $main_product->is_type( 'bundled' ) ) {
			return $action;
		}

		return '';
	}
}