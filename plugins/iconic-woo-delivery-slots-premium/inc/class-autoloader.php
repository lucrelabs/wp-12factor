<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class autoloader.
 */
class Iconic_WDS_Autoloader {
	/**
	 * Class prefix
	 *
	 * @var null|string $class_prefix
	 */
	protected $class_prefix = null;

	/**
	 * Inc path.
	 *
	 * @var null|string $inc_path
	 */
	protected $inc_path = null;

	/**
	 * Init.
	 */
	public function __construct( $class_prefix, $inc_path ) {
		spl_autoload_register( array( $this, 'autoload' ) );

		$this->class_prefix = $class_prefix;
		$this->inc_path = $inc_path;

		add_action( 'plugins_loaded', array( $this, 'register_versioned_classes' ) );
	}

	/**
	 * Register versioned classes for use after plugins loaded.
	 */
	public function register_versioned_classes() {
		$versioned_classes_file = $this->inc_path . '/vendor/versioned-classes.php';

		if ( ! file_exists( $versioned_classes_file ) ) {
			return;
		}

		require_once( $versioned_classes_file );
	}

	/**
	 * Autoloader
	 *
	 * Classes should reside within /inc and follow the format of
	 * Iconic_The_Name ~ class-the-name.php or {{class-prefix}}The_Name ~ class-the-name.php
	 */
	private function autoload( $class_name ) {
		/**
		 * If the class being requested does not start with our prefix,
		 * we know it's not one in our project
		 */
		if ( 0 !== strpos( $class_name, 'Iconic_' ) && 0 !== strpos( $class_name, $this->class_prefix ) ) {
			return;
		}

		$file_name = strtolower( str_replace(
			array( $this->class_prefix, 'Iconic_', '_' ),      // Prefix | Plugin Prefix | Underscores
			array( '', '', '-' ),                              // Remove | Remove | Replace with hyphens
			$class_name
		) );

		// Compile our path from the current location
		$file = dirname( __FILE__ ) . '/class-' . $file_name . '.php';

		// If a file is found
		if ( file_exists( $file ) ) {
			// Then load it up!
			require( $file );

			return;
		}

		self::autoload_versioned( $class_name );
	}

	/**
	 * Load versioned classes.
	 */
	public function autoload_versioned( $class_name ) {
		if ( empty( $GLOBALS['iconic_versioned_classes'] ) || empty( $GLOBALS['iconic_versioned_classes'][ $class_name ] ) ) {
			return;
		}

		$versions = $GLOBALS['iconic_versioned_classes'][ $class_name ];

		// sort the versions
		uksort( $versions, 'version_compare' );

		// get the latest versions (the last in the array)
		$latest = end( $versions );

		if ( file_exists( $latest ) && ! class_exists( $class_name ) ) {
			require_once( $latest );
		}
	}
}