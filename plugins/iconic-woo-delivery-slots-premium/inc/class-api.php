<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WDS API Interface class.
 */
class Iconic_WDS_API {
	/**
	 * Init
	 */
	public static function init() {
		add_filter( 'woocommerce_rest_prepare_shop_order_object', array( __CLASS__, 'prepare_shop_order' ), 10, 3 );
		add_filter( 'woocommerce_api_order_response', array( __CLASS__, 'prepare_legacy_shop_order' ), 10, 4 );
	}

	/**
	 * Prepare shop order API response.
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WC_Data          $object   Object data.
	 * @param WP_REST_Request  $request  Request object.
	 *
	 * @return WP_REST_Response
	 */
	public static function prepare_shop_order( $response, $object, $request ) {
		if ( empty( $response->data ) ) {
			return $response;
		}

		$order_id = $object->get_id();

		$response->data['iconic_delivery_meta'] = array(
			'date'      => get_post_meta( $order_id, 'jckwds_date', true ),
			'timeslot'  => get_post_meta( $order_id, 'jckwds_timeslot', true ),
			'timestamp' => get_post_meta( $order_id, 'jckwds_timestamp', true ),
		);

		return $response;
	}

	/**
	 * Add delivery date meta to legacy API.
	 *
	 * @param $order_data
	 * @param $order
	 * @param $fields
	 * @param $server
	 *
	 * @return mixed
	 */
	public static function prepare_legacy_shop_order( $order_data, $order, $fields, $server ) {
		$order_data['iconic_delivery_meta'] = Iconic_WDS_Order::get_delivery_slot_data( $order );

		return $order_data;
	}
}