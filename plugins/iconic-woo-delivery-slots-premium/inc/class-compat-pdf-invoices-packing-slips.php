<?php
/**
 * WDS Ajax class.
 *
 * @package Iconic_WDS
 */

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;


defined( 'ABSPATH' ) || exit;

/**
 * Compatiblity with WooCommerce PDF Invoices & Packing Slips
 * https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/
 *
 * @class    Iconic_WDS_Compat_PDF_Invoices_Packaging_Slips
 */
class Iconic_WDS_Compat_Pdf_Invoices_Packing_Slips {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'plugins_loaded', array( __CLASS__, 'hooks' ) );
	}

	/**
	 * Hooks.
	 */
	public static function hooks() {
		if ( ! class_exists( 'WPO_WCPDF' ) ) {
			return;
		}

		add_action( 'iconic_wds_admin_deliveries_table_heading', array( __CLASS__, 'add_heading' ) );
		add_action( 'iconic_wds_admin_deliveries_table_body_cell', array( __CLASS__, 'add_column' ) );
		add_action( 'admin_footer', array( __CLASS__, 'add_css' ) );
	}

	/**
	 * Add table heading.
	 *
	 * @return void
	 */
	public static function add_heading() {
		?>
		<td><?php echo esc_html__( 'PDF Actions', 'jckwds' ); ?></td>
		<?php
	}

	/**
	 * Add buttons in the body.
	 *
	 * @param object $reservation Reservation Object.
	 *
	 * @return void
	 */
	public static function add_column( $reservation ) {
		$post             = get_post( $reservation->order_id );
		$post_id          = $reservation->order_id;
		$meta_box_actions = array();
		$documents        = WPO_WCPDF()->documents->get_documents();
		$order            = WCX::get_order( $post->ID );

		if ( empty( $documents ) ) {
			return;
		}

		foreach ( $documents as $document ) {
			$document_title = $document->get_title();
			$document       = wcpdf_get_document( $document->get_type(), $order );

			if ( empty( $document ) ) {
				continue;
			}

			$document_title                            = method_exists( $document, 'get_title' ) ? $document->get_title() : $document_title;
			$meta_box_actions[ $document->get_type() ] = array(
				'url'    => wp_nonce_url( admin_url( "admin-ajax.php?action=generate_wpo_wcpdf&document_type={$document->get_type()}&order_ids=" . $post_id ), 'generate_wpo_wcpdf' ),
				'alt'    => esc_attr( 'PDF ' . $document_title ),
				'title'  => 'PDF ' . $document_title,
				'exists' => method_exists( $document, 'exists' ) ? $document->exists() : false,
			);
		}

		$meta_box_actions = apply_filters( 'wpo_wcpdf_meta_box_actions', $meta_box_actions, $post_id );

		if ( empty( $meta_box_actions ) ) {
			return;
		}
		?>
		<td>
			<ul class="wpo_wcpdf-actions">
				<?php
				foreach ( $meta_box_actions as $document_type => $data ) {
					$exists = ( isset( $data['exists'] ) && $data['exists'] ) ? 'exists' : '';
					printf( '<li><a href="%1$s" class="button %4$s" target="_blank" alt="%2$s">%3$s</a></li>', esc_attr( $data['url'] ), esc_attr( $data['alt'] ), esc_attr( $data['title'] ), esc_attr( $exists ) );
				}
				?>
			</ul>
		</td>
		<?php
	}

	/**
	 * Add CSS.
	 */
	public static function add_css() {
		global $pagenow;

		if ( 'admin.php' !== $pagenow || 'jckwds-deliveries' !== filter_input( INPUT_GET, 'page' ) ) {
			return;
		}
		?>
		<style>
		.wpo_wcpdf-actions a.button.exists::after {
			font-family: Dashicons;
			content: "\f147";
			font-size: 16px;
			margin-left: 4px;
			color: #2aad2a;
			vertical-align: middle;
		}
		</style>
		<?php
	}
}
