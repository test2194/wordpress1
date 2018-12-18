<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="admin.php?page=reservation-resources&resource=<?php echo $resource->ID; ?>"  id="set_spaces_names" name="set_spaces_names">
	<table class="<?php echo RESERVATIONS_STYLE; ?> table" style="margin-top:10px;width: 100%">
		<thead>
			<tr>
				<th colspan="2"><?php _e('Resource spaces names', 'easyReservations');?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2"><input type="submit" onclick="document.getElementById('set_spaces_names').submit(); return false;" class="easy-button" value="<?php _e('Submit', 'easyReservations');?>"></td>
			</tr>
		</tfoot>
		<tbody>
			<?php for($i=0; $i < $resource->quantity; $i++){
				if( isset($spaces_names[$i]) && !empty($spaces_names[$i])) $name = $spaces_names[$i];
				else $name = $i+1;
				$class = $i%2==0 ? '' : 'alternate'; ?>
				<tr class="<?php echo $class; ?>">
					<td> #<?php echo $i+1; ?></td>
					<td style="text-align:right;width:70%"><input type="text" name="resource_spaces[]" value="<?php echo $name; ?>" style="width:99%"></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</form>

