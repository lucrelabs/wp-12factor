<?php

/**
 * Output a listing of all reservations, ordered by date.
 */

$tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'upcoming-deliveries';
?>

<div class="wrap">

	<h2 class="nav-tab-wrapper" style="margin-bottom: 20px;">
		<a href="<?php echo admin_url( 'admin.php?page=jckwds-deliveries' ); ?>" class="nav-tab <?php echo $tab == 'upcoming-deliveries' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Upcoming Deliveries', 'jckwds' ); ?></a>
		<a href="<?php echo admin_url( 'admin.php?page=jckwds-deliveries&tab=currently-reserved' ); ?>" class="nav-tab <?php echo $tab == 'currently-reserved' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Currently Reserved', 'jckwds' ); ?></a>
	</h2>

	<?php if ( $tab == 'upcoming-deliveries' ) {
		$upcoming_deliveries = Iconic_WDS_Reservations::get_reservations( 1 );
		$this->reservations_layout( $upcoming_deliveries );
	} ?>

	<?php if ( $tab == 'currently-reserved' ) {
		$upcoming_reservations = Iconic_WDS_Reservations::get_reservations( 0 );
		$this->reservations_layout( $upcoming_reservations );
	} ?>

</div>