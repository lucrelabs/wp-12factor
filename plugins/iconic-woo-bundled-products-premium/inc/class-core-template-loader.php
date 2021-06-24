<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( class_exists( 'Iconic_WBP_Core_Template_Loader' ) ) {
	return;
}

if ( ! class_exists( 'Gamajo_Template_Loader' ) ) {
	require plugin_dir_path( __FILE__ ) . 'vendor/class-gamajo-template-loader.php';
}

/**
 * Template loader for Iconic plugins
 *
 * Only need to specify class properties here.
 */
class Iconic_WBP_Core_Template_Loader extends Gamajo_Template_Loader {
	/**
	 * Prefix for filter names.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $filter_prefix;

	/**
	 * Directory name where custom templates for this plugin should be found in the theme.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $theme_template_directory;

	/**
	 * Reference to the root directory path of this plugin.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $plugin_directory;

	/**
	 * Construct
	 *
	 * @param string $filter_prefix
	 * @param string $theme_template_directory
	 * @param string $plugin_directory
	 */
	public function __construct( $filter_prefix, $theme_template_directory, $plugin_directory ) {
		$this->filter_prefix            = $filter_prefix;
		$this->theme_template_directory = $theme_template_directory;
		$this->plugin_directory         = $plugin_directory;
	}
}