<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WDS_Helpers.
 *
 * @class    Iconic_WDS_Helpers
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WDS_Helpers {
	/**
	 * Run.
	 */
	public static function run() {
		self::add_filters();
	}

	/**
	 * Add filters.
	 */
	public static function add_filters() {
		add_filter( 'woocommerce_form_field_hidden', array( __CLASS__, 'form_field_hidden' ), 10, 4 );
	}

	/**
	 * Output hidden form field.
	 *
	 * @param string $field
	 * @param        $key
	 * @param        $args
	 * @param        $value
	 *
	 * @return string
	 */
	public static function form_field_hidden( $field, $key, $args, $value ) {
		$field .= '<input type="hidden" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" />';

		return $field;
	}

	/**
	 * Get time formatted.
	 *
	 * @param string $time Hi formatted time
	 *
	 * @return string
	 */
	public static function get_time_formatted( $time ) {
		global $jckwds;

		$time_format = $jckwds->settings['timesettings_timesettings_setup_timeformat'];
		$time        = DateTime::createFromFormat( 'Hi', str_pad( $time, 4, "0", STR_PAD_LEFT ), wp_timezone() );

		return $time->format( $time_format );;
	}

	/**
	 * Get date formatted.
	 *
	 * @param $date Y-m-d H:i:s
	 *
	 * @return string
	 */
	public static function get_date_formatted( $date ) {
		$date_format = get_option( 'date_format' );
		$date        = new DateTime( $date );

		return $date->format( $date_format );
	}

	/**
	 * Get email link HTML.
	 *
	 * @param string $email
	 *
	 * @return string
	 */
	public static function get_email_link_html( $email ) {
		return sprintf( '<a href="mailto:%s" target="_blank">%s</a>', esc_url( $email ), $email );
	}

	/**
	 * Get user's name.
	 *
	 * @param WP_User $user
	 *
	 * @return string
	 */
	public static function get_user_name( $user ) {
		$full_name = implode( ' ', array(
			$user->first_name,
			$user->last_name,
		) );

		return ! empty( $full_name ) ? $full_name : $user->user_login;
	}

	/**
	 * Labels by type.
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public static function get_labels_by_type( $order = null ) {
		$label_type = self::get_label_type( $order );

		$labels_by_type = apply_filters( 'iconic_wds_labels_by_type', array(
			'delivery'   => array(
				'details'           => apply_filters( 'iconic_wds_delivery_details_text', __( 'Delivery Details', 'jckwds' ), $order ),
				'date'              => apply_filters( 'iconic_wds_delivery_date_text', __( 'Delivery Date', 'jckwds' ), $order ),
				'select_date'       => apply_filters( 'iconic_wds_select_delivery_date_text', __( 'Select a delivery date', 'jckwds' ), $order ),
				'choose_date'       => apply_filters( 'iconic_wds_choose_delivery_date_text', __( 'Please choose a date for your delivery.', 'jckwds' ), $order ),
				'select_date_first' => apply_filters( 'iconic_wds_select_date_first_text', __( 'Please select a date first...', 'jckwds' ) ),
				'time_slot'         => apply_filters( 'iconic_wds_time_slot_text', __( 'Time Slot', 'jckwds' ), $order ),
				'choose_time_slot'  => apply_filters( 'iconic_wds_choose_time_slot_text', __( 'Please choose a time slot for your delivery.', 'jckwds' ), $order ),
				'select_time_slot'  => apply_filters( 'iconic_wds_select_time_slot_text', __( 'Please select a time slot...', 'jckwds' ), $order ),
				'no_time_slots'     => apply_filters( 'iconic_wds_no_slots_available_text', __( 'Sorry, no slots available...', 'jckwds' ) ),
			),
			'collection' => array(
				'details'           => __( 'Collection Details', 'jckwds' ),
				'date'              => __( 'Collection Date', 'jckwds' ),
				'select_date'       => __( 'Select a collection date', 'jckwds' ),
				'choose_date'       => __( 'Please choose a date for your collection.', 'jckwds' ),
				'select_date_first' => __( 'Please select a date first...', 'jckwds' ),
				'time_slot'         => __( 'Time Slot', 'jckwds' ),
				'choose_time_slot'  => __( 'Please choose a time slot for your collection.', 'jckwds' ),
				'select_time_slot'  => __( 'Please select a time slot...', 'jckwds' ),
				'no_time_slots'     => __( 'Sorry, no slots available...', 'jckwds' ),
			),
		), $label_type, $order );

		return isset( $labels_by_type[ $label_type ] ) ? $labels_by_type[ $label_type ] : $labels_by_type['delivery'];
	}

	/**
	 * Get label.
	 *
	 * @param string|bool $type
	 * @param null        $order
	 *
	 * @return bool|string|array
	 */
	public static function get_label( $type = false, $order = null ) {
		$labels_by_type = self::get_labels_by_type( $order );

		// Keep individual strings filtered for
		// backwards compatibility.
		$labels = apply_filters( 'iconic_wds_labels', $labels_by_type, $order );

		if ( ! $type ) {
			return $labels;
		}

		if ( empty( $labels[ $type ] ) ) {
			return false;
		}

		return $labels[ $type ];
	}

	/**
	 * Get label type.
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public static function get_label_type( $order = null ) {
		global $jckwds;

		if ( $order ) {
			$shipping_method = Iconic_WDS_Order::get_shipping_method_id( $order );
		} else {
			$shipping_method = $jckwds->get_chosen_shipping_method();
		}

		$label_type             = $jckwds->settings['general_setup_labels'];
		$override_label_type    = false;
		$shipping_method_labels = Iconic_WDS_Settings::get_shipping_method_labels();

		if ( ! empty( $shipping_method_labels[ $shipping_method ] ) ) {
			$override_label_type = $shipping_method_labels[ $shipping_method ];
		}

		$label_type = 'default' === $override_label_type ? $label_type : $override_label_type;

		return apply_filters( 'iconic_wds_get_label_type', $label_type, $order );
	}

	/**
	 * Try and get order ID if present.
	 *
	 * @return int|null
	 */
	public static function get_order_id() {
		global $order;

		if ( is_a( $order, 'WC_Order' ) ) {
			return $order->get_id();
		}

		$order_id = absint( filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT ) );

		if ( $order_id ) {
			return $order_id;
		}

		return null;
	}

	/**
	 * Search a multidimensional array by key/value.
	 *
	 * @param mixed $key   The key to find.
	 * @param mixed $value The value to match.
	 * @param array $array A multidimensional array.
	 *
	 * @return bool|mixed
	 */
	public static function search_array_by_key_value( $key, $value, $array ) {
		if ( empty( $array ) || ! is_array( $array ) ) {
			return false;
		}

		foreach ( $array as $array_key => $array_value ) {
			if ( (string) $array_value[ $key ] === (string) $value ) {
				return $array[ $array_key ];
			}
		}

		return false;
	}
}
