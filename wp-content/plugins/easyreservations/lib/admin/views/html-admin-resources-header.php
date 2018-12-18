<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(function_exists('icl_object_id')){
	$link = 'post-new.php?post_type=easy-rooms';
} else{
	$link = 'admin.php?page=reservation-resources&add_resource=resource';
}
?>
<h2 style="display: inline-block">
	<?php _e('Resources', 'easyReservations');?>
	<a class="badge secondary" id="add-new-h2" href="<?php echo $link; ?>"><?php _e('Add', 'easyReservations');?></a>
	<a class="badge secondary" id="post-view" href="<?php echo admin_url('edit.php?post_type=easy-rooms'); ?>">
		<?php _e('Post view', 'easyReservations');?>
	</a>
</h2>
<?php ER()->messages()->output(); ?>