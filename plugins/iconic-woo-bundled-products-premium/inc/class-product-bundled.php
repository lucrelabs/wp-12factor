<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Bundled Product Class
 *
 * Bundled product type.
 *
 * @class          WC_Product_Bundled
 * @version        1.0.0
 * @category       Class
 * @author         James Kemp
 */

if ( class_exists( 'WC_Product' ) ):
	class WC_Product_Bundled extends WC_Product {
		/**
		 * Bundled Product Ids
		 */
		public $product_ids = array();

		/**
		 * Bundled Products
		 */
		public $products = false;

		/**
		 * Bundled Prices
		 */
		public $prices = false;

		/**
		 * Bundled min price
		 */
		public $min_price = 0;

		/**
		 * Bundled max price
		 */
		public $max_price = 0;

		/**
		 * Product specific options
		 */
		public $options = array();

		/**
		 * Construct
		 *
		 * @access public
		 *
		 * @param mixed $product
		 */
		public function __construct( $product = 0 ) {
			global $iconic_woo_bundled_products;

			$product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : is_numeric( $product ) ? $product : $product->ID;

			$this->product_type = 'bundled';
			$this->options      = get_post_meta( $product_id, $iconic_woo_bundled_products->product_options_name, true );
			parent::__construct( $product_id );
		}

		/**
		 * Get internal type.
		 *
		 * @return string
		 */
		public function get_type() {
			return 'bundled';
		}

		/**
		 * Helper: Is purchasable
		 *
		 * @return bool
		 */
		public function is_purchasable() {
			return false;
		}

		/**
		 * Helper: Get Bundled IDs
		 */
		public function get_product_ids() {
			global $post, $iconic_woo_bundled_products;

			if ( empty( $this->product_ids ) ) {
				$product_ids = get_post_meta( $post->ID, $iconic_woo_bundled_products->product_options_name, true );

				if ( ! $product_ids || empty( $product_ids['product_ids'] ) ) {
					return $this->product_ids;
				}

				$product_ids = is_array( $product_ids['product_ids'] ) ? $product_ids['product_ids'] : explode( ',', $product_ids['product_ids'] );

				$this->product_ids = $product_ids;
			}

			return $this->product_ids;
		}

		/**
		 * Helper: Get Bundled products
		 */
		public function get_products() {
			if ( $this->products !== false ) {
				return apply_filters( 'iconic_wbp_products', $this->products, $this );
			}

			$this->products = array();
			$product_ids    = $this->get_product_ids();

			if ( ! empty( $product_ids ) ) {
				foreach ( $product_ids as $product_id ) {
					$product = wc_get_product( $product_id );

					if ( ! $product ) {
						continue;
					}

					$this->products[] = $product;
				}
			}

			return apply_filters( 'iconic_wbp_products', $this->products, $this );
		}

		/**
		 * Helper: Get price
		 *
		 * @param string $context
		 *
		 * @return int
		 */
		public function get_price( $context = 'view' ) {
			if ( ! isset( $this->options['price_display'] ) ) {
				return false;
			}

			if ( $this->options['price_display'] == "" ) {
				return false;
			}

			if ( $this->options['price_display'] == "range" ) {
				return apply_filters( 'woocommerce_stl_price', $this->get_min_price(), $this );
			} elseif ( $this->options['price_display'] == "combined" ) {
				$prices = $this->get_prices();

				$prices = array_merge( $prices['min'], $prices['single'] );

				return apply_filters( 'woocommerce_stl_price', array_sum( $prices ), $this );
			} elseif ( $this->has_custom_price() ) {
				return apply_filters( 'woocommerce_stl_price', $this->options['bundle_price'], $this );
			}

			return false;
		}

		/**
		 * Helper: Has custom price
		 */
		public function has_custom_price() {
			return $this->options['price_display'] == "custom";
		}

		/**
		 * Get variation price HTML. Prices are not inherited from parents.
		 *
		 * @return string containing the formatted price
		 */
		public function get_price_html( $price = '' ) {
			global $iconic_woo_bundled_products;

			$price = $this->get_price();

			if ( ! $price ) {
				return '';
			}

			if ( $price === 0 ) {
				$price = apply_filters( 'woocommerce_stl_free_price_html', __( 'Free!', 'woocommerce' ), $this );
			} elseif ( $this->options['price_display'] == "range" ) {
				$price = $this->get_min_price() !== $this->get_max_price() ? sprintf( '%1$s&ndash;%2$s', wc_price( $this->get_min_price() ), wc_price( $this->get_max_price() ) ) : wc_price( $this->get_min_price() );
			} elseif ( $this->options['price_display'] == "combined" ) {
				$discount = $this->get_discount();

				if ( $discount['amount'] > 0 ) {
					$min_price_discounted = $this->get_min_price() - $discount['amount'];
					$max_price_discounted = $this->get_max_price() - $discount['amount'];

					$price = $this->get_min_price() !== $this->get_max_price() ? sprintf( '<del>%s&ndash;%s</del> <ins>%s&ndash;%s</ins>', wc_price( $this->get_min_price() ), wc_price( $this->get_max_price() ), wc_price( $min_price_discounted ), wc_price( $max_price_discounted ) ) : sprintf( '<del>%s</del> <ins>%s</ins>', wc_price( $this->get_min_price() ), wc_price( $min_price_discounted ) );
				} else {
					$price = $this->get_min_price() !== $this->get_max_price() ? sprintf( '%1$s&ndash;%2$s', wc_price( $this->get_min_price() ), wc_price( $this->get_max_price() ) ) : wc_price( $this->get_min_price() );
				}
			} else {
				$price = apply_filters( 'woocommerce_stl_price_html', wc_price( $price ) . $this->get_price_suffix(), $this );
			}

			return apply_filters( 'woocommerce_get_price_html', $price, $this );
		}

		/**
		 * Get Prices
		 *
		 * @param str $type min/max
		 *
		 * @return str
		 */
		public function get_prices() {
			if ( $this->prices === false ) {
				$prices   = array(
					'min'    => array(),
					'max'    => array(),
					'single' => array(),
				);
				$products = $this->get_products();

				if ( ! empty( $products ) ) {
					foreach ( $products as $stl_product ) {
						if ( $stl_product->is_type( 'variable' ) ) {
							$variation_prices = $stl_product->get_variation_prices();

							if ( empty( $variation_prices['price'] ) ) {
								$prices['single'][] = $stl_product->get_price();
								continue;
							}
							
							$min_price = current( $variation_prices['price'] );
							$max_price = end( $variation_prices['price'] );

							$prices['min'][] = $min_price;
							$prices['max'][] = $max_price;
						} else {
							$prices['single'][] = $stl_product->get_price();
						}
					}
				}

				$this->prices = $prices;
			}

			return $this->prices;
		}

		/**
		 * Get min price
		 *
		 * @return int
		 */
		public function get_min_price( $type = null ) {
			if ( $this->min_price === 0 || ! is_null( $type ) ) {
				$prices = $this->get_prices();

				$prices = array_merge( $prices['min'], $prices['single'] );

				$this->min_price = $this->options['price_display'] == "combined" || $type === 'combined' ? array_sum( $prices ) : min( $prices );
			}

			return (float) $this->min_price;
		}

		/**
		 * Get max price
		 *
		 * @param string|bool $type
		 *
		 * @return int
		 */
		public function get_max_price( $type = null ) {
			if ( $this->max_price === 0 || ! is_null( $type ) ) {
				$prices = $this->get_prices();

				$prices = array_merge( $prices['max'], $prices['single'] );

				if ( $this->options['price_display'] == 'range' || $type === 'range' ) {
					$this->max_price = max( $prices );
				} elseif ( $this->options['price_display'] == 'combined' || $type === 'combined' ) {
					$this->max_price = array_sum( $prices );
				} else {
					$this->max_price = min( $prices );
				}
			}

			return (float) $this->max_price;
		}

		/**
		 * Get discount
		 */
		public function get_discount() {
			$discount = isset( $this->options['fixed_discount'] ) ? $this->options['fixed_discount'] : false;

			if ( ! $discount ) {
				return array(
					'type'      => 'none',
					'amount'    => 0,
					'formatted' => wc_price( 0 ),
				);
			}

			return array(
				'type'      => 'fixed',
				'amount'    => (float) $discount,
				'formatted' => wc_price( $discount ),
			);
		}

		/**
		 * Get add all to cart button text.
		 *
		 * @return string
		 */
		public function get_add_all_to_cart_button_text() {
			$discount = $this->get_discount();
			$up_to    = false;

			if ( $this->has_custom_price() ) {
				$price              = $this->get_price();
				$min_combined_price = $this->get_min_price( 'combined' );
				$max_combined_price = $this->get_max_price( 'combined' );
				$price_discount     = $max_combined_price - $price;
				$up_to              = $max_combined_price > $min_combined_price ? __( 'up to', 'iconic-woo-bundled-products' ) : false;

				$discount['amount'] = $discount['amount'] + $price_discount;
			}

			$button_text = $discount['amount'] > 0 ? sprintf( __( 'Add All to Cart &mdash; Save %s %s', 'iconic-woo-bundled-products' ), $up_to, wc_price( $discount['amount'] ) ) : __( 'Add All to Cart', 'iconic-woo-bundled-products' );

			return apply_filters( 'iconic_wbp_add_all_to_cart_text', $button_text, $this );
		}
	}
endif;