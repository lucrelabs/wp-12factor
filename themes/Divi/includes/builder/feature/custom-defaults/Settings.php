<?php

class ET_Builder_Custom_Defaults_Settings {
	const CUSTOM_DEFAULTS_OPTION = 'builder_custom_defaults';
	const CUSTOM_DEFAULTS_UNMIGRATED_OPTION = 'builder_custom_defaults_unmigrated';
	const CUSTOMIZER_SETTINGS_MIGRATED_FLAG = 'customizer_settings_migrated_flag';

	/**
	 * @var array - The list of the product short names we allowing to do a Module Customizer settings migration rollback
	 */
	public static $allowed_products = array(
		'divi'  => '3.26',
		'extra' => '2.26',
	);

	// Migration phase two settings
	public static $phase_two_settings = array(
		'body_font_size',
		'captcha_font_size',
		'caption_font_size',
		'filter_font_size',
		'form_field_font_size',
		'header_font_size',
		'meta_font_size',
		'number_font_size',
		'percent_font_size',
		'price_font_size',
		'sale_badge_font_size',
		'sale_price_font_size',
		'subheader_font_size',
		'title_font_size',
		'toggle_font_size',
		'icon_size',
		'padding',
		'custom_padding',
	);

	protected static $_module_additional_slugs = array(
		'et_pb_section' => array(
			'et_pb_section_fullwidth',
			'et_pb_section_specialty',
		),
		'et_pb_slide'   => array(
			'et_pb_slide_fullwidth',
		),
		'et_pb_column'  => array(
			'et_pb_column_specialty',
		),
	);

	protected static $_module_types_conversion_map = array(
		'et_pb_section'      => '_convert_section_type',
		'et_pb_column'       => '_convert_column_type',
		'et_pb_column_inner' => '_convert_column_type',
		'et_pb_slide'        => '_convert_slide_type',
	);

	protected static $_instance;
	protected $_settings;

	protected function __construct() {
		$custom_defaults = et_get_option( self::CUSTOM_DEFAULTS_OPTION, (object) array(), '', true );

		$this->_settings = $this->_normalize_custom_defaults( $custom_defaults );

		$this->_register_hooks();
	}

	protected function _register_hooks() {
		add_action( 'et_after_version_rollback', array( $this, 'after_version_rollback' ), 10, 3 );
	}

	/**
	 * Returns instance of the singleton class
	 *
	 * @since 3.26
	 *
	 * @return ET_Builder_Custom_Defaults_Settings
	 */
	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Returns the list of additional module slugs used to separate Custom Default settings.
	 * For example defaults for sections must be separated depends on the section type (regular, fullwidth or specialty).
	 *
	 * @since 3.26
	 *
	 * @param $module_slug - The module slug for which additional slugs are looked up
	 *
	 * @return array       - The list of the additional slugs
	 */
	public function get_module_additional_slugs( $module_slug ) {
		if ( ! empty( self::$_module_additional_slugs[ $module_slug ] ) ) {
			return self::$_module_additional_slugs[ $module_slug ];
		}

		return array();
	}

	/**
	 * Returns builder custom defaults settings.
	 *
	 * @since 3.26
	 *
	 * @return object
	 */
	public function get_custom_defaults() {
		return $this->_settings;
	}

	/**
	 * Returns custom defaults for the particular module.
	 *
	 * @since 3.26
	 *
	 * @param $module_slug
	 *
	 * @return array
	 */
	public function get_module_custom_defaults( $module_slug ) {
		$result = array();

		if ( isset( $this->_settings->{$module_slug} ) ) {
			$result = (array) $this->_settings->{$module_slug};
		}

		return $result;
	}

	public static function is_customizer_migrated() {
		return et_get_option( self::CUSTOMIZER_SETTINGS_MIGRATED_FLAG, false );
	}

	/**
	 * Migrates Module Customizer settings to Custom Defaults
	 *
	 * @since 3.26
	 *
	 * @param array $defaults - The list of modules default settings
	 *
	 */
	public function migrate_customizer_settings( $defaults ) {
		$template_directory = get_template_directory();

		require_once $template_directory . '/includes/module-customizer/migrations.php';

		$migrations = ET_Module_Customizer_Migrations::instance();

		list (
			$custom_defaults,
			$custom_defaults_unmigrated,
			) = $migrations->migrate( $defaults );

		et_update_option( self::CUSTOM_DEFAULTS_OPTION, (object) $custom_defaults );
		et_update_option( self::CUSTOMIZER_SETTINGS_MIGRATED_FLAG, true );

		if ( ! empty( $custom_defaults_unmigrated ) ) {
			et_update_option( self::CUSTOM_DEFAULTS_UNMIGRATED_OPTION, (object) $custom_defaults_unmigrated );
		} else {
			et_update_option( self::CUSTOM_DEFAULTS_UNMIGRATED_OPTION, false );
		}
	}

	/**
	 * Handles theme version rollback.
	 *
	 * @since 3.26
	 *
	 * @param string $product_name - The short name of the product rolling back.
	 * @param string $rollback_from_version
	 * @param string $rollback_to_version
	 */
	public function after_version_rollback( $product_name, $rollback_from_version, $rollback_to_version ) {
		if ( ! isset( self::$allowed_products[ $product_name ] ) ) {
			return;
		}

		if ( 0 > version_compare( $rollback_to_version, self::$allowed_products[ $product_name ] ) ) {
			et_delete_option( self::CUSTOMIZER_SETTINGS_MIGRATED_FLAG );
			et_delete_option( self::CUSTOM_DEFAULTS_UNMIGRATED_OPTION );
		}
	}

	/**
	 * Converts module type (slug).
	 * Used to separate custom defaults settings for modules sharing the same slug but having different meaning
	 * For example: Regular, Fullwidth and Specialty section types
	 *
	 * @since 3.26
	 *
	 * @param string $type - The module type (slug)
	 * @param array $attrs - The module attributes
	 *
	 * @return string      - The converted module type (slug)
	 */
	public function maybe_convert_module_type( $type, $attrs ) {
		if ( isset( self::$_module_types_conversion_map[ $type ] ) ) {
			// @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found
			$type = call_user_func_array(
				array( $this, self::$_module_types_conversion_map[ $type ] ),
				array( $attrs, $type )
			);
		}

		return $type;
	}

	/**
	 * Converts Section module slug to appropriate slug used in Custom Defaults
	 *
	 * @since 3.26
	 *
	 * @param array $attrs - The section attributes
	 *
	 * @return string      - The converted section type depends on the section attributes
	 */
	protected function _convert_section_type( $attrs ) {
		if ( isset( $attrs['fullwidth'] ) && 'on' === $attrs['fullwidth'] ) {
			return 'et_pb_section_fullwidth';
		}

		if ( isset( $attrs['specialty'] ) && 'on' === $attrs['specialty'] ) {
			return 'et_pb_section_specialty';
		}

		return 'et_pb_section';
	}

	/**
	 * Converts Slide module slug to appropriate slug used in Custom Defaults
	 *
	 * @since 3.26
	 *
	 * @return string - The converted slide type depends on the parent slider type
	 */
	protected function _convert_slide_type() {
		global $et_pb_slider_parent_type;

		if ( $et_pb_slider_parent_type === 'et_pb_fullwidth_slider' ) {
			return 'et_pb_slide_fullwidth';
		}

		return 'et_pb_slide';
	}

	/**
	 * Converts Column module slug to appropriate slug used in Custom Defaults
	 *
	 * @since 3.26
	 *
	 * @return string - The converted column type
	 */
	protected function _convert_column_type( $attrs, $type ) {
		global $et_pb_parent_section_type;

		if ( 'et_pb_column_inner' === $type ) {
			return 'et_pb_column';
		}

		if ( 'et_pb_specialty_section' === $et_pb_parent_section_type
		     || ( isset( $attrs['specialty_columns'] ) && '' !== $attrs['specialty_columns'] ) ) {
			return 'et_pb_column_specialty';
		}

		return 'et_pb_column';
	}

	/**
	 * Filters custom defaults to avoid non plain values like arrays or objects
	 *
	 * @since 3.26.7 This function has been added to avoid unexpected values. See https://github.com/elegantthemes/Divi/issues/16082
	 *
	 * @param $value - The custom defaults value
	 *
	 * @return bool
	 */
	protected static function _filter_custom_defaults( $value ) {
		return ! is_object( $value ) && ! is_array( $value );
	}

	/**
	 * Performs custom defaults format normalization.
	 * Usually used to cast format from array to object
	 *
	 * @since 3.27.4
	 *
	 * @param $defaults - The list of custom defaults needs to be normalized
	 *
	 * @return object
	 */
	protected function _normalize_custom_defaults( $defaults ) {
		$result = (object) array();

		foreach ( $defaults as $module => $settings ) {
			$settings_filtered = array_filter( (array) $settings, array( $this, '_filter_custom_defaults' ) );

			// Since we still support PHP 5.2 we can't use `array_filter` with array keys
			// So check if defaults have empty key
			if ( isset( $settings_filtered[''] ) ) {
				continue;
			}

			$result->$module = (object) array();

			foreach ( $settings_filtered as $setting_name => $value ) {
				$result->$module->$setting_name = $value;
			}
		}

		return $result;
	}
}

ET_Builder_Custom_Defaults_Settings::instance();
