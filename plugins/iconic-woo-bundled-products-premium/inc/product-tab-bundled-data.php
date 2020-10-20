<?php $options = get_post_meta( $post->ID, $this->product_options_name, true ); ?>

<div id="iconic_bundled_product_data" class="panel woocommerce_options_panel">

	<div class="options_group">

		<?php

		$fields = array();

		$fields[] = array(
			'type' => 'product_ids',
			'id'   => 'product_ids',
		);

		$fields[] = array(
			'type'        => 'select',
			'id'          => 'price_display',
			'label'       => __( 'Price Display', 'iconic-woo-bundled-products' ),
			'desc_tip'    => true,
			'description' => '',
			'options'     => array(
				''         => __( 'None', 'iconic-woo-bundled-products' ),
				'combined' => __( 'Combined', 'iconic-woo-bundled-products' ),
				'range'    => __( 'Range', 'iconic-woo-bundled-products' ),
				'custom' => __('Custom', 'iconic-woo-bundled-products')
			),
		);

		$fields[] = array(
			'type' => 'text',
			'id' => 'bundle_price',
			'label' => sprintf( __('Bundle Price (%s)','iconic-woo-bundled-products'), get_woocommerce_currency_symbol()),
			'desc_tip' => true,
			'description' => __('If you selected "Custom" for "Price Display", enter the total price for your bundle here. Note: this is only effective if using "Add All to Cart" button.','iconic-woo-bundled-products'),
			'data_type' => 'price'
		);

		$fields[] = array(
			'type'    => 'select',
			'id'      => 'add_all_to_cart',
			'label'   => __( 'Enable "Add All to Cart"?', 'iconic-woo-bundled-products' ),
			'options' => array(
				'no'  => __( 'No', 'iconic-woo-bundled-products' ),
				'yes' => __( 'Yes', 'iconic-woo-bundled-products' ),
			),
		);

		$fields[] = array(
			'type'    => 'select',
			'id'      => 'hide_add_to_cart',
			'label'   => __( 'Hide Individual "Add to Cart" Buttons?', 'iconic-woo-bundled-products' ),
			'options' => array(
				'no'  => __( 'No', 'iconic-woo-bundled-products' ),
				'yes' => __( 'Yes', 'iconic-woo-bundled-products' ),
			),
		);

		$fields[] = array(
			'type'    => 'select',
			'id'      => 'hide_qty',
			'label'   => __( 'Hide Individual Quantity Fields?', 'iconic-woo-bundled-products' ),
			'options' => array(
				'no'  => __( 'No', 'iconic-woo-bundled-products' ),
				'yes' => __( 'Yes', 'iconic-woo-bundled-products' ),
			),
		);

		$fields[] = array(
			'type'    => 'select',
			'id'      => 'hide_prices',
			'label'   => __( 'Hide Individual Prices?', 'iconic-woo-bundled-products' ),
			'options' => array(
				'no'  => __( 'No', 'iconic-woo-bundled-products' ),
				'yes' => __( 'Yes', 'iconic-woo-bundled-products' ),
			),
		);

		$fields[] = array(
			'type' => 'text',
			'id' => 'fixed_discount',
			'label' => sprintf( __('Fixed Discount (%s)','iconic-woo-bundled-products'), get_woocommerce_currency_symbol()),
			'data_type' => 'price',
			'desc_tip' => true,
			'description' => sprintf( __('Enter an amount in %s to apply a fixed discount when all products are added to the cart.','iconic-woo-bundled-products'), get_woocommerce_currency_symbol() ),
		);

		foreach ( $fields as $field ) {
			$id             = sprintf( '%s_%s', $this->product_options_name, $field['id'] );
			$field['name']  = sprintf( '%s[%s]', $this->product_options_name, $field['id'] );
			$field['value'] = isset( $options[ $field['id'] ] ) ? $options[ $field['id'] ] : "";

			if ( $field['type'] == "text" ) {
				woocommerce_wp_text_input( $field );
			} elseif ( $field['type'] == "select" ) {
				woocommerce_wp_select( $field );
			} elseif ( $field['type'] == "product_ids" ) {
				Iconic_WBP_Meta::product_search_feild( $field );
			}
		} ?>

	</div>

</div>