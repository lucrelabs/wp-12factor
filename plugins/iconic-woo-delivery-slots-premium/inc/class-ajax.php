<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WDS Ajax class.
 */
class Iconic_WDS_Ajax {
	/**
	 * Init
	 */
	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		// iconic_wds_{event} => nopriv
		$ajax_events = array(
			'get_chosen_shipping_method'  => true,
			'reserve_slot'                => true,
			'remove_reserved_slot'        => true,
			'get_slots_on_date'           => true,
			'get_upcoming_bookable_dates' => true,
			'is_delivery_slots_allowed'   => true,
			'get_reserved_slot'           => true,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_iconic_wds_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_iconic_wds_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Get chosen shipping method
	 */
	public static function get_chosen_shipping_method() {
		$data = array(
			'chosen_method' => jckWooDeliverySlots::get_chosen_shipping_method(),
		);

		wp_send_json( $data );
	}

	/**
	 * Reserve a slot
	 */
	public static function reserve_slot() {
		global $jckwds;

		$slot_id         = filter_input( INPUT_POST, 'slot_id' );
		$slot_date       = filter_input( INPUT_POST, 'slot_date' );
		$slot_start_time = filter_input( INPUT_POST, 'slot_start_time' );
		$slot_end_time   = filter_input( INPUT_POST, 'slot_end_time' );

		// Check if slot is still available before reserving it.
		$slot_id_exploded = explode( '_', $slot_id );
		$timeslot         = $jckwds->get_timeslot_data( $slot_id_exploded[1] );
		$slots_available  = $jckwds->get_slots_available_count( array( $timeslot ), $slot_date );
		$is_available     = ! empty( $slots_available[ $slot_id_exploded[1] ] );

		if ( ! $is_available ) {
			wp_send_json( array( 'success' => false ) );
		}

		$jckwds->add_reservation(
			array(
				'datetimeid' => $slot_id,
				'date'       => $slot_date,
				'starttime'  => $slot_start_time,
				'endtime'    => $slot_end_time,
				'asap'       => strpos( $slot_id, 'asap' ) !== false,
			)
		);

		wp_send_json( array( 'success' => true ) );
	}

	/**
	 * Remove a reserved slot
	 */
	public static function remove_reserved_slot() {
		global $wpdb, $jckwds;

		$wpdb->delete(
			$jckwds->reservations_db_table_name,
			array(
				'processed' => 0,
				'user_id'   => $jckwds->user_id,
			),
			array(
				'%d',
				'%s',
			)
		);

		wp_send_json( array( 'success' => true ) );
	}

	/**
	 * Get available timeslots on posted date
	 *
	 * Date format is always Ymd to cater for multiple languages. This
	 * is set when a date is selected via the datepicker script
	 */
	public static function get_slots_on_date() {
		global $jckwds;

		$response = array( 'success' => false, 'reservation' => false );

		if ( empty( $_POST['date'] ) ) {
			wp_send_json( $response );
		}

		$timeslots = $jckwds->slots_available_on_date( $_POST['date'] );

		if ( $timeslots ) {
			$response['success'] = true;

			$response['html'] = '';

			$available_slots = array();

			foreach ( $timeslots as $timeslot ) {
				$response['html'] .= '<option value="' . esc_attr( $timeslot['value'] ) . '">' . $timeslot['formatted_with_fee'] . '</option>';
			}

			$response['slots'] = $timeslots;
		}

		if ( $reservation = $jckwds->has_reservation() ) {
			$slot_id_exploded = explode( '_', $reservation->datetimeid );
			$timeslot_id      = $slot_id_exploded[1];
			$timeslot         = $jckwds->get_timeslot_data( $timeslot_id );

			$response['reservation'] = $timeslot['value'];
		}

		wp_send_json( $response );
	}

	/**
	 * Get upcoming bookable dates
	 */
	public static function get_upcoming_bookable_dates() {
		global $jckwds;

		$response = array(
			'success'        => true,
			'bookable_dates' => $jckwds->get_upcoming_bookable_dates( $jckwds->date_format() ),
		);

		wp_send_json( $response );
	}

	/**
	 * Is delivery slots allowed.
	 */
	public static function is_delivery_slots_allowed() {
		global $jckwds;

		$allowed = Iconic_WDS_Checkout::is_delivery_slots_allowed();
		$data    = array(
			'index' => absint( filter_input( INPUT_POST, 'index', FILTER_SANITIZE_NUMBER_INT ) ),
		);

		if ( $allowed ) {
			$data['bookable_dates'] = $jckwds->get_upcoming_bookable_dates( $jckwds->date_format() );

			wp_send_json_success( $data );
		}

		wp_send_json_error( $data );
	}

	/**
	 * Get the reserved slot for Reservation table shortcode.
	 */
	public static function get_reserved_slot() {
		global $jckwds;

		$reserved = $jckwds->get_reserved_slot();

		if ( $reserved ) {
			wp_send_json_success( $reserved );
		}

		wp_send_json_error();
	}
}