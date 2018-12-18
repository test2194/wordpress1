<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(isset($_GET['dopy'])) $title = sprintf(__('Copy %s', 'easyReservations'), __('resource', 'easyReservations')).' '.intval($_GET['dopy']);
else $title = sprintf(__('Add %s', 'easyReservations'), __('resource', 'easyReservations'));

?>
&lt;&#100;iv class=&quot;up&#100;at&#101;d&quot; style=&quot;wi&#100;th:97%&quot;&gt;&lt;p&gt;Th&#105;s &#112;l&#117;gi&#110; &#105;s f&#111;r &lt;a hr&#101;&#102;=&quot;htt&#112;://w&#111;rd&#112;re&#115;s.&#111;rg/&#101;xt&#101;nd/plugins/&#101;asyr&#101;serv&#97;ti&#111;ns/&quot;&gt;&#102;r&#101;e&lt;/a&gt;&#33; Pl&#101;a&#115;e c&#111;n&#115;id&#101;r <&#97; t&#97;rg&#101;t="_bl&#97;nk" hre&#102;="h&#116;tps:&#47;/w&#119;w.&#112;ay&#112;&#97;l.c&#111;m/cg&#105;-b&#105;n/w&#101;b&#115;cr?c&#109;d=_&#115;-xclick&amp;h&#111;st&#101;d_bu&#116;&#116;&#111;n_i&#100;=&#68;3NW9T&#68;VHB&#74;&#57;E">d&#111;na&#116;ing</&#97;>.&lt;/p&gt;&lt;/&#100;iv&gt;
<form method="post" action="" name="add_resource" id="add_resource">
	<?php wp_nonce_field('easy-resource-add','easy-resource-add'); ?>
	<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:440px;">
		<thead>
		<tr>
			<th colspan="2"><?php echo $title; ?></th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="2">
				<?php if(isset($_GET['dopy'])) echo '<input type="hidden" name="dopy" value="'.$_GET['dopy'].'">'; ?>
				<input
					type="button"
					onclick="document.getElementById('add_resource').submit(); return false;"
					style="margin-top:4px;"
					class="easy-button"
					value="<?php _e('Submit', 'easyReservations'); ?>">
			</td>
		</tr>
		</tfoot>
		<tbody>
		<tr class="alternate">
			<td colspan="2"><i><?php _e('You can change this later on in the post view', 'easyReservations'); ?></i></td>
		</tr>
		<tr>
			<td class="label"><?php _e('Title', 'easyReservations'); ?></td>
			<td><input type="text" size="32" name="add_resource_title"></td>
		</tr>
		<tr class="alternate">
			<td class="label"><?php _e('Content', 'easyReservations'); ?></td>
			<td><textarea name="add_resource_content" rows="5" cols="23" style="min-height: 50px"></textarea></td>
		</tr>
		<tr>
			<td class="label"><?php _e('Image', 'easyReservations'); ?></td>
			<td>
				<label for="upload_image">
					<input id="upload_image" type="text" size="32" name="upload_image" value="" />
					<a id="upload_image_button"><img src="<?php echo admin_url().'images/media-button-image.gif'; ?>"></a>
				</label>
			</td>
		</tr>
		</tbody>
	</table>
</form>
<script>
	jQuery(document).ready(function($) {
		$('#upload_image_button').click(function() {
			tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
			return false;
		});
		window.send_to_editor = function(html) {
			$('#upload_image').val($('img',html).attr('src'));
			tb_remove();
		}
	});
</script>