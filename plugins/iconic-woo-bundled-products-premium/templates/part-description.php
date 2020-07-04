<?php
/**
 * The template for displaying the description in content-shop-the-look.php
 *
 * @author James Kemp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

echo apply_filters( 'woocommerce_short_description', Iconic_WBP_Backwards_Compatibility::get_short_description( $stl_product ) );