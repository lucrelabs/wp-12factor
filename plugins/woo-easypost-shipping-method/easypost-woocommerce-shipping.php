<?php
/*
    Plugin Name: ELEX Easypost WooCommerce Extension (BASIC)
    Plugin URI: https://elextensions.com/plugin/easypost-shipping-method-plugin-for-woocommerce/
    Description: Using Easypost shipping APIs obtain USPS, UPS and FedEx real time shipping rates.
    Version: 1.2.4
    WC requires at least: 2.6.0
    WC tested up to: 3.5
    Author: ELEX
    Author URI: https://elextensions.com/
*/
//Dev Version 1.5.20

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Required functions
if ( ! function_exists( 'wf_is_woocommerce_active' ) ) {
	require_once( 'wf-includes/wf-functions.php' );
}

// WC active check
if ( ! wf_is_woocommerce_active() ) {
    add_action( 'admin_notices', 'xa_basic_easypost_woocommerce_inactive_notice' );
    return;
}

function xa_basic_easypost_woocommerce_inactive_notice() {
    ?>
<div id="message" class="error">
    <p>
	<?php	printf(__( '<b>WooCommerce</b> plugin must be active for <b>Easypost WooCommerce Extension (BASIC)</b> to work. ', 'wf-easypost' ) ); ?>
    </p>
</div>
<?php
}

if( !defined('WF_USPS_EASYPOST_ACCESS_KEY') )
	define("WF_USPS_EASYPOST_ACCESS_KEY", "A5DgeMLnX5ATAeZu3dKixg"); // This is test key , You can change it with live key when going production

if( !defined('WF_EASYPOST_ID') )
	define("WF_EASYPOST_ID", "wf_easypost_id");

if( !defined('WF_EASYPOST_ADV_DEBUG_MODE') )
	define("WF_EASYPOST_ADV_DEBUG_MODE", "on"); // Turn "off" to disable advanced logs.

function wf_easyshop_basic_activation_check(){
    if ( is_plugin_active('easypost-woocommerce-shipping/easypost-woocommerce-shipping.php') ){
        deactivate_plugins( basename( __FILE__ ) );
		wp_die(__("Is everything fine? You already have the Premium version installed in your website. For any issues, kindly raise a ticket via <a target='_blank' href='https://elextensions.com/support/'>Support</a>","wf-easypost"), "", array('back_link' => 1 ));
	}

    if (!function_exists('curl_init')) {
        deactivate_plugins(basename(__FILE__));
        wp_die(__('EasyPost needs the CURL PHP extension.', 'wf-easypost'));
    }

    if (!function_exists('json_decode')) {
        deactivate_plugins(basename(__FILE__));
        wp_die(__('EasyPost needs the JSON PHP extension.', 'wf-easypost'));
    }
}
register_activation_hook( __FILE__, 'wf_easyshop_basic_activation_check' );

/**
 * WC_USPS class
 */
if(!class_exists('USPS_Easypost_WooCommerce_Shipping')){
	class USPS_Easypost_WooCommerce_Shipping {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'init' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			add_action( 'woocommerce_shipping_init', array( $this, 'shipping_init' ) );
			add_filter( 'woocommerce_shipping_methods', array( $this, 'add_method' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		}

		/**
		 * Localisation
		 */
		public function init() {
			if ( ! class_exists( 'wf_order' ) ) {
		  		include_once 'includes/class-wf-legacy.php';
		  	}
			load_plugin_textdomain( 'wf-easypost', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Plugin page links
		 */
		public function plugin_action_links( $links ) {
			$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wf_easypost' ) . '">' . __( 'Settings', 'wf-easypost' ) . '</a>',

				'<a href="https://elextensions.com/support/" target="_blank">' . __( 'Support', 'wf-easypost' ) . '</a>',

				'<a href="https://elextensions.com/plugin/easypost-shipping-method-plugin-for-woocommerce/" target="_blank">' . __( 'Premium Upgrade', 'wf-easypost' ) . '</a>'

			);
			return array_merge( $plugin_links, $links );
		}

		/**
		 * Load gateway class
		 */
		public function shipping_init() {
			include_once( 'includes/class-wf-shipping-easypost.php' );
		}

		/**
		 * Add method to WC
		 */
		public function add_method( $methods ) {
			$methods[] = 'WF_Easypost';
			return $methods;
		}

		/**
		 * Enqueue scripts
		 */
		public function scripts() {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_style( 'wf-common-style', plugins_url( '/resources/css/wf_common_style.css', __FILE__ ));
			wp_enqueue_script( 'wf-common-script', plugins_url( '/resources/js/wf_common.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_script( 'wf-easypost-script', plugins_url( '/resources/js/wf_easypost.js', __FILE__ ), array( 'jquery' ) );

		}
	}
	new USPS_Easypost_WooCommerce_Shipping();
}
