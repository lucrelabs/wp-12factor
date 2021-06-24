<?php
/**
 * The template for displaying the description in content-shop-the-look.php
 *
 * @author James Kemp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! $stl_product->is_type( 'variation' ) ) {
	return;
}

echo wc_get_formatted_variation( $stl_product->get_variation_attributes() );