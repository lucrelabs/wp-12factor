<?php
/**
 * Functions used by plugins
 */
if ( ! class_exists( 'Wf_Dependencies' ) )
	require_once 'class-wf-dependencies.php';

/**
 * WC Detection
 */
if ( ! function_exists( 'wf_is_woocommerce_active' ) ) {
	function wf_is_woocommerce_active() {
		return Wf_Dependencies::woocommerce_active_check();
	}
}