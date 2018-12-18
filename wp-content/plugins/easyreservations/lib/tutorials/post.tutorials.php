<?php
	function easyreservations_post_tutorial() {
		$handler = array('#content_easyReservations');
		$content = array(
				'<h3>Shortcodes</h3><p>This function will help you to add the shortcodes for the form, calendar, search, hourly calendar and user control panel to your posts or pages. If you want to change or add new styles for them copy the .css file to <b>/easyreservations/assets/css/custom/</b> and name it like the howto.txt describes.</p>'
		);
		$at = array('top');
		$execute = array('tinyMCE.activeEditor.execCommand(\'mceOpenReservation\');');
		echo easyreservations_execute_pointer(1, $handler, $content, $at, $execute);
	}
?>