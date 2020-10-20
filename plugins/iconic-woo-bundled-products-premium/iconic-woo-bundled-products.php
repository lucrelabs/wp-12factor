<?php
/**
 * Plugin Name: WooCommerce Bundled Products by Iconic
 * Plugin URI: https://iconicwp.com/products/woocommerce-bundled-products/
 * Description: Bundled Products plugin for WooCommerce
 * Version: 2.0.13
 * Author: Iconic
 * Author Email: support@iconicwp.com
 * Author URI: https://iconicwp.com
 * Text Domain: iconic-woo-bundled-products
 * WC requires at least: 2.6.14
 * WC tested up to: 4.0
 */

class Iconic_Woo_Bundled_Products {
	public $name = 'WooCommerce Bundled Products';

	public $slug = 'iconic-woo-bundled-products';

	public static $version = "2.0.13";

	public $product_options_name;

	public $options;

	public $current_product = false;

	/**
	 * @var null|Iconic_WBP_Core_Template_Loader
	 */
	public $templates;

	/**
	 * Class prefix
	 *
	 * @since  4.5.0
	 * @access protected
	 * @var string $class_prefix
	 */
	protected $class_prefix = "Iconic_WBP_";

	/**
	 * Construct.
	 */
	public function __construct() {
		$this->textdomain();
		$this->define_constants();
		$this->set_constants();
		$this->load_classes();

		if ( ! Iconic_WBP_Core_Helpers::is_plugin_active( 'woocommerce/woocommerce.php' ) && ! Iconic_WBP_Core_Helpers::is_plugin_active( 'woocommerce-old/woocommerce.php' ) ) {
			return;
		}

		if ( ! Iconic_WBP_Core_Licence::has_valid_licence() ) {
			return;
		}

		// Hook up to the init and plugins_loaded actions
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded_hook' ) );
		add_action( 'init', array( $this, 'initiate_hook' ) );
		add_action( 'wp', array( $this, 'wp_hook' ) );
	}

	/**
	 * Define Constants.
	 */
	private function define_constants() {
		$this->define( 'ICONIC_WBP_PATH', plugin_dir_path( __FILE__ ) );
		$this->define( 'ICONIC_WBP_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'ICONIC_WBP_INC_PATH', ICONIC_WBP_PATH . 'inc/' );
		$this->define( 'ICONIC_WBP_VENDOR_PATH', ICONIC_WBP_INC_PATH . 'vendor/' );
		$this->define( 'ICONIC_WBP_BASENAME', plugin_basename( __FILE__ ) );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name
	 * @param string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Load textdomain
	 */
	public function textdomain() {
		load_plugin_textdomain( 'iconic-woo-bundled-products', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Setup Constants for this class
	 */
	public function set_constants() {
		$this->product_options_name = $this->slug . '_options';
	}

	/**
	 * Load Classes
	 */
	private function load_classes() {
		require_once( ICONIC_WBP_INC_PATH . 'class-core-autoloader.php' );

		Iconic_WBP_Core_Autoloader::run( array(
			'prefix'   => 'Iconic_WBP_',
			'inc_path' => ICONIC_WBP_INC_PATH,
		) );

		Iconic_WBP_Core_Licence::run( array(
			'basename' => ICONIC_WBP_BASENAME,
			'urls'     => array(
				'product'  => 'https://iconicwp.com/products/woocommerce-bundled-products/',
				'settings' => admin_url( 'admin.php?page=iconic-wbp-settings' ),
				'account'  => admin_url( 'admin.php?page=iconic-wbp-settings-account' ),
			),
			'paths'    => array(
				'inc'    => ICONIC_WBP_INC_PATH,
				'plugin' => ICONIC_WBP_PATH,
			),
			'freemius' => array(
				'id'         => '1042',
				'slug'       => 'iconic-bundled-products',
				'public_key' => 'pk_a669b9e5ac3ad536e2f0c30f549a4',
				'menu'       => array(
					'slug' => 'iconic-wbp-settings',
				),
			),
		) );

		Iconic_WBP_Core_Settings::run( array(
			'vendor_path'   => ICONIC_WBP_VENDOR_PATH,
			'title'         => $this->name,
			'version'       => self::$version,
			'menu_title'    => 'Bundled Products',
			'settings_path' => ICONIC_WBP_INC_PATH . 'admin/settings.php',
			'option_group'  => 'iconic_wbp',
			'docs'          => array(
				'collection'      => '/collection/140-woocommerce-bundled-products',
				'troubleshooting' => '/collection/140-woocommerce-bundled-products',
				'getting-started' => '/category/144-getting-started',
			),
			'cross_sells'   => array(
				'iconic-woothumbs',
				'iconic-woo-attribute-swatches',
			),
		) );
		
		if ( ! Iconic_WBP_Core_Licence::has_valid_licence() ) {
			return;
		}

		$this->templates = new Iconic_WBP_Core_Template_Loader( $this->slug, 'iconic-woo-bundled-products', ICONIC_WBP_PATH );

		Iconic_WBP_Settings::run();
		Iconic_WBP_Shortcodes::run();
		Iconic_WBP_Templates::run();
	}

	/**
	 * Run quite near the start (http://codex.wordpress.org/Plugin_API/Action_Reference)
	 */
	public function plugins_loaded_hook() {
		require_once( ICONIC_WBP_PATH . '/inc/class-product-bundled.php' );
	}

	/**
	 * Run after the current user is set (http://codex.wordpress.org/Plugin_API/Action_Reference)
	 */
	public function initiate_hook() {
		// Run on admin
		if ( is_admin() ) {
			add_filter( 'product_type_selector', array( $this, 'add_product_type' ) );
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'edit_product_tabs' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'product_tab_content' ) );
			add_action( 'woocommerce_process_product_meta', array( $this, 'process_product_tabs' ), 10, 2 );
		} // Run on frontend
		else {
			add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_discount' ) );
			add_action( 'woocommerce_remove_cart_item', array( $this, 'maybe_remove_discount' ), 10, 2 );
		}
	}

	/**
	 * Run after wp is fully set up and $post is accessible (http://codex.wordpress.org/Plugin_API/Action_Reference)
	 */
	public function wp_hook() {
		if ( ! is_admin() ) {
			$this->add_frontend_hooks();
			$this->process_add_to_cart();
		}
	}

	/**
	 * Add WooCommerce hooks for product page
	 */
	public function add_frontend_hooks() {
		if ( ! is_product() ) {
			return;
		}

		$product = $this->get_current_product();

		if ( ! $product->is_type( 'bundled' ) ) {
			return;
		}

		add_action( 'woocommerce_after_single_product_summary', array( $this, 'output_bundled_products' ), 5 );
	}

	/**
	 * Plugin Styles
	 *
	 * @access public
	 */
	public function styles() {
		if ( ! is_product() ) {
			return;
		}

		$product = $this->get_current_product();

		if ( ! $product->is_type( 'bundled' ) ) {
			return;
		}

		wp_register_style( $this->slug . '_styles', ICONIC_WBP_URL . 'assets/frontend/css/main.min.css', array(), self::$version );

		wp_enqueue_style( $this->slug . '_styles' );
	}

	/**
	 * Plugin Scripts
	 *
	 * @access public
	 */
	public function scripts() {
		if ( ! is_product() ) {
			return;
		}

		$product = $this->get_current_product();

		if ( ! $product->is_type( 'bundled' ) ) {
			return;
		}

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'iconic_woo_bundled_products_scripts', ICONIC_WBP_URL . 'assets/frontend/js/main' . $min . '.js', array( 'jquery' ), self::$version, true );

		wp_enqueue_script( 'iconic_woo_bundled_products_scripts' );
	}

	/**
	 * Add the "Shop the Look" product type to the edit/add product dropdown
	 *
	 * @param array $types Current types of products
	 */
	public function add_product_type( $types ) {
		$types['bundled'] = __( 'Bundled Product' );

		return $types;
	}

	/**
	 * Edit product tabs
	 *
	 * We're adding and editing the tabs which show when "Shop the Look" is
	 * the product type.
	 *
	 * @param array $tabs All tabs
	 */
	public function edit_product_tabs( $tabs ) {
		$tabs['shipping']['class'][] = 'hide_if_bundled';

		$tabs['bundled'] = array(
			'label'  => __( 'Bundled Products', 'iconic-woo-bundled-products' ),
			'target' => 'iconic_bundled_product_data',
			'class'  => array( 'show_if_bundled' ),
		);

		return $tabs;
	}

	/**
	 * Product tab content
	 *
	 * Display the tab content for the new tabs we added in edit_product_tabs()
	 */
	public function product_tab_content() {
		global $post, $jckstlData;

		include_once( ICONIC_WBP_PATH . '/inc/product-tab-bundled-data.php' );
	}

	/**
	 * Save the new product tab content from product_tab_content()
	 */
	public function process_product_tabs( $post_id ) {
		$jckstlData = array();

		if ( isset( $_POST[ $this->product_options_name ]['product_ids'] ) ) {
			$jckstlData['product_ids'] = is_array( $_POST[ $this->product_options_name ]['product_ids'] ) ? $_POST[ $this->product_options_name ]['product_ids'] : str_replace( ' ', '', $_POST[ $this->product_options_name ]['product_ids'] );
		}

		if ( isset( $_POST[ $this->product_options_name ]['price_display'] ) ) {
			$jckstlData['price_display'] = $_POST[ $this->product_options_name ]['price_display'];
		}

		if ( isset( $_POST[ $this->product_options_name ]['bundle_price'] ) ) {
			$jckstlData['bundle_price'] = $_POST[ $this->product_options_name ]['bundle_price'];
		}

		if ( isset( $_POST[ $this->product_options_name ]['add_all_to_cart'] ) ) {
			$jckstlData['add_all_to_cart'] = $_POST[ $this->product_options_name ]['add_all_to_cart'];
		}

		if ( isset( $_POST[ $this->product_options_name ]['fixed_discount'] ) ) {
			$fixed_discount = $_POST[ $this->product_options_name ]['fixed_discount'];

			if ( $jckstlData['price_display'] == "custom" && isset( $jckstlData['bundle_price'] ) && $jckstlData['bundle_price'] < $fixed_discount ) {
				$fixed_discount = '';
			}

			$jckstlData['fixed_discount'] = $fixed_discount;
		}

		if ( isset( $_POST[ $this->product_options_name ]['hide_add_to_cart'] ) ) {
			$jckstlData['hide_add_to_cart'] = $_POST[ $this->product_options_name ]['hide_add_to_cart'];
		}

		if ( isset( $_POST[ $this->product_options_name ]['hide_qty'] ) ) {
			$jckstlData['hide_qty'] = $_POST[ $this->product_options_name ]['hide_qty'];
		}

		if ( isset( $_POST[ $this->product_options_name ]['hide_prices'] ) ) {
			$jckstlData['hide_prices'] = $_POST[ $this->product_options_name ]['hide_prices'];
		}

		update_post_meta( $post_id, $this->product_options_name, $jckstlData );
	}

	/**
	 * Remove the reviews tab for this product type
	 */
	public function remove_reviews_tab( $tabs ) {
		unset( $tabs['reviews'] );

		return $tabs;
	}

	/**
	 * Shop the Look products display
	 *
	 * Loops through all the chosen products and displays them
	 * in the summary section.
	 */
	public function output_bundled_products() {
		global $post, $product;

		$this->unset_posted_attributes();

		include( $this->templates->locate_template( 'content-bundled-products.php' ) );
	}

	/**
	 * Frontend: unset posted attributes $_POST
	 */
	public function unset_posted_attributes() {
		if ( ! empty( $_REQUEST ) ) {
			foreach ( $_REQUEST as $key => $value ) {
				if ( strpos( $key, 'attribute_' ) !== false ) {
					unset( $_REQUEST[ $key ] );
				}
			}
		}
	}

	/**
	 * Allow to remove method for a hook when it's a class method used
	 * and the class doesn't have a variable assigned, but the class name is known
	 *
	 * @hook_name  :      Name of the wordpress hook
	 * @class_name :      Name of the class where the add_action resides
	 * @method_name:      Name of the method to unhook
	 * @priority   :      The priority of which the above method has in the add_action
	 */
	public function remove_filters_for_anonymous_class( $hook_name = '', $class_name = '', $method_name = '', $priority = 0 ) {
		global $wp_filter;

		// Take only filters on right hook name and priority
		if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
			return false;
		}

		// Loop on filters registered
		foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
			// Test if filter is an array ! (always for class/method)
			if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
				// Test if object is a class, class and method is equal to param !
				if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
					unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
				}
			}
		}

		return false;
	}

	/**
	 * Get WooCommerce version number
	 */
	public function get_woo_version_number() {
		// If get_plugins() isn't available, require it
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		// Create the plugins folder and file variables
		$plugin_folder = get_plugins( '/' . 'woocommerce' );
		$plugin_file   = 'woocommerce.php';

		// If the plugin version number is set, return it
		if ( isset( $plugin_folder[ $plugin_file ]['Version'] ) ) {
			return $plugin_folder[ $plugin_file ]['Version'];
		} else {
			// Otherwise return null
			return null;
		}
	}

	/**
	 * Helper: Is product allowed in look
	 *
	 * @param WC_Product $product
	 *
	 * @return bool
	 */
	public function is_allowed_in_look( $product ) {
		if ( $product->is_type( 'grouped' ) || $product->is_type( 'bundled' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Helper: Get current product
	 */
	public function get_current_product() {
		global $post;

		if ( ! is_product() ) {
			return;
		}

		if ( $this->current_product === false ) {
			$this->current_product = wc_get_product( $post->ID );
		}

		return $this->current_product;
	}

	/**
	 * Frontend: Process add to cart action
	 */
	public function process_add_to_cart() {
		if ( ! isset( $_POST['jckstl-add-all-to-cart'] ) ) {
			return;
		}

		if ( ! isset( $_POST['jckstl-product-data'] ) || empty( $_POST['jckstl-product-data'] ) ) {
			return;
		}

		$product_json = json_decode( stripslashes( $_POST['jckstl-product-data'] ), true );

		if ( ! $product_json || empty( $product_json ) ) {
			wc_add_notice( __( 'Sorry, the products could not be added to the cart.', 'iconic-woo-bundled-products' ), 'error' );

			return;
		}

		$products_added = true;
		$instance_id    = uniqid();
		$cart_keys      = array();

		foreach ( $product_json as $product ) {
			$product_id   = $product['id'];
			$qty          = $product['qty'];
			$variation_id = isset( $product['variation_id'] ) ? $product['variation_id'] : 0;
			$variation    = isset( $product['variation'] ) ? $product['variation'] : array();

			if ( $variation_id > 0 && empty( $variation ) ) {
				wc_add_notice( __( 'Sorry, one of the products could not be added to the cart.', 'iconic-woo-bundled-products' ), 'error' );
				$products_added = false;
				continue;
			}

			$atc = WC()->cart->add_to_cart(
				$product_id,
				$qty,
				$variation_id,
				$variation,
				array(
					'instance_id' => $instance_id,
				)
			);

			if ( $atc ) {
				wc_add_to_cart_message( array( $product_id => $qty ), true );
				$cart_keys[] = $atc;
			}
		}

		if ( $products_added ) {
			$this->set_bundle_session( $_POST['jckstl-product-id'], $instance_id, $cart_keys );
		}

		if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
			wp_safe_redirect( wc_get_cart_url(), 302 );
			exit;
		}
	}

	/**
	 * Set Cookie for Bundled Discount
	 *
	 * @param int   $bundle_id
	 * @param int   $instance_id
	 * @param array $cart_keys
	 */
	public function set_bundle_session( $bundle_id, $instance_id, $cart_keys = array() ) {
		$bundle = wc_get_product( $bundle_id );

		if ( ! $bundle ) {
			return;
		}

		$bundles  = $this->get_bundle_session();
		$discount = $bundle->get_discount();

		if ( ! $bundle->has_custom_price() && $discount['amount'] <= 0 ) {
			return;
		}

		$amount = - ( $discount['amount'] );

		if ( $bundle->has_custom_price() ) {
			$cart_total_price = $this->get_cart_total_bundle_price( $cart_keys );
			$bundle_price     = $bundle->get_price();
			$amount           += - ( $cart_total_price - $bundle_price );
		}

		if ( $amount >= 0 ) {
			return;
		}

		$bundles[ $instance_id ] = array(
			'id'       => $bundle_id,
			'name'     => get_the_title( $bundle_id ),
			'discount' => $amount,
		);

		WC()->session->set( 'bundles', $bundles );
	}

	/**
	 * Helper: Get total bundle price in cart
	 *
	 * @param array $cart_keys
	 *
	 * @return float
	 */
	public function get_cart_total_bundle_price( $cart_keys ) {
		$total = 0;

		foreach ( $cart_keys as $cart_key ) {
			if ( isset( WC()->cart->cart_contents[ $cart_key ]['line_total'] ) ) {
				$total += (float) WC()->cart->cart_contents[ $cart_key ]['line_total'];
			}
		}

		return $total;
	}

	/**
	 * Get Cookie for Bundled Discount
	 *
	 * @return array
	 */
	public function get_bundle_session() {
		$bundles = WC()->session->get( 'bundles' );

		if ( $bundles ) {
			return $bundles;
		}

		return array();
	}

	/**
	 * Add discount if applicable
	 *
	 * @param WC_Cart $wc_cart
	 */
	public function add_discount( $wc_cart ) {
		$bundles = $this->get_bundle_session();

		if ( ! $bundles || empty( $bundles ) ) {
			return;
		}

		foreach ( $bundles as $instace_id => $bundle ) {
			$this->wc_cart_add_fee( $instace_id, sprintf( '"%s" %s', $bundle['name'], __( 'Discount', 'iconic-woo-bundled-products' ) ), $bundle['discount'] );
		}
	}

	/**
	 * Add fee to cart.
	 */
	public function wc_cart_add_fee( $id, $name, $amount ) {
		$fees = WC()->cart->get_fees();

		// Only add each fee once
		foreach ( $fees as $fee ) {
			if ( $fee->id == $id ) {
				return;
			}
		}

		WC()->cart->fees_api()->add_fee(
			array(
				'name'      => esc_attr( $name ),
				'amount'    => (float) $amount,
				'taxable'   => false,
				'tax_class' => '',
			)
		);
	}

	/**
	 * Maybe remove discount
	 *
	 * Check which product is being removed from the cart,
	 * and if it's aprt of a bundle, remove the discount.
	 *
	 * @param string         $cart_item_key
	 * @param        WC_Cart $wc_cart
	 */
	public function maybe_remove_discount( $cart_item_key, $wc_cart ) {
		if ( ! isset( $wc_cart->cart_contents[ $cart_item_key ]['instance_id'] ) ) {
			return;
		}

		$bundles = $this->get_bundle_session();

		if ( ! $bundles ) {
			return;
		}

		foreach ( $bundles as $instance_id => $bundle ) {
			if ( $wc_cart->cart_contents[ $cart_item_key ]['instance_id'] == $instance_id ) {
				unset( $bundles[ $instance_id ] );
				break;
			}
		}

		WC()->session->set( 'bundles', $bundles );
	}
}

$iconic_woo_bundled_products = new Iconic_Woo_Bundled_Products();