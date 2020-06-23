<div class="jckwds-reserve-wrap">
	<table class="jckwds-reserve">
		<thead>
		<tr>
			<th class="alwaysVis">
				<a href="#" class="jckwds-prevday"><i class="jckwds-icn-left"></i></a>
				<a href="#" class="jckwds-nextday"><i class="jckwds-icn-right"></i></a>
			</th>
			<?php if ( ! empty( $reservation_table_data['headers'] ) ) { ?>
				<?php $i = 0;
				foreach ( $reservation_table_data['headers'] as $header_data ) { ?>

					<th <?php echo $header_data['classes']; ?>><?php echo $header_data['cell']; ?></th>

					<?php $i ++;
				} ?>
			<?php } ?>
		</tr>
		</thead>
		<tbody>
		<?php if ( ! empty( $reservation_table_data['body'] ) ) { ?>
			<?php $i = 0;
			foreach ( $reservation_table_data['body'] as $rows ) { ?>
				<tr>
				<?php foreach ( $rows as $row ) { ?>
					<<?php echo $row['cell_type']; ?>
					<?php echo $row['classes']; ?>
					<?php echo $row['attributes']; ?>>
					<?php echo $row['cell']; ?>
					</<?php echo $row['cell_type']; ?>>
				<?php } ?>
				</tr>
				<?php $i ++;
			} ?>
		<?php } ?>
		</tbody>
	</table>
</div>