(function( $, document ) {

	var jckstl = {

		cache: function() {
			jckstl.els = {};
			jckstl.vars = {};

			// common vars
			jckstl.vars.a_variable = 1;
			jckstl.vars.another_variable = "some_data";

			// common elements
			jckstl.els.add_to_cart_input_fields = $( '.iconic-woo-bundled-products [name="add-to-cart"]' );
			jckstl.els.add_to_cart_qty_fields = $( '.iconic-woo-bundled-products input.qty' );
			jckstl.els.add_to_cart_variation_fields = $( '.iconic-woo-bundled-products input.variation_id' );
			jckstl.els.product_data_field = $( '.jckstl-add-all-to-cart__product-data' );
			jckstl.els.add_all_to_cart_button = $( '.jckstl-add-all-to-cart__button' );

		},

		on_ready: function() {

			// on ready stuff here
			jckstl.cache();
			jckstl.update_add_to_cart_field();
			jckstl.watch_product_fields();

		},

		/**
		 * Update the hidden add all product IDs feild
		 */
		update_add_to_cart_field: function() {
			console.log( jckstl.els );

			if ( jckstl.els.add_to_cart_input_fields.length <= 0 ) {
				return;
			}

			var products = {},
				enabled_add_to_cart = true;

			$.each( jckstl.els.add_to_cart_input_fields, function( index, input ) {

				var $input = $( input ),
					$product_wrapper = $input.closest( '.iconic-woo-bundled-product' ),
					product_id = $input.val(),
					product_type = $product_wrapper.data( 'product-type' ),
					qty = product_type === "variable" ? 1 : $input.closest( 'form' ).find( '.qty' ).val(),
					product = {};

				if ( qty === "" || parseInt( qty ) <= 0 ) {
					enabled_add_to_cart = false;
					return;
				}

				product.id = product_id;
				product.qty = qty;

				if ( product_type !== "variable" ) {
					products[ index ] = product;
					return;
				}

				var variation_id = $product_wrapper.find( '.variation_id' ).val();

				if ( variation_id === "" || parseInt( variation_id ) <= 0 || typeof variation_id === "undefined" ) {
					enabled_add_to_cart = false;
					return;
				}

				product.variation_id = variation_id;
				product.variation = jckstl.get_selected_variation( $product_wrapper );

				if ( product.variation.length <= 0 || product.variation === false ) {
					enabled_add_to_cart = false;
					return;
				}

				products[ index ] = product;

			} );

			jckstl.toggle_add_all_to_cart_button( enabled_add_to_cart );

			jckstl.els.product_data_field.val( JSON.stringify( products ) );

		},

		/**
		 * Watch the product fields for changes
		 */
		watch_product_fields: function() {

			jckstl.els.add_to_cart_input_fields.on( 'change', function() {

				jckstl.update_add_to_cart_field();

			} );

			jckstl.els.add_to_cart_qty_fields.on( 'change', function() {

				jckstl.update_add_to_cart_field();

			} );

			jckstl.els.add_to_cart_qty_fields.on( 'change', function() {

				jckstl.update_add_to_cart_field();

			} );

			jckstl.els.add_to_cart_variation_fields.on( 'change', function() {

				jckstl.update_add_to_cart_field();

			} );

		},

		/**
		 * Toggle Add all to cart button
		 *
		 * @param bool status
		 */
		toggle_add_all_to_cart_button: function( status ) {

			if ( typeof status === "undefined" ) {
				return;
			}

			var disabled = status ? false : true;

			jckstl.els.add_all_to_cart_button.prop( 'disabled', disabled );

			if ( disabled ) {
				jckstl.els.add_all_to_cart_button.addClass( 'disabled' );
			} else {
				jckstl.els.add_all_to_cart_button.removeClass( 'disabled' );
			}

		},

		/**
		 * Get selected variations
		 *
		 * @param obj $product_wrapper
		 * @return arr
		 */
		get_selected_variation: function( $product_wrapper ) {

			var attributes = {},
				$variation_selects = $product_wrapper.find( '.variations select' );

			if ( $variation_selects.length <= 0 ) {
				return false;
			}

			$.each( $variation_selects, function( index, select ) {

				var $select = $( select ),
					attribute_name = $select.data( 'attribute_name' ),
					attribute_value = $select.val();

				attributes[ attribute_name ] = attribute_value;

			} );

			return attributes;

		}

	};

	$( document ).ready( jckstl.on_ready() );

}( jQuery, document ));