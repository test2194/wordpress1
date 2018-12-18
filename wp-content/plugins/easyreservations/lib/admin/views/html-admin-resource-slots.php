<?php
if( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>
<script>var slots = new Array();</script>
<table class="<?php echo RESERVATIONS_STYLE; ?> table" style="width: 100%">
	<thead>
	<tr>
		<th><?php _e( 'Name', 'easyReservations' ); ?></th>
		<th style=""><?php _e( 'Active', 'easyReservations' ); ?></th>
		<th style="text-align: center"><?php _e( 'Duration', 'easyReservations' ); ?></th>
		<th style="text-align: center"><?php _e( 'Repeat', 'easyReservations' ); ?></th>
		<th style="text-align: right"><?php _e( 'Price', 'easyReservations' ); ?></th>
		<th></th>
	</tr>
	</thead>
	<tbody id="sortable">
	<?php if( $slots && ! empty( $slots ) ): ?>
		<?php foreach( $slots as $key => $slot ): ?>
			<?php
			$slot['range-from'] = date( RESERVATIONS_DATE_FORMAT, $slot['range-from'] );
			$slot['range-to']   = date( RESERVATIONS_DATE_FORMAT, $slot['range-to'] );
			?>
			<script>
				slots[<?php echo $key; ?>] = new Object();
				slots[<?php echo $key; ?>] = <?php echo json_encode( $slot ); ?>;
			</script>

			<tr>
				<td><?php echo $slot['name']; ?></td>
				<td class="<?php if( current_time( 'timestamp' ) >= $slot['range-from'] && current_time( 'timestamp' ) <= $slot['range-to'] ) echo '#5EE06B'; else echo '#F26868'; ?>">
					<code><?php echo $slot['range-from']; ?></code> -
					<code><?php echo $slot['range-to']; ?></code>
				</td>
				<td style="text-align: center">
					<?php echo human_time_diff( $slot['from'] * 60, $slot['duration'] * 86400 + $slot['to'] * 60 ); ?><br>
				</td>
				<td style="text-align: center">
					<?php echo isset( $slot['repeat'] ) ? $slot['repeat'] : 0; ?><br>
				</td>
				<td style="text-align: right">
					<?php echo er_format_money( $slot['base-price'], 1 ); ?><br>
					<?php echo er_format_money( $slot['children-price'], 1 ); ?>
				</td>
				<td style="text-align: right">
					<a class="fa fa-pencil slot-edit easy-tooltip" data-slot="<?php echo $key; ?>"
					   title="<?php echo sprintf(__('Edit %s', 'easyReservations'), __('slot', 'easyReservations')); ?>"></a>
					<a class="fa fa-copy slot-copy easy-tooltip" data-slot="<?php echo $key; ?>"
					   title="<?php echo sprintf( __( 'Copy %s', 'easyReservations' ), __( 'slot', 'easyReservations' ) ); ?>"></a>
					<a
						href="<?php echo wp_nonce_url( 'admin.php?page=reservation-resources&resource=' . $resource->ID . '&delete_slot=' . $key, 'easy-resource-delete-slot' ); ?>#slots"
						class="fa fa-trash easy-tooltip" title="<?php echo sprintf(__('Delete %s', 'easyReservations'), __('slot', 'easyReservations')); ?>"></a>
				</td>
			</tr>
		<?php endforeach; ?>
	<?php else: ?>
		<tr>
			<td colspan="5">
				<?php _e( 'No slots defined', 'easyReservations' ); ?>
			</td>
		</tr>
	<?php endif; ?>
	</tbody>
</table>