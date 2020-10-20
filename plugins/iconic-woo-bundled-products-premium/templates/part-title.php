<?php
/**
 * The template for displaying the title in content-shop-the-look.php
 *
 * Override this template by copying it to yourtheme/woocommerce/shop-the-look/title.php
 *
 * @author James Kemp
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<h2><a href="<?php echo $stl_product->get_permalink(); ?>" title="<?php echo esc_attr( $stl_product->get_name() ); ?>"><?php echo $stl_product->get_name(); ?></a></h2>