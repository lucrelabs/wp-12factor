<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WDS_Settings.
 *
 * @class    Iconic_WDS_Settings
 * @version  1.0.0
 * @author   Iconic
 */
class Iconic_WDS_Settings {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'init', array( __CLASS__, 'init_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ), 20 );
		add_filter( 'jckwds_settings_validate', array( __CLASS__, 'validate_settings' ), 10, 1 );
	}

	/**
	 * Init settings class.
	 */
	public static function init_settings() {
		global $jckwds;
		$jckwds->set_settings( Iconic_WDS_Core_Settings::$settings );
	}

	/**
	 * Scripts and styles on settings page.
	 */
	public static function assets() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( $screen_id !== 'woocommerce_page_jckwds-settings' ) {
			return;
		}

		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_script( 'wc-enhanced-select' );
	}

	/**
	 * Get category options.
	 *
	 * @return array|null
	 */
	public static function get_category_options() {
		static $categories = null;

		if ( ! is_null( $categories ) ) {
			return $categories;
		}

		$categories       = array();
		$categories_query = get_terms( array(
			'taxonomy' => 'product_cat',
		) );

		if ( empty( $categories_query ) ) {
			return $categories;
		}

		foreach ( $categories_query as $category ) {
			$categories[ $category->term_id ] = $category->name;
		}

		return $categories;
	}

	/**
	 * Get exclude products custom field.
	 *
	 * @return string
	 */
	public static function get_exclude_products_field() {
		ob_start();

		$exclude_products = wpsf_get_setting( 'jckwds', 'general_setup', 'exclude_products' );

		?>
		<style>
			.select2-container .select2-search--inline .select2-search__field {
				padding: 0;
				min-height: 28px;
			}

			.select2-container .select2-selection--multiple {
				border: 1px solid #7F8993;
				box-shadow: none;
				border-radius: 3px;
				box-sizing: border-box;
				padding-left: 8px;
				padding-right: 8px;
			}

			.select2-container--default .select2-selection--multiple .select2-selection__rendered {
				padding: 0;
			}

			.select2-container--default .select2-selection--multiple .select2-selection__rendered li {
				height: 27px;
			}

			.select2-dropdown,
			.select2-container--open .select2-selection--multiple {
				border-color: #000;
			}
		</style>
		<select class="wc-product-search" multiple="multiple" style="width: 25em;" id="exclude_products" name="jckwds_settings[general_setup_exclude_products][]" data-sortable="true" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products">
			<?php
			$product_ids = ! empty( $exclude_products ) ? $exclude_products : array();

			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( is_object( $product ) ) {
					echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
				}
			}
			?>
		</select>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get day fees fields for settings page.
	 *
	 * @return false|string
	 */
	public static function get_day_fees_fields() {
		if ( ! is_admin() ) {
			return;
		}

		ob_start();

		$day_fees = self::get_day_fees();
		$days     = self::get_days();
		?>
		<table class="iconic-wds-table" cellpadding="0" cellspacing="0">
			<tbody>
			<?php foreach ( $day_fees as $day => $fee ) { ?>
				<tr>
					<td class="iconic-wds-table__column">
						<input type="number" id="general_setup_shipping_methods_<?php echo esc_attr( $day ); ?>" name="jckwds_settings[datesettings_fees_days][<?php echo esc_attr( $day ); ?>]" value="<?php echo ! empty( $fee ) ? esc_attr( $fee ) : ''; ?>">
					</td>
					<td class="iconic-wds-table__column iconic-wds-table__column--label">
						<label for="general_setup_shipping_methods_<?php echo esc_attr( $day ); ?>"><?php echo esc_attr( $days[ $day ] ); ?></label>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get days of the week.
	 *
	 * @return array
	 */
	public static function get_days() {
		return array(
			0 => __( 'Sunday', 'jckwds' ),
			1 => __( 'Monday', 'jckwds' ),
			2 => __( 'Tuesday', 'jckwds' ),
			3 => __( 'Wednesday', 'jckwds' ),
			4 => __( 'Thursday', 'jckwds' ),
			5 => __( 'Friday', 'jckwds' ),
			6 => __( 'Saturday', 'jckwds' ),
		);
	}

	/**
	 * Get day fees.
	 *
	 * @return array
	 */
	public static function get_day_fees() {
		$day_fees_setting = wpsf_get_setting( 'jckwds', 'datesettings_fees', 'days' );

		if ( empty( $day_fees_setting ) ) {
			return array(
				0 => 0,
				1 => 0,
				2 => 0,
				3 => 0,
				4 => 0,
				5 => 0,
				6 => 0,
			);
		}

		return array_map( 'floatval', $day_fees_setting );
	}

	/**
	 * Get delivery days fields for settings page.
	 *
	 * @return string
	 */
	public static function get_delivery_days_fields() {
		if ( ! is_admin() ) {
			return;
		}

		ob_start();

		$delivery_days = self::get_delivery_days();
		$max_orders    = self::get_delivery_days_max_orders();
		$days          = self::get_days();
		?>
		<style>
			.iconic-wds-table {
				table-layout: fixed;
				max-width: 100%;
				border-collapse: collapse;
				border-radius: 4px;
				background: #F9F9F9;
				border: 1px solid #CCD0D4;
			}

			.iconic-wds-table tr,
			.iconic-wds-table thead {
				border-bottom: 1px solid #E5E5E5;
			}

			.iconic-wds-table tr:last-child {
				border: none;
			}

			.iconic-wds-table__column {
				padding: 8px 14px !important;
				vertical-align: middle !important;
				text-align: left;
				height: 30px;
				border-left: none;
			}

			.iconic-wds-table__column--checkbox {
				width: 20px !important;
				padding-right: 0 !important;
				border-right: none;
			}

			.iconic-wds-table__column--label {
				padding-left: 4px !important;
				max-width: 260px;
			}
		</style>
		<table class="iconic-wds-table" cellpadding="0" cellspacing="0">
			<thead>
			<tr>
				<th colspan="2" class="iconic-wds-table__column iconic-wds-table__column--label">&nbsp;</th>
				<th class="iconic-wds-table__column iconic-wds-table__column--input"><?php esc_attr_e( 'Maximum Orders', 'jckwds' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $days as $day_number => $day_name ) { ?>
				<?php $checked = in_array( $day_number, $delivery_days, true ); ?>
				<?php $max_order = isset( $max_orders[ $day_number ] ) && is_numeric( $max_orders[ $day_number ] ) ? $max_orders[ $day_number ] : ''; ?>
				<tr>
					<td class="iconic-wds-table__column iconic-wds-table__column--checkbox">
						<input type="checkbox" name="jckwds_settings[datesettings_datesettings_days][<?php echo esc_attr( $day_number ); ?>]" id="datesettings_datesettings_days_<?php echo esc_attr( $day_number ); ?>" value="<?php echo esc_attr( $day_number ); ?>" <?php checked( $checked ); ?>>
					</td>
					<td class="iconic-wds-table__column iconic-wds-table__column--label"><label for="datesettings_datesettings_days_<?php echo esc_attr( $day_number ); ?>"><?php echo esc_attr( $day_name ); ?></label></td>
					<td class="iconic-wds-table__column iconic-wds-table__column--input">
						<input type="number" name="jckwds_settings[datesettings_datesettings_max_orders][<?php echo esc_attr( $day_number ); ?>]" id="datesettings_datesettings_max_orders_<?php echo esc_attr( $day_number ); ?>" value="<?php echo esc_attr( $max_order ); ?>">
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get delivery days fields for settings page.
	 *
	 * @return string
	 */
	public static function get_field_labels_by_shipping_method() {
		if ( ! is_admin() ) {
			return;
		}

		global $jckwds;

		$shipping_methods                = $jckwds->get_shipping_method_options();
		$selected_shipping_methods       = self::get_shipping_methods();
		$selected_shipping_method_labels = self::get_shipping_method_labels();

		ob_start();
		?>
		<table class="iconic-wds-table" cellpadding="0" cellspacing="0">
			<thead>
			<tr>
				<th colspan="2" class="iconic-wds-table__column iconic-wds-table__column--label">&nbsp;</th>
				<th class="iconic-wds-table__column iconic-wds-table__column--input"><?php esc_attr_e( 'Labels', 'jckwds' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $shipping_methods as $shipping_method_value => $shipping_method_label ) { ?>
				<?php $checked = in_array( $shipping_method_value, $selected_shipping_methods, true ); ?>
				<?php $label = ! empty( $selected_shipping_method_labels[ $shipping_method_value ] ) ? $selected_shipping_method_labels[ $shipping_method_value ] : 'default'; ?>
				<tr>
					<td class="iconic-wds-table__column iconic-wds-table__column--checkbox">
						<input type="checkbox" name="jckwds_settings[general_setup_shipping_methods][]" id="general_setup_shipping_methods_<?php echo esc_attr( $shipping_method_value ); ?>" value="<?php echo esc_attr( $shipping_method_value ); ?>" <?php checked( $checked ); ?>>
					</td>
					<td class="iconic-wds-table__column iconic-wds-table__column--label">
						<label for="general_setup_shipping_methods_<?php echo esc_attr( $shipping_method_value ); ?>"><?php echo esc_attr( $shipping_method_label ); ?></label>
					</td>
					<td class="iconic-wds-table__column iconic-wds-table__column--input">
						<?php if ( 'any' !== $shipping_method_value ) { ?>
							<select name="jckwds_settings[general_setup_shipping_method_labels][<?php echo esc_attr( $shipping_method_value ); ?>]">
								<option value="default" <?php selected( $label, 'default' ); ?>><?php esc_attr_e( 'Default', 'jckwds' ); ?></option>
								<option value="delivery" <?php selected( $label, 'delivery' ); ?>><?php esc_attr_e( 'Delivery', 'jckwds' ); ?></option>
								<option value="collection" <?php selected( $label, 'collection' ); ?>><?php esc_attr_e( 'Collection', 'jckwds' ); ?></option>
							</select>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get delivery days.
	 *
	 * @return array
	 */
	public static function get_delivery_days() {
		$delivery_days_setting = wpsf_get_setting( 'jckwds', 'datesettings_datesettings', 'days' );

		if ( empty( $delivery_days_setting ) ) {
			$delivery_days_setting = array( 0, 1, 2, 3, 4, 5, 6 );
		} else {
			$delivery_days_setting = array_map( 'absint', $delivery_days_setting );
		}

		return apply_filters( 'iconic_wds_delivery_days', $delivery_days_setting );
	}

	/**
	 * Get delivery days max orders.
	 *
	 * @param int $day_of_the_week The specific day of the week, ranging from 0 (Sunday) to 6 (Saturday).
	 *
	 * @return array|int|bool
	 */
	public static function get_delivery_days_max_orders( $day_of_the_week = false ) {
		$max_orders = wpsf_get_setting( 'jckwds', 'datesettings_datesettings', 'max_orders' );

		if ( empty( $max_orders ) ) {
			$max_orders = array_fill( 0, 7, true );
		}

		foreach ( $max_orders as $key => $max_order ) {
			$count              = is_numeric( $max_order ) ? absint( $max_order ) : true;
			$max_orders[ $key ] = $count <= 0 ? false : $count;
		}

		$max_orders = apply_filters( 'iconic_wds_delivery_days_max_orders', $max_orders, $day_of_the_week );

		if ( false !== $day_of_the_week ) {
			// Return setting for specific day, or false if none set.
			return isset( $max_orders[ $day_of_the_week ] ) ? $max_orders[ $day_of_the_week ] : false;
		}

		return $max_orders;
	}

	/**
	 * Get shipping methods.
	 *
	 * @return array
	 */
	public static function get_shipping_methods() {
		$shipping_methods = wpsf_get_setting( 'jckwds', 'general_setup', 'shipping_methods' );

		if ( empty( $shipping_methods ) ) {
			$shipping_methods = array( 'any' );
		}

		return apply_filters( 'iconic_wds_shipping_methods', $shipping_methods );
	}

	/**
	 * Get shipping method labels.
	 *
	 * @return array
	 */
	public static function get_shipping_method_labels() {
		$shipping_method_labels = wpsf_get_setting( 'jckwds', 'general_setup', 'shipping_method_labels' );

		if ( empty( $shipping_method_labels ) ) {
			$shipping_method_labels = array();
		}

		return apply_filters( 'iconic_wds_shipping_method_labels', $shipping_method_labels );
	}

	/**
	 * Admin: Validate Settings
	 *
	 * @param array $settings Un-validated settings
	 *
	 * @return array $validated_settings
	 */
	public static function validate_settings( $settings ) {
		// Validate shipping methods.

		if ( empty( $settings['general_setup_shipping_methods'] ) ) {
			$settings['general_setup_shipping_methods'] = array( 'any' );

			$message = __( 'You need to select at least one shipping method in General Settings. "Any Method" has been selected for you.', 'jckwds' );
			add_settings_error( 'general_setup_shipping_methods', esc_attr( 'jckwds-error' ), $message, 'error' );
		}

		// validate cutoff.

		if ( isset( $settings['timesettings_timesettings_cutoff'] ) ) {
			if ( $settings['timesettings_timesettings_cutoff'] < 0 || ! is_numeric( $settings['timesettings_timesettings_cutoff'] ) ) {
				$settings['timesettings_timesettings_cutoff'] = 0;

				$message = __( '"Allow Bookings Up To (x) Minutes Before Slot" should be a positive integer. It has defaulted to 0.', 'jckwds' );

				add_settings_error( 'timesettings_timesettings_cutoff', esc_attr( 'jckwds-error' ), $message, 'error' );
			}
		}

		// validate min selectable date.

		if ( isset( $settings['datesettings_datesettings_minimum'] ) ) {
			if ( $settings['datesettings_datesettings_minimum'] < 0 || ! is_numeric( $settings['datesettings_datesettings_minimum'] ) ) {
				$settings['datesettings_datesettings_minimum'] = 0;

				$message = __( 'Minimum selectable date should be a positive integer. It has defaulted to 0.', 'jckwds' );

				add_settings_error( 'datesettings_datesettings_minimum', esc_attr( 'jckwds-error' ), $message, 'error' );
			}
		}

		// validate max selectable date.

		if ( isset( $settings['datesettings_datesettings_maximum'] ) ) {
			if ( $settings['datesettings_datesettings_maximum'] < 0 || ! is_numeric( $settings['datesettings_datesettings_maximum'] ) ) {
				$settings['datesettings_datesettings_maximum'] = 14;

				$message = __( 'Maximum selectable date should be a positive integer. It has defaulted to 14.', 'jckwds' );

				add_settings_error( 'datesettings_datesettings_maximum', esc_attr( 'jckwds-error' ), $message, 'error' );
			}
		}

		// validate min/max selectable date.

		if ( isset( $settings['datesettings_datesettings_minimum'] ) && isset( $settings['datesettings_datesettings_maximum'] ) ) {
			if ( $settings['datesettings_datesettings_minimum'] > $settings['datesettings_datesettings_maximum'] ) {
				$settings['datesettings_datesettings_minimum'] = $settings['datesettings_datesettings_maximum'];

				$message = __( 'Minimum selectable date should be less than or equal to the maximum selectable date.', 'jckwds' );

				add_settings_error( 'datesettings_datesettings_maximum', esc_attr( 'jckwds-error' ), $message, 'error' );
			}
		}

		// validate same day cutoff.

		if ( isset( $settings['datesettings_datesettings_sameday_cutoff'] ) ) {
			if ( $settings['datesettings_datesettings_sameday_cutoff'] != "" && ! self::validate_time_format( $settings['datesettings_datesettings_sameday_cutoff'] ) ) {
				$settings['datesettings_datesettings_sameday_cutoff'] = "";

				$message = __( 'The Same Day cutoff should be a valid time format (00:00), try using the time picker instead.', 'jckwds' );

				add_settings_error( 'datesettings_datesettings_sameday_cutoff', esc_attr( 'jckwds-error' ), $message, 'error' );
			}
		}

		// validate next day cutoff.

		if ( isset( $settings['datesettings_datesettings_nextday_cutoff'] ) ) {
			if ( $settings['datesettings_datesettings_nextday_cutoff'] != "" && ! self::validate_time_format( $settings['datesettings_datesettings_nextday_cutoff'] ) ) {
				$settings['datesettings_datesettings_nextday_cutoff'] = "";

				$message = __( 'The Next Day cutoff should be a valid time format (00:00), try using the time picker instead.', 'jckwds' );

				add_settings_error( 'datesettings_datesettings_nextday_cutoff', esc_attr( 'jckwds-error' ), $message, 'error' );
			}
		}

		// validate timeslots.

		if ( $settings['timesettings_timesettings_setup_enable'] ) {
			if ( is_array( $settings['timesettings_timesettings_timeslots'] ) ) {
				$default_cutoff  = '';

				$cutoff_numeric         = true;
				$empty_shipping_methods = false;
				$empty_days             = false;
				$valid_time_format      = true;

				$i = 0;
				foreach ( $settings['timesettings_timesettings_timeslots'] as $timeslot ) {
					// validate shipping methods.

					if ( empty( $timeslot['shipping_methods'] ) ) {
						$settings['timesettings_timesettings_timeslots'][ $i ]['shipping_methods'] = array( 'any' );
						$empty_shipping_methods                                                    = true;
					}

					// validate cutoff.

					if ( isset( $timeslot['cutoff'] ) ) {
						if ( ! empty( $timeslot['cutoff'] ) && ( $timeslot['cutoff'] <= 0 || ! is_numeric( $timeslot['cutoff'] ) ) ) {
							$settings['timesettings_timesettings_timeslots'][ $i ]['cutoff'] = $default_cutoff;

							$cutoff_numeric = false;
						}
					}

					// validate days.

					if ( isset( $timeslot['days'] ) && $timeslot['days'] == "" ) {
						$settings['timesettings_timesettings_timeslots'][ $i ]['days'] = array( 0, 1, 2, 3, 4, 5, 6 );

						$empty_days = true;
					}

					// validate time formats.

					if ( isset( $timeslot['timefrom'] ) ) {
						$validated_time_format = self::validate_time_format( $timeslot['timefrom'] );

						if ( $validated_time_format == false ) {
							$settings['timesettings_timesettings_timeslots'][ $i ]['timefrom'] = '01:00';

							$valid_time_format = false;
						}
					}

					if ( isset( $timeslot['timeto'] ) ) {
						$validated_time_format = self::validate_time_format( $timeslot['timeto'] );

						if ( $validated_time_format == false ) {
							$settings['timesettings_timesettings_timeslots'][ $i ]['timeto'] = '23:00';

							$valid_time_format = false;
						}
					}

					$i ++;
				}

				// validate shipping methods.

				if ( $empty_shipping_methods ) {
					$message = __( 'Some of your time slots were not enabled for any shipping methods. "Any Method" has been selected for them.', 'jckwds' );
					add_settings_error( 'timesettings_timesettings_timeslots_shipping_methods', esc_attr( 'jckwds-error' ), $message, 'error' );
				}

				// validate cutoff.

				if ( ! $cutoff_numeric ) {
					$message = __( 'The "Allow Bookings Up To (x) Minutes Before Slot" time slot setting should be a positive integer. It has been removed.', 'jckwds' );
					add_settings_error( 'timesettings_timesettings_timeslots_cutoff', esc_attr( 'jckwds-error' ), $message, 'error' );
				}

				// validate days.

				if ( $empty_days ) {
					$message = __( 'You should select at least one active day for your time slot. All days have now been selected.', 'jckwds' );
					add_settings_error( 'timesettings_timesettings_timeslots_days', esc_attr( 'jckwds-error' ), $message, 'error' );
				}

				// validate time format.

				if ( ! $valid_time_format ) {
					$message = __( 'One of the time slots you entered had an invalid format. Try using the time picker instead. A default has been added in its place.', 'jckwds' );
					add_settings_error( 'timesettings_timesettings_timeslots_format', esc_attr( 'jckwds-error' ), $message, 'error' );
				}
			}
		}

		// clear transients.

		global $jckwds;

		delete_transient( $jckwds->timeslot_data_transient_name );

		return $settings;
	}

	/**
	 * Helper: Validate Time Format
	 *
	 * @param string $time
	 *
	 * @return bool
	 */
	public static function validate_time_format( $time ) {
		if ( false === $time || '' === $time ) {
			return false;
		}

		if ( preg_match( '/(2[0-3]|[01][0-9]):([0-5][0-9])/', $time ) == false ) {
			return false;
		}

		return true;
	}
}