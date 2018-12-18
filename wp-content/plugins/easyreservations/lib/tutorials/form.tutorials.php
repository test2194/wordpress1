<?php
	function easyreservations_form_tutorial() {
		$handler = array('#wpbody a.active','input[name="formname"]', 'formtag:first', '.ui-accordion-header:first');
		$content = array(
				'<h3>Forms</h3><p>With forms you get the reservation information from you guests. You can add them to a page or post with the button with the red e in the visual editor. They get get generated by the templates you can define below.</p>',
				'<h3>Templates</h3><p>You can add unlimited form templates. This is very helpful to make forms only for a resource or an offer. HTML is supported in forms.<p>',
				'<h3>Fields</h3><p>The form elements are represented by [tags] in the form editor. You can click on these tags to edit their settings.</p>',
				'<h3>Add fields</h3><p>All available fields are listed in this tables, divided into three groups. Just click on one to add it to the form.</p>',
		);
		$at = array('top', 'top', 'top', 'top');

		echo easyreservations_execute_pointer(4, $handler, $content, $at);
	}
?>