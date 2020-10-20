<?php
/**
 * The template for displaying the image thumbnail in content-shop-the-look.php
 *
 * Override this template by copying it to yourtheme/woocommerce/shop-the-look/image.php
 *
 * @author James Kemp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="iconic-woo-bundled-product__image images">
	<div class="woocommerce-product-gallery__image"><?php echo $stl_product->get_image( 'shop_catalog' ); ?></div>
</div>