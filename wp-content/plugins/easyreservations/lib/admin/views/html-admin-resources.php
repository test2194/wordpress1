<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wpdb;
$res = new ER_Reservation(false, array('dontclean', 'interval' => 86400));
$res->arrival = current_time( 'timestamp' );
$res->departure= $res->arrival + 86400;

?>

<table class="<?php echo RESERVATIONS_STYLE; ?> table" style="width:99%;">
	<thead>
		<tr>
			<th></th>
			<th nowrap><?php _e('Title', 'easyReservations');?></th>
			<th nowrap><?php _e('ID', 'easyReservations');?></th>
			<th style="text-align:center;" nowrap><?php _e('Quantity', 'easyReservations'); ?></th>
			<th style="text-align:right" nowrap><?php _e('Base price', 'easyReservations'); ?></th>
			<th nowrap><?php _e('Reservations', 'easyReservations'); ?></th>
			<th nowrap><?php _e('Status', 'easyReservations'); ?></th>
			<th nowrap><?php _e('Excerpt', 'easyReservations'); ?></th>
			<th nowrap></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach (ER()->resources()->get_accessible() as $resource): ?>
		<?php
			$all_reservations = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}reservations WHERE approve='yes' AND resource=%d", $resource->ID
				)
			);
			$res->set_resource($resource);
			$check = $res->checkAvailability(3);

			if($check >= $resource->quantity) $status = __('Full', 'easyReservations');
			else $status = __('Available', 'easyReservations');

			$status .= ' ('.(!$check ? 0 : $check).')';
		?>
		<tr>
			<td style="text-align:left; vertical-align:middle;max-width:25px;width:25px;">
				<a href="post.php?post=<?php echo $resource->ID; ?>&action=edit">
					<?php echo get_the_post_thumbnail($resource->ID, array(25,25)); ?>
				</a>
			</td>
			<td>
				<a class="resource-link" href="admin.php?page=reservation-resources&resource=<?php echo $resource->ID;?>">
					<?php echo '<b>'.stripslashes(__($resource->post_title)).'</b>'; ?>
				</a>
			</td>
			<td style="text-align:center;font-weight: bold">
				<?php echo $resource->ID; ?>
			</td>
			<td style="text-align:center;">
				<?php echo $resource->quantity; ?>
			</td>
			<td style="text-align:right;width:100px" nowrap><?php echo er_format_money($resource->base_price, 1);?></td>
			<td style="text-align:center;width:85px" nowrap><?php echo $all_reservations; ?></td>
			<td nowrap><?php echo $status; ?></td>
			<td><?php echo strip_tags(substr($resource->post_content, 0, 36)); ?></td>
			<td style="text-align:right;width:100px">
				<a href="post.php?post=<?php echo $resource->ID; ?>&action=edit" title="<?php echo sprintf(__('Edit %s', 'easyReservations'), __('post', 'easyReservations')); ?>" style="font-size:16px" class="fa fa-file-text easy-tooltip"></a>
				<a href="admin.php?page=reservation-resources&resource=<?php echo $resource->ID;?>" title="<?php echo sprintf(__('Edit %s', 'easyReservations'), __('resource', 'easyReservations')); ?>" style="font-size:16px" class="fa fa-pencil easy-tooltip"></a>
				<a href="admin.php?page=reservation-resources&add_resource=resource&dopy=<?php echo $resource->ID;?>" target="_blank" title="Copy resource settings" style="font-size:16px" class="fa fa-files-o easy-tooltip"></a>
				<a href="#" onclick="if(confirm('<?php echo addslashes(__('Really delete this resource and all its reservations?', 'easyReservations')); ?>')) window.location = '<?php echo wp_nonce_url('admin.php?page=reservation-resources&delete='.($resource->ID), 'easy-resource-delete'); ?>';" title="<?php _e('delete', 'easyReservations'); ?>" style="font-size:16px" class="fa fa-trash easy-tooltip"></a>
			</td>
		</tr>
	<?php endforeach; ?>
</tbody>
</table>