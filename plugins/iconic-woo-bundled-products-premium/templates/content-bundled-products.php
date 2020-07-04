<?php
/**
 * The template for displaying product content in the content-shop-the-look.php template
 *
 * @author James Kemp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>

<?php global $product, $iconic_woo_bundled_products, $stl_product; ?>

<?php if ( empty( $product->get_product_ids() ) ) {
	return;
} ?>

<?php
$wrapper_classes = array( 'iconic-woo-bundled-products' );
$stl_products    = $product->get_products();

if ( empty( $stl_products ) ) {
	return;
}

if ( isset( $product->options['hide_add_to_cart'] ) && $product->options['hide_add_to_cart'] == "yes" ) {
	$wrapper_classes[] = 'iconic-woo-bundled-products--hide-add-to-cart';
}

if ( isset( $product->options['hide_qty'] ) && $product->options['hide_qty'] == "yes" ) {
	$wrapper_classes[] = 'iconic-woo-bundled-products--hide-qty';
}

if ( isset( $product->options['hide_prices'] ) && $product->options['hide_prices'] == "yes" ) {
	$wrapper_classes[] = 'iconic-woo-bundled-products--hide-prices';
}
?>

<div class="<?php echo implode( ' ', apply_filters( 'iconic_wbp_wrapper_classes', $wrapper_classes, $product ) ); ?>">
	<?php foreach ( $stl_products as $stl_product ) { ?>

		<?php if ( ! $iconic_woo_bundled_products->is_allowed_in_look( $stl_product ) ) {
			return;
		} ?>

		<?php
		$post_object = get_post( $stl_product->get_id() );
		setup_postdata( $GLOBALS['post'] =& $post_object );
		?>

		<?php $classes = array(
			'product',
			'iconic-woo-bundled-product',
			sprintf( 'iconic-woo-bundled-product--%s', $stl_product->get_type() ),
			sprintf( 'product-type-%s', $stl_product->get_type() ),
		); ?>

		<div class="<?php echo implode( ' ', apply_filters( 'iconic_wbp_product_classes', $classes, $stl_product, $product ) ); ?>" data-product-type="<?php echo $stl_product->get_type(); ?>">
			<?php do_action( 'iconic_wbp_content_product_image', $stl_product, $product ); ?>

			<div class="iconic-woo-bundled-product__summary">
				<?php do_action( 'iconic_wbp_content_product_summary', $stl_product, $product ); ?>
			</div>
		</div>

	<?php }
	wp_reset_postdata(); ?>

	<?php if ( isset( $product->options['add_all_to_cart'] ) && $product->options['add_all_to_cart'] !== "no" ) { ?>
		<form action="" method="post" class="jckstl-add-all-to-cart">
			<input type="hidden" name="jckstl-product-data" class="jckstl-add-all-to-cart__product-data">
			<input type="hidden" name="jckstl-product-id" value="<?php echo $product->get_id() ?>">
			<button type="submit" name="jckstl-add-all-to-cart" class="jckstl-add-all-to-cart__button button alt"><?php echo $product->get_add_all_to_cart_button_text(); ?></button>

			<?php do_action( 'iconic_wbp_add_all_to_cart_form', $product ); ?>
		</form>
	<?php } ?>
</div>