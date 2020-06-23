<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Iconic_WDS_Order class.
 */
class Iconic_WDS_Order {
	/**
	 * Run.
	 */
	public static function run() {
		self::add_filters();
		self::add_actions();
	}

	/**
	 * Add filters.
	 */
	public static function add_filters() {
		add_filter( 'manage_edit-shop_order_columns', array( __CLASS__, 'shop_order_columns' ) );
		add_filter( 'manage_edit-shop_order_sortable_columns', array( __CLASS__, 'sortable_shop_order_columns' ) );
		add_filter( 'request', array( __CLASS__, 'orderby_shop_order_columns' ) );

		if ( is_admin() ) {
			add_filter( 'request', array( __CLASS__, 'filter_orders_by_delivery_date' ), 200, 1 );
		}
	}

	/**
	 * Add actions.
	 */
	public static function add_actions() {
		add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'render_shop_order_columns' ), 2 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( __CLASS__, 'display_admin_order_meta' ) );
		add_action( 'deleted_post', array( __CLASS__, 'cancel_order' ), 10, 1 );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'status_changed' ), 10, 3 );
		add_action( 'woocommerce_process_shop_order_meta', array( __CLASS__, 'save_delivery_details' ), 10, 2 );

		if ( is_admin() ) {
			add_action( 'restrict_manage_posts', array( __CLASS__, 'add_delivery_date_filter' ), 20, 1 );
		}
	}

	/**
	 * Admin: Add Columns to orders tab
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public static function shop_order_columns( $columns ) {
		$columns['jckwds_delivery'] = __( 'Delivery', 'jckwds' );

		return $columns;
	}

	/**
	 * Admin: Output date and timeslot columns on orders tab
	 *
	 * @param string $column
	 */
	public static function render_shop_order_columns( $column ) {
		global $post, $woocommerce, $the_order, $jckwds;

		if ( empty( $the_order ) || self::get_id( $the_order ) != $post->ID ) {
			$the_order = wc_get_order( $post->ID );
		}

		switch ( $column ) {
			case 'jckwds_delivery' :

				$jckwds->display_date_and_timeslot( $the_order );

				break;
		}
	}

	/**
	 * Admin: Make delivery column sortable
	 */
	public static function sortable_shop_order_columns( $columns ) {
		$columns['jckwds_delivery'] = 'jckwds_delivery';

		return $columns;
	}

	/**
	 * Admin: Delivery columns orderby
	 */
	public static function orderby_shop_order_columns( $vars ) {
		if ( isset( $vars['orderby'] ) && 'jckwds_delivery' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' => 'jckwds_timestamp',
				'orderby'  => 'meta_value_num',
			) );
		}

		return $vars;
	}

	/**
	 * Admin: Display date and time slot on the admin order page
	 *
	 * @param WC_Order $order
	 */
	public static function display_admin_order_meta( $order ) {
		global $jckwds;

		$fields = $jckwds->get_checkout_fields_data( true );

		if ( empty( $fields ) ) {
			return;
		}

		$delivery_slot_data = self::get_delivery_slot_data( $order );

		if ( empty( $delivery_slot_data ) ) {
			return;
		}

		if ( isset( $fields['jckwds-delivery-date'] ) && isset( $delivery_slot_data['date'] ) ) {
			unset( $fields['jckwds-delivery-date']['field_args']['custom_attributes']['readonly'] );
			$fields['jckwds-delivery-date']['field_args']['custom_attributes']['maxlength'] = 10;

			$fields['jckwds-delivery-date']['value'] = $delivery_slot_data['date'];
		}

		if ( isset( $fields['jckwds-delivery-time'] ) && isset( $delivery_slot_data['time_slot'] ) ) {
			$fields['jckwds-delivery-time']['field_args']['options'] = array(
				'' => empty( $delivery_slot_data['time_slot'] ) ? __( 'No slot was selected.', 'jckwds' ) : $delivery_slot_data['time_slot'],
			);

			$fields['jckwds-delivery-time']['value'] = '';
		}

		if ( isset( $fields['jckwds-delivery-date-ymd'] ) && isset( $delivery_slot_data['ymd'] ) ) {
			$fields['jckwds-delivery-date-ymd']['value'] = $delivery_slot_data['ymd'];
		}

		$fields['jckwds-date-changed'] = array(
			'value'      => 0,
			'field_args' => array(
				'type' => 'hidden',
			),
		);
		?>
		<div class="iconic-wds-order-delivery-details">
			<?php foreach ( $fields as $field_name => $field_data ) {
				woocommerce_form_field( $field_name, $field_data['field_args'], $field_data['value'] );
			} ?>
		</div>
		<?php
	}

	/**
	 * Get shipping method ID for order.
	 *
	 * @param WC_Order $order
	 *
	 * @return bool|string
	 */
	public static function get_shipping_method_id( $order ) {
		$shipping_methods = $order->get_shipping_methods();

		if ( empty( $shipping_methods ) ) {
			return false;
		}

		$shipping_method = array_pop( $shipping_methods );
		$instance_id     = $shipping_method->get_instance_id();

		$method = WC_Shipping_Zones::get_shipping_method( $instance_id );

		if ( empty( $method ) ) {
			return false;
		}

		$rate_id = $method->get_rate_id();

		if ( empty( $rate_id ) ) {
			return false;
		}

		return $rate_id;
	}

	/**
	 * Get delivery slot data.
	 *
	 * @param WC_Order $order
	 *
	 * @return array|bool
	 */
	public static function get_delivery_slot_data( $order ) {
		$order_id           = self::get_id( $order );
		$delivery_slot_data = apply_filters( 'iconic_wds_delivery_slot_data', array(
			'date'      => get_post_meta( $order_id, 'jckwds_date', true ),
			'time_slot' => get_post_meta( $order_id, 'jckwds_timeslot', true ),
			'timestamp' => get_post_meta( $order_id, 'jckwds_timestamp', true ),
			'ymd'       => get_post_meta( $order_id, 'jckwds_date_ymd', true ),
		), $order );

		$delivery_slot_data_filtered = array_filter( $delivery_slot_data );

		if ( empty( $delivery_slot_data_filtered ) ) {
			return false;
		}

		return $delivery_slot_data;
	}

	/**
	 * Cancel order
	 *
	 * If an order is cancelled, delete the time slot, too
	 *
	 * @param int $order_id
	 */
	public static function cancel_order( $order_id ) {
		global $jckwds;

		$post_type = get_post_type( $order_id );

		if ( $post_type !== "shop_order" ) {
			return;
		}

		global $wpdb;

		$delete = $wpdb->delete( $jckwds->reservations_db_table_name, array(
			'order_id' => $order_id,
		), array(
			'%d',
		) );

		if ( ! $delete ) {
			return;
		}

		delete_post_meta( $order_id, $jckwds->date_meta_key );
		delete_post_meta( $order_id, $jckwds->timeslot_meta_key );
		delete_post_meta( $order_id, $jckwds->timestamp_meta_key );
	}

	/**
	 * @param int    $order_id
	 * @param string $from
	 * @param string $to
	 */
	public static function status_changed( $order_id, $from, $to ) {
		if ( $to !== 'cancelled' ) {
			return;
		}

		self::cancel_order( $order_id );
	}

	/**
	 * Get ID
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public static function get_id( $order ) {
		return method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
	}

	/**
	 * Get billing first name
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public static function get_billing_first_name( $order ) {
		return method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : $order->billing_first_name;
	}

	/**
	 * Get billing last name
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public static function get_billing_last_name( $order ) {
		return method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : $order->billing_last_name;
	}

	/**
	 * Get billing email
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public static function get_billing_email( $order ) {
		return method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;
	}

	/**
	 * Save delivery details when editing an order in admin.
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public static function save_delivery_details( $post_id, $post ) {
		self::update_order_meta( $post_id );
	}

	/**
	 * Get shipping address link.
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public static function get_shipping_address_link_html( $order ) {
		$address = preg_replace( '#<br\s*/?>#i', ', ', $order->get_formatted_shipping_address() );
		$map_url = $order->get_shipping_address_map_url();

		return sprintf( '<a target="_blank" href="%s">%s</a>', esc_url( $map_url ), $address );
	}

	/**
	 * Get edit order link html.
	 *
	 * @param int $order_id
	 *
	 * @return string
	 */
	public static function get_edit_order_link_html( $order_id ) {
		$edit_url = admin_url( 'post.php?post=' . $order_id . '&action=edit' );

		return sprintf( '<a href="%s" target="_blank">#%d</a>', $edit_url, $order_id );
	}

	/**
	 * Get billing full name.
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public static function get_billing_full_name( $order ) {
		$billing_first_name = Iconic_WDS_Order::get_billing_first_name( $order );
		$billing_last_name  = Iconic_WDS_Order::get_billing_last_name( $order );

		return sprintf( '%s %s', $billing_first_name, $billing_last_name );
	}

	/**
	 * Get billing email link html.
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public static function get_billing_email_link_html( $order ) {
		$billing_email = Iconic_WDS_Order::get_billing_email( $order );

		return Iconic_WDS_Helpers::get_email_link_html( $billing_email );
	}

	/**
	 * Get status badge.
	 *
	 * @param string $status
	 *
	 * @return string
	 */
	public static function get_status_badge( $status ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			$status_label = ucwords( $status );

			return sprintf( '<mark class="%s tips" data-tip="%s">%s</mark>', esc_attr( $status ), esc_attr( $status_label ), $status_label );
		} else {
			return sprintf( '<mark class="order-status status-%s"><span>%s</span></mark>', esc_attr( $status ), ucwords( $status ) );
		}
	}

	/**
	 * Get order items.
	 *
	 * @param WC_Order $order
	 *
	 * @return bool|string
	 */
	public static function get_order_items( $order ) {
		$items = $order->get_items();

		if ( empty( $items ) ) {
			return false;
		}

		$items_array = array();

		foreach ( $items as $item ) {
			$quantity      = isset( $item['quantity'] ) ? $item['quantity'] : $item['qty'];
			$items_array[] = sprintf( '%s (x%d)', $item['name'], $quantity );
		}

		return implode( ', ', $items_array );
	}

	/**
	 * Add delivery dates filter to orders screen.
	 */
	public static function add_delivery_date_filter( $screen ) {
		if ( 'shop_order' !== $screen ) {
			return;
		}

		$selected_date = filter_input( INPUT_GET, 'delivery_date', FILTER_SANITIZE_STRING );
		$today         = current_time( 'timestamp', 1 );
		$today_ymd     = date( 'Ymd', $today );
		$tomorrow      = strtotime( date( 'Y-m-d', $today ) . '+1 day' );
		$tomorrow_ymd  = date( 'Ymd', $tomorrow );
		$options       = array(
			$today_ymd    => __( 'Today', 'jckwds' ),
			$tomorrow_ymd => __( 'Tomorrow', 'jckwds' ),
		);
		$months        = self::get_months_with_deliveries();
		$options       = $options + $months; ?>
		<select name="delivery_date" class="wc-enhanced-select">
			<option selected="selected" value=""><?php esc_attr_e( 'All delivery dates', 'jckwds' ); ?></option>
			<?php foreach ( $options as $date => $label ) { ?>
				<option value="<?php echo esc_attr( $date ); ?>" <?php selected( $selected_date, $date ); ?>><?php echo esc_attr( $label ); ?></option>
			<?php } ?>
		</select>
		<?php
	}

	/**
	 * Get list of months with deliveries in them.
	 *
	 * @return array
	 */
	public static function get_months_with_deliveries() {
		global $wpdb, $jckwds;

		static $months = array();

		if ( ! empty( $months ) ) {
			return $months;
		}

		$result = $wpdb->get_results(
			"SELECT DATE_FORMAT(date, '%Y%m') as month, UNIX_TIMESTAMP(date) as timestamp 
			FROM $jckwds->reservations_db_table_name
			WHERE processed = 1
			AND date >= CURRENT_DATE()
			GROUP BY month
			ORDER BY month ASC"
		);

		if ( empty( $result ) ) {
			return $months;
		}

		foreach ( $result as $month ) {
			$months[ $month->month ] = date( 'F Y', $month->timestamp );
		}

		return $months;
	}

	/**
	 * Filter orders by delivery date.
	 *
	 * @param $query_vars
	 *
	 * @return mixed
	 */
	public static function filter_orders_by_delivery_date( $query_vars ) {
		global $typenow;

		if ( 'shop_order' !== $typenow ) {
			return $query_vars;
		}

		$date = filter_input( INPUT_GET, 'delivery_date', FILTER_SANITIZE_STRING );

		if ( ! $date ) {
			return $query_vars;
		}

		$query_vars['meta_query']   = isset( $query_vars['meta_query'] ) ? $query_vars['meta_query'] : array();
		$query_vars['meta_query'][] = array(
			'key'     => 'jckwds_date_ymd',
			'value'   => '^' . $date,
			'compare' => 'REGEXP',
		);
		$query_vars['meta_key']     = 'jckwds_date_ymd';

		return $query_vars;
	}

	/**
	 * Helper: Update order meta on successful checkout submission
	 *
	 * @param int $order_id
	 */
	public static function update_order_meta( $order_id ) {
		global $jckwds;

		$timeslot = false;

		$posted_date     = filter_input( INPUT_POST, 'jckwds-delivery-date', FILTER_SANITIZE_STRING );
		$posted_date_ymd = filter_input( INPUT_POST, 'jckwds-delivery-date-ymd', FILTER_SANITIZE_STRING );
		$posted_time     = filter_input( INPUT_POST, 'jckwds-delivery-time', FILTER_SANITIZE_STRING );
		$date_changed    = (int) filter_input( INPUT_POST, 'jckwds-date-changed', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $posted_date ) || empty( $posted_date_ymd ) ) {
			return;
		}

		if ( is_admin() && ! $date_changed ) {
			return;
		}

		$is_same_day = $jckwds->get_same_day_date( 'Ymd' ) === $posted_date_ymd;
		$is_next_day = $jckwds->get_next_day_date( 'Ymd' ) === $posted_date_ymd;

		update_post_meta( $order_id, '_iconic_wds_is_same_day', $is_same_day );
		update_post_meta( $order_id, '_iconic_wds_is_next_day', $is_next_day );
		update_post_meta( $order_id, $jckwds->date_meta_key, esc_attr( htmlspecialchars( $posted_date ) ) );
		update_post_meta( $order_id, $jckwds->date_meta_key . '_ymd', esc_attr( htmlspecialchars( $posted_date_ymd ) ) );

		if ( ! empty( $posted_time ) ) {
			$timeslot_id = $jckwds->extract_timeslot_id_from_option_value( $posted_time );

			// Add time data to the order.
			if ( false !== $timeslot_id ) {
				$timeslot = $jckwds->get_timeslot_data( $timeslot_id );
				update_post_meta( $order_id, $jckwds->timeslot_meta_key, esc_attr( htmlspecialchars( $timeslot['formatted'] ) ) );
			} else {
				delete_post_meta( $order_id, $jckwds->timeslot_meta_key );
			}
		}

		if ( ! empty( $posted_date_ymd ) ) {
			$slot_id = $timeslot ? sprintf( '%s_%s', $posted_date_ymd, $timeslot['id'] ) : $posted_date_ymd;

			if ( $jckwds->has_reservation() && ! is_admin() ) {
				$jckwds->update_reservation( $slot_id, $order_id );
			} else {
				$data = array(
					'date'      => $jckwds->convert_date_for_database( $posted_date_ymd ),
					'order_id'  => $order_id,
					'processed' => 1,
				);

				if ( $timeslot ) {
					$data['datetimeid'] = $slot_id;
					$data['starttime']  = $timeslot['timefrom']['stripped'];
					$data['endtime']    = $timeslot['timeto']['stripped'];
					$data['asap']       = ! empty( $timeslot['asap'] );
				}

				$jckwds->add_reservation( $data );
			}

			$jckwds->add_timestamp_order_meta( $posted_date_ymd, $timeslot, $order_id );
		}

		if ( is_admin() ) {
			$order = wc_get_order( $order_id );
			$order->add_order_note( __( 'Delivery date updated.', 'jckwds' ), false );
		}
	}
}