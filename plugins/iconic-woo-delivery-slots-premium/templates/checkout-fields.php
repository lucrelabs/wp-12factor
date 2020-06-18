<?php
/**
 * The template for the checkout fields.
 *
 * @package Iconic_WDS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $fields ) ) {
	return;
} ?>

<?php
/**
 * Fires before the checkout fields container.
 */
do_action( 'iconic_wds_before_checkout_fields' );
?>

	<div id="jckwds-fields" class="iconic-wds-fields woocommerce-billing-fields <?php echo esc_attr( ! $active ? 'jckwds-fields-inactive' : '' ); ?>" style="<?php echo esc_attr( ! $active ? 'display: none;' : '' ); ?>">
		<h3 class="iconic-wds-fields__title"><?php echo wp_kses_post( Iconic_WDS_Helpers::get_label( 'details' ) ); ?></h3>

		<?php
		/**
		 * Fires after the "Delivery Details" title.
		 */
		do_action( 'iconic_wds_after_delivery_details_title' );
		?>

		<?php foreach ( $fields as $field_name => $field_data ) { ?>
			<?php
			/**
			 * Fires before each checkout field.
			 *
			 * @param array $field_data
			 */
			do_action( 'iconic_wds_before_delivery_details_field_wrapper', $field_data );
			?>

			<div id="<?php echo esc_attr( $field_name ); ?>-wrapper">
				<?php $field_data = apply_filters( 'iconic_wds_checkout_field_data', $field_data ); ?>

				<?php if ( 'hidden' === $field_data['field_args']['type'] ) { ?>
					<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_data['value'] ); ?>">
				<?php } else { ?>
					<?php woocommerce_form_field( $field_name, $field_data['field_args'], $field_data['value'] ); ?>
				<?php } ?>
			</div>

			<?php
			/**
			 * Fires after each checkout field.
			 *
			 * @param array $field_data
			 */
			do_action( 'iconic_wds_after_delivery_details_field_wrapper', $field_data );
			?>
		<?php } ?>

		<?php
		/**
		 * Fires after the checkout fields, but inside the container.
		 */
		do_action( 'iconic_wds_after_delivery_details_fields' );
		?>

		<input type="hidden" name="iconic-wds-fields-hidden" value="<?php echo $active ? 0 : 1; ?>">
	</div>

<?php
/**
 * Fires after the checkout fields container.
 */
do_action( 'iconic_wds_after_checkout_fields' );
