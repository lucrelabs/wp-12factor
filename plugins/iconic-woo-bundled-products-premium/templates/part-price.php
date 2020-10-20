<?php
/**
 * The template for displaying the price in content-shop-the-look.php
 *
 * Override this template by copying it to yourtheme/woocommerce/shop-the-look/price.php
 *
 * @author James Kemp
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php printf( '<p class="price">%s</p>', $stl_product->get_price_html() ); ?>