<?php
require('../../../../../../wp-load.php');
global $wpdb;
if ( !is_user_logged_in() || !current_user_can('edit_posts') ){
	wp_die("You are not allowed to access this file.");
}
$resource_options     = er_form_resources_options('', false, false, false, true);
$color_scheme_options = easyreservations_get_color_schemes_options('default empty');
?><html xmlns="http://www.w3.org/1999/xhtml" style="background:#fff">
	<head>
	<title>easyReservations <?php _e("Shortcode Creator", "easyReservations"); ?></title>
	<script language="javascript" type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/jquery.js'></script>
	<script language="javascript" type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-admin/js/common.js'></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/hoverIntent.js'></script>
	<script type='text/javascript'>
		/* <![CDATA[ */
		var commonL10n = {"warnDelete":"You are about to permanently delete the selected items.\n  'Cancel' to stop, 'OK' to delete."};
		/* ]]> */
	</script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/jquery.color.min.js'></script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/ui/widget.min.js'></script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/ui/position.min.js'></script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/wp-pointer.min.js'></script>
	<script type='text/javascript'>
		/* <![CDATA[ */
		var thickboxL10n = {"next":"Next >","prev":"< Prev","image":"Image","of":"of","close":"Close","noiframes":"This feature requires inline frames. You have iframes disabled or your browser does not support them.","loadingAnimation":"<?php echo addslashes(get_option('siteurl')); ?>\/wp-includes\/js\/thickbox\/loadingAnimation.gif","closeImage":"http:\/\/127.0.0.1\/er\/wp-includes\/js\/thickbox\/tb-close.png"};
		/* ]]> */
	</script>
	<style>
		input[tcype="text"], select {
			padding:3px;
			background-color: #FFFFFF;
			font-family: Arial,"Bitstream Vera Sans",Helvetica,Verdana,sans-serif !important;
			font-size: 13px !important;
			border-color: #DFDFDF;
			border-radius: 3px 3px 3px 3px;
			border-style: solid;
			border-width: 1px;
		}

		.easyreservations_tiny_popUp {
			font-family: Arial,"Bitstream Vera Sans",Helvetica,Verdana,sans-serif !important;
			font-size: 13px !important;
			line-height: 30px;
		}

		.easyreservations_tiny_popUp td {
			font-family: Arial,"Bitstream Vera Sans",Helvetica,Verdana,sans-serif !important;
			font-size: 13px !important;
			color: #666666 !important;
		}

		.easyreservations_tiny_popUp input[type=text],
		.easyreservations_tiny_popUp textarea {
			min-width:150px;
			padding:3px;
			font-size:13px
		}

		td > label {
			font-weight:bold;
			color: #333333;
		}
	</style>
	<base target="_self" />
	</head>
	<body id="link" onload=";document.body.style.display='';" style="display: none;background:#fff">
		<form name="easyreservations_tiny_popUp" action="#">
			<table border="0" cellpadding="0" cellspacing="0"  class="easyreservations_tiny_popUp" style="width:99%;">
				<tbody>
					<tr>
						<td nowrap="nowrap" style="border-bottom:1px solid #ececec;padding-bottom:4px;width:30%"></td>
						<td  style="border-bottom:1px solid #ececec;padding-bottom:4px;width:70%">
							<select id="type_select" name="type_select" style="width: 100px" onChange="jumpto(this.value)">
								<option value="choose"><?php _e("Select", "easyReservations"); ?> shortcode</option>
								<option value="form"><?php _e("Form", "easyReservations"); ?></option>
								<option value="calendar"><?php _e("Calendar", "easyReservations"); ?></option>
								<?php do_action('easy-tinymce-add-name'); ?>
							 </select> <?php _e("Type of shortcode", "easyReservations"); ?>
						</td>
					</tr>
				</tbody>
				<tbody id="tiny_Field">
					<tr><td colspan="2"><div style="float: left"><?php _e("The shortcodes wont work if more then one of the same type are on the same site", "easyReservations"); ?>. <?php _e("This can happen with posts in category-views or on the homepage", "easyReservations"); ?>.<br><?php _e("To prevent this add the shortcodes after the [more] tag", "easyReservations"); ?>.<br></div></td></tr>
				</tbody>
			</table>
			<div class="mceActionPanel" style="vertical-align:bottom;">
				<div style="float: left">
					<input type="submit" id="insert" name="insert" value="<?php _e("Insert", "easyReservations"); ?>" onclick="insertEasyShortcode();" />
				</div>
				<div style="float: right">
					<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", "easyReservations"); ?>" onclick="tinyMCEPopup.close();" />
				</div>
			</div>
		</form>
	</body>
</html>
<script type='text/javascript'>
function jumpto(x){ // Chained inputs;
	var click = 0;
	var form_options = '<?php echo er_form_template_options(); ?>';
	var color_sheme_select = '<select id="color_scheme"><?php echo $color_scheme_options; ?></select>';
	var float_select = '<select id="box-float"><option value="full-width"><?php echo addslashes(__("Full width", "easyReservations")); ?></option><option value="half-30">30%</option><option value="half-40">40%</option><option value="half-50">50%</option><option value="half-60">60%</option><option value="half-70">70%</option><option value="left"><?php echo addslashes(__("Float left", "easyReservations")); ?></option><option value="right"><?php echo addslashes(__("Float right", "easyReservations")); ?></option></select>';
	if(x == "form"){
		var FieldAdd = '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="form_chooser"><?php echo addslashes(__("Form", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select id="form_chooser">'+form_options+'</select> <?php echo addslashes(__("Select form template", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="resource"><?php echo addslashes(__("Resource", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select id="resource"><?php echo $resource_options; ?></select> <?php echo addslashes(__("Attached to reservations if no resource [tag] in form", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="inline"><?php echo addslashes(__("Style", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select name="inline" id="inline"><option value="boxed"><?php echo addslashes(__("Boxed", "easyReservations")); ?></option><option value="inline"><?php echo addslashes(__("Inline", "easyReservations")); ?></option></select></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="multiple_check"><?php echo addslashes(__("Multiple reservations", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><input type="checkbox" id="multiple_check" checked> <?php echo addslashes(__("Allow multiple reservations in a", "easyReservations")); ?> <select id="multiple_style"><option value="full"><?php echo addslashes(__("Full", "easyReservations")); ?></option><option value="popup"><?php echo addslashes(__("Popup", "easyReservations")); ?></option></select> <?php echo addslashes(__("Overlay", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td colspan="2"><u><b><?php echo addslashes(__("Overlay", "easyReservations")); ?></b></u><br><?php echo addslashes(__("If multiple reservations is enabled the overlay will show the list of the reservations with this message.", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="form_val_message"><?php echo addslashes(__("Overlay message", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><input type="text" id="form_val_message" style="width: 250px;" value="Reservation successfully verified"></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="form_val_submessage"><?php echo addslashes(__("Overlay sub-message", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><textarea id="form_val_submessage" style="width: 250px;">Either make additional reservations or submit</textarea></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="form_res_name"><?php echo addslashes(__("Label for resources", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><input type="text" id="form_res_name" name="form_res_name" style="13px" value="Resource"></td>';
			FieldAdd += '</tr>';
			FieldAdd += '</tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="show_pers"><?php echo addslashes(__("Persons", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><label><input type="checkbox"  id="show_pers" checked></label> <?php echo addslashes(__("Display person count in reservation list", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td colspan="2"><u><b><?php echo addslashes(__("Submit", "easyReservations")); ?></b></u><br><?php echo addslashes(__("After the guest submits their reservation(s) this message, the price and if available the payment and credit cards form will be shown.", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="form_submit_message"><?php echo addslashes(__("Submit message", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><input type="text" id="form_submit_message" style="width: 250px;padding:3px;font-size:13px" value="We got your reservation!"></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="form_submit_submessage"><?php echo addslashes(__("Submit sub-message", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><textarea id="form_submit_submessage" style="width: 250px;padding:3px;font-size:13px">Please continue by paying through PayPal or enter your credit card details.</textarea></td>';
			FieldAdd += '</tr>';
			FieldAdd += '</tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="show_price"><?php _e("Show price", "easyReservations"); ?>: </label></td>';
			FieldAdd += '<td><label><input type="checkbox"  id="show_price" checked></label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td colspan="2"><u><b><?php echo addslashes(__("Credit card", "easyReservations")); ?> (Payment Module is required)</b></u><br><?php echo addslashes(__("Message to be displayed after the credit card got entered.", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="form_credit_message"><?php echo addslashes(__("Credit card message", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><input type="text" id="form_credit_message" style="width: 250px;padding:3px;font-size:13px" value="Reservation complete"></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="form_credit_submessage"><?php echo addslashes(__("Credit card sub-message", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><textarea id="form_credit_submessage" style="width: 250px;padding:3px;font-size:13px">You\'ll receive an email with the reservations details</textarea></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr><td colspan="2"><b><?php echo addslashes(__("Only add one form per page or post", "easyReservations")); ?>.</b></td></tr>';
		document.getElementById("tiny_Field").innerHTML = FieldAdd;
	} else if(x == "calendar"){
		var FieldAdd = '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="calendar_resource"><?php echo addslashes(__("Resource", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select id="calendar_resource"><?php echo $resource_options; ?></select> <?php echo addslashes(sprintf(__('Select %s', 'easyReservations'), __('default resource', 'easyReservations'))); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="box-float"><?php echo addslashes(__("Width", "easyReservations")); ?></label></td>';
			FieldAdd += '<td>'+float_select+'</td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="color_scheme"><?php echo addslashes(__("Color scheme", "easyReservations")); ?></label></td>';
			FieldAdd += '<td>'+color_sheme_select+'</td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="show_price"><?php echo addslashes(__("Price", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select id="show_price"><option value="0"><?php echo addslashes(__("None", "easyReservations")); ?></option><option value="1">150&<?php echo RESERVATIONS_CURRENCY; ?>;</option><option value="2">150</option><option value="3"><?php echo er_format_money(150,1); ?></option><option value="4"><?php echo er_format_money(150); ?></option><option value="5">&<?php echo RESERVATIONS_CURRENCY; ?>;150</option><option value="avail"><?php echo addslashes(__("Display available spaces", "easyReservations")); ?></option></select> <?php echo addslashes(__("Show price in calendar", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="calendar_select"><?php echo addslashes(__("Selection", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select id="calendar_select"><option value="0"><?php echo addslashes(__("No selection", "easyReservations")); ?></option><option value="1"><?php echo addslashes(__("One click for arrival date", "easyReservations")); ?></option><option value="2" selected="selected"><?php echo addslashes(__("First click for arrival date; second for departure date", "easyReservations")); ?></select><br><i><?php echo addslashes(__("Select how the calendar can be clicked", "easyReservations")); ?></i></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="calendar_checkreq"><?php echo addslashes(__("Arrival and departure", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><input type="checkbox" id="calendar_checkreq"> <?php echo addslashes(__("Check resources default possible arrival and departure days so they cant be selected", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="calendar_past"><?php echo addslashes(__("Past", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><input type="checkbox" id="calendar_past"> <?php echo addslashes(__("Display past days availability", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="calendar_half"><?php echo addslashes(__("Arrival and departure", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select id="calendar_half"><option value="1"><?php echo addslashes(__("Display arrival and departure as half available dates", "easyReservations")); ?></option><option value="0"><?php echo addslashes(__("Display arrival days as free", "easyReservations")); ?></option><option value="2"><?php echo addslashes(__("Display arrival days as full", "easyReservations")); ?></option></select></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<?php do_action('easy-tinymce-cal',1); ?>';
			FieldAdd += '<tr><td colspan="2"><?php echo addslashes(__("This shortcode adds an availability calendar to the post or page", "easyReservations")); ?>. <?php echo addslashes(__("You can combine it with a formm the user control panel or the search form by adding it to the same page", "easyReservations")); ?>.<br><b><?php echo addslashes(__("Only add the calendar once per page or post", "easyReservations")); ?>.</b></td></tr>';
		document.getElementById("tiny_Field").innerHTML = FieldAdd;
	} else if(x == "choose"){
		document.getElementById("tiny_Field").innerHTML = '<tr><td colspan="2"><?php echo addslashes(__("The shortcodes wont work if more then one of the same type are on the same site", "easyReservations")); ?>. <?php echo addslashes(__("This can happen with posts in category-views or on homepage", "easyReservations")); ?>.<br><?php echo addslashes(__("To prevent this add the shortcodes after the [more] tag", "easyReservations")); ?>.<br></td></tr>';
	}  <?php do_action('easy-tinymce-add', $resource_options); ?>
}

function insertEasyShortcode() {
	var tagtext = '[easy_';
	var y = document.easyreservations_tiny_popUp.type_select.options[document.easyreservations_tiny_popUp.type_select.options.selectedIndex].value;

	var classAttribs = document.getElementById('type_select').value;

	if(y == "form"){
		classAttribs += ' '+document.getElementById('form_chooser').value + ' submit="' + document.getElementById('form_submit_message').value + '" subsubmit="' + document.getElementById('form_submit_submessage').value + '" credit="' + document.getElementById('form_credit_message').value + '" subcredit="' + document.getElementById('form_credit_submessage').value + '"';
		if(document.getElementById('form_res_name').value != '') classAttribs += ' resourcename="'+document.getElementById('form_res_name').value+'"';
		if(document.getElementById('show_price').checked == true) classAttribs += ' price="1"';
		if(document.getElementById('show_pers').checked == true) classAttribs += ' pers="1"';
		if(document.getElementById('inline').value == "inline") classAttribs += ' inline="1"';
		if(document.getElementById('resource')) classAttribs += ' resource="'+document.getElementById('resource').value+'"';
		if(document.getElementById('multiple_check').checked == true) classAttribs += ' multiple="'+document.getElementById('multiple_style').value+'" validate="'+document.getElementById('form_val_message').value+'" subvalidate="'+document.getElementById('form_val_submessage').value+'"';
	} else if(y == "calendar"){
		classAttribs += ' resource="' + document.getElementById('calendar_resource').value + '"';
		classAttribs += ' float="' +jQuery('#box-float').val()+ '"';
		classAttribs += ' half="' +jQuery('#calendar_half').val()+ '"';
		classAttribs += ' colors="' +jQuery('#color_scheme').val()+ '"';

		if(document.getElementById('calendar_past').checked !== true) classAttribs += ' past="0"';
		if(document.getElementById('calendar_checkreq').checked == true) classAttribs += ' req="1"';
		if(document.getElementById('show_price').value != "") classAttribs += ' price="' + document.getElementById('show_price').value + '"';
		var months_field = document.getElementById('calendar_monthesx');
		if(months_field){
			classAttribs += ' months="' + months_field.value + 'x' + document.getElementById('calendar_monthesy').value + '"';
		}
		var intervalfield = document.getElementById('calendar_interval');
		if(intervalfield) classAttribs += ' interval="' + intervalfield.value + '"';
		var headerfield = document.getElementById('calendar_header');
		if(headerfield && headerfield.checked == true) classAttribs += ' header="1"';

		classAttribs += ' select="'+document.getElementById('calendar_select').value+'"';
	} <?php do_action('easy-tinymce-save'); ?>
	if(y != "choose") tinyMCEPopup.editor.execCommand('mceInsertContent', false, tagtext+classAttribs+']');

	tinyMCEPopup.close();
}

var userSettings = {
		'url': '<?php echo SITECOOKIEPATH; ?>',
		'uid': '<?php if ( ! isset($current_user) ) $current_user = wp_get_current_user(); echo $current_user->ID; ?>',
		'time':'<?php echo current_time( 'timestamp' ) ?>'
	},
	ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>',
	isRtl = <?php echo (int) is_rtl(); ?>;
</script>

<?php
$settings = get_option( "reservations_settings" );

if(!isset( $settings['tutorial']) || $settings['tutorial'] == 1){
	require_once(RESERVATIONS_ABSPATH."lib/tutorials/handle.tutorials.php");
	easyreservations_load_pointer('tinymce');
}
do_action('admin_print_footer_scripts'); ?>