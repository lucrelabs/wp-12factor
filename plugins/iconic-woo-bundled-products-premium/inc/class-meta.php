<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WBP_Meta.
 *
 * @class    Iconic_WBP_Meta
 * @version  1.0.0
 * @package  Iconic_Woo_Bundled_Products
 * @category Class
 * @author   Iconic
 */
class Iconic_WBP_Meta {
	/**
	 * Get product search field.
	 *
	 * @param array $field
	 */
	public static function product_search_feild( $field ) {
		global $post, $iconic_woo_bundled_products;

		$product_object = wc_get_product( $post->ID );
		$id             = sprintf( '%s_%s', $iconic_woo_bundled_products->product_options_name, $field['id'] );
		$field['name']  = sprintf( '%s[%s]', $iconic_woo_bundled_products->product_options_name, $field['id'] );
		$field['value'] = isset( $options[ $field['id'] ] ) ? $options[ $field['id'] ] : "";
		$product_ids    = $product_object->is_type( 'bundled' ) ? array_filter( array_map( 'absint', $product_object->get_product_ids() ) ) : array();

		if ( version_compare( WC_VERSION, '3.0.0', '<' ) ) {
			$json_ids = array();

			foreach ( $product_ids as $productId ) {
				$product = wc_get_product( $productId );

				if ( is_object( $product ) ) {
					$json_ids[ $productId ] = wp_kses_post( html_entity_decode( $product->get_formatted_name() ) );
				}
			}

			$data_selected = esc_attr( json_encode( $json_ids ) );

			woocommerce_wp_text_input( array(
				'id'                => $id,
				'name'              => $field['name'],
				'label'             => __( 'Products', 'woocommerce' ),
				'value'             => implode( ',', $product_ids ),
				'placeholder'       => __( 'Search for a product&hellip;', 'woocommerce' ),
				'class'             => "wc-product-search short",
				'type'              => "hidden",
				'custom_attributes' => array(
					'data-action'   => "woocommerce_json_search_products_and_variations",
					'data-multiple' => "true",
					'data-selected' => $data_selected,
				),
			) );
		} else {
			?>

			<p class="form-field">
				<label for="<?php echo esc_attr( $id ); ?>"><?php _e( 'Products', 'woocommerce' ); ?></label>
				<select
					class="wc-product-search"
					multiple="multiple"
					style="width: 50%;"
					id="<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( $field['name'] ); ?>[]"
					data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
					data-action="woocommerce_json_search_products_and_variations"
					data-exclude="<?php echo esc_attr( intval( $post->ID ) ); ?>"
					data-sortable="true"
				>
					<?php
					foreach ( $product_ids as $product_id ) {
						$product = wc_get_product( $product_id );
						if ( is_object( $product ) ) {
							echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
						}
					}
					?>
				</select>
			</p>

			<?php
		}

		return;
	}
}