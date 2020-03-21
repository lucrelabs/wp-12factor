<?php

/**
 * Register data store for ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder_Data_Store_CPT
 * which aims to bypass database value retrieval and simply returns default value as placeholder
 *
 * @since 4.0.10
 */
class ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder_Data_Store_CPT extends WC_Product_Variable_Data_Store_CPT implements WC_Object_Data_Store_Interface, WC_Product_Variable_Data_Store_Interface {
	/**
	 * Basically the original read() method with one exception: retruns default value (which is
	 * placeholder value) and remove all database value retrieval mechanism so any add-ons
	 * on TB refers to TB placeholder product data
	 *
	 * @since 4.0.10
	 *
	 * @param WC_Product $product Product object.
	 */
	public function read( &$product ) {
		$product->set_defaults();
	}

	/**
	 * Register product type data store
	 *
	 * @since 4.0.10
	 *
	 * @param array $stores
	 *
	 * @return array
	 */
	public static function register_store( $stores ) {
		$stores['product-tb-placeholder'] = 'ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder_Data_Store_CPT';

		return $stores;
	}
}

/**
 * Register product tb-placeholder's store
 *
 * @since 4.0.10
 */
add_filter(
	'woocommerce_data_stores',
	array( 'ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder_Data_Store_CPT', 'register_store' )
);
