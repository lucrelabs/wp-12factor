<?php
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

/**
 * Class Iconic_WBP_Settings
 */
class Iconic_WBP_Settings {
	/**
	 * Run.
	 */
	public static function run() {
		add_filter( 'wpsf_show_save_changes_button_iconic_wbp', '__return_false' );
		add_filter( 'wpsf_show_tab_links_iconic_wbp', '__return_false' );
	}
}