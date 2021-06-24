<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Iconic_WBP_Shortcodes.
 *
 * @class    Iconic_WBP_Shortcodes
 * @version  1.0.0
 * @package  Iconic_Woo_Bundled_Products
 * @category Class
 * @author   Iconic
 */
class Iconic_WBP_Shortcodes {

	/*
	 * Init shortcodes
	 */
	public static function run() {

		if( is_admin() ) {
			return;
		}

		add_shortcode( 'iconic-woo-bundle', array( __CLASS__, 'output_bundle' ) );

	}

	/**
	 * Output bundle.
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public static function output_bundle( $atts ) {
		global $iconic_woo_bundled_products, $post;

		$atts = shortcode_atts( array(
			'id' => false,
		), $atts, 'iconic-woo-bundle' );

		$atts['id'] = $atts['id'] ? $atts['id'] : $post->ID;

		if ( ! $atts['id'] ) {
			return;
		}

		$post_object = get_post( $atts['id'] );

		if ( ! $post_object ) {
			return;
		}

		$GLOBALS['post'] =& $post_object;

		setup_postdata( $GLOBALS['post'] );

		ob_start();

		$iconic_woo_bundled_products->unset_posted_attributes();
		include( $iconic_woo_bundled_products->templates->locate_template( 'content-bundled-products.php' ) );

		wp_reset_postdata();

		return ob_get_clean();
	}

}