<?php
/**
 * The template for displaying the add-to-cart button for a variation
 *
 * @author James Kemp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

echo wc_get_stock_html( $stl_product );

if ( $stl_product->is_in_stock() ) : ?>
	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" method="post" enctype='multipart/form-data'>
		<?php
		/**
		 * @since 2.1.0.
		 */
		do_action( 'woocommerce_before_add_to_cart_button' );

		/**
		 * @since 3.0.0.
		 */
		do_action( 'woocommerce_before_add_to_cart_quantity' );

		woocommerce_quantity_input( array(
			'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $stl_product->get_min_purchase_quantity(), $stl_product ),
			'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $stl_product->get_max_purchase_quantity(), $stl_product ),
			'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $stl_product->get_min_purchase_quantity(),
		) );

		/**
		 * @since 3.0.0.
		 */
		do_action( 'woocommerce_after_add_to_cart_quantity' );
		?>

		<button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_html( $stl_product->single_add_to_cart_text() ); ?></button>
		<input type="hidden" name="add-to-cart" value="<?php echo absint( $stl_product->get_id() ); ?>" />
		<input type="hidden" name="product_id" value="<?php echo Iconic_WBP_Backwards_Compatibility::get_parent_id( $stl_product ); ?>" />
		<input type="hidden" name="variation_id" class="variation_id" value="<?php echo absint( $stl_product->get_id() ); ?>" />

		<?php
		/**
		 * @since 2.1.0.
		 */
		do_action( 'woocommerce_after_add_to_cart_button' );
		?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
<?php endif;