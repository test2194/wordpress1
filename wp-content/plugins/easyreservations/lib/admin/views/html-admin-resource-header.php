<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wpdb;
$forms = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT option_name FROM ".$wpdb->prefix ."options WHERE option_name like %s ",
		$wpdb->esc_like("reservations_form") . '%'
	)
);

foreach($forms as $form_option) {
	$form = get_option($form_option->option_name);

	$new_custom_fields = array('title' => 4, 'tert' => 6);
	foreach($new_custom_fields as $key => $value){
		$form = preg_replace('/(custom|customp) .*{,10} '.$key.'/', 'custom id="'.$value.'"', $form);
	}
	update_option($form_option->option_name, $form);
}

?>
<div class="resource-header">
	<div class="resource-thumbnail">
		<?php if($thumbnail) echo $thumbnail; else echo '<a class="thumbnail-placeholder" href="post.php?post='.$resource->ID.'&action=edit"></a>' ?><br>
	</div>
	<div class="main">
		<h1><?php echo $resource->post_title; ?></h1>
		<div class="content"><?php echo strip_shortcodes(__($resource->post_content)); ?></div>
		<a href="post.php?post=<?php echo $resource->ID; ?>&action=edit"><?php _e('Post view', 'easyReservations'); ?></a> |
		<a href="admin.php?page=reservation-resources&add_resource=resource&dopy=<?php echo $resource->ID; ?>"><?php _e('Copy', 'easyReservations'); ?></a> |
		<a href="#" onclick="if(confirm('<?php echo addslashes(__('Really delete this resource and all its reservations?', 'easyReservations')); ?>')) window.location = '<?php echo wp_nonce_url('admin.php?page=reservation-resources&delete='.($resource->ID), 'easy-resource-delete'); ?>';"><?php echo ucfirst(__('delete', 'easyReservations')); ?></a>
	</div>
	<?php ER()->messages()->output(); ?>
</div>
<ul class="easy-ui easy-ui-container easy-navigation resource-navigation">
	<li><a href="#" target="settings"><?php _e('Settings', 'easyReservations'); ?></a></li>
	<li><a href="#" target="filters"><?php _e('Filter', 'easyReservations'); ?></a></li>
	<li><a href="#" target="slots"><?php _e('Slots', 'easyReservations'); ?></a></li>
	<?php do_action('easy_resource_navigation'); ?>
</ul>
<script>
	jQuery(document).ready(function($) {
		$('.resource-navigation').easyNavigation({value: 'settings', hash: true});
		easyUiTooltip();
	});
</script>