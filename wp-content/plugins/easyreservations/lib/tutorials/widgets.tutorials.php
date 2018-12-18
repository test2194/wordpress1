<?php
	function easyreservations_widgets_tutorial() {
		$handler = array('div[id*="easyreservations_form_widget"] input[name*="title"]', 'div[id*="easyreservations_form_widget"] input[name*="form_url"]');
		$content = array(
			'<h3>Widget</h3><p>The widget is a pre-form that fills the content of a form or search form. It can show the calendar and a form.</p>',
			'<h3>URL</h3><p>Enter the URL to a page or post with a form or a search form in it. The data gathered in the widget gets inserted.</p>',
		);
		$at = array('top', 'top' );
		$position = array('', '' );

		echo easyreservations_execute_pointer(2, $handler, $content, $at, false, $position);
	}

	function easyreservations_widget_open_event(){
		$return = <<<EOF
 <script type="text/javascript">
		var countopeneasywidget = 0;
		jQuery('div[id*="easyreservations_form_widget"] > div.widget-top, div[id*="easyreservations_form_widget"] a.widget-action').live('click', function(){
			if(countopeneasywidget == 0 && typeof easypointer0 === "function") setTimeout('easypointer0();', 200);
			countopeneasywidget++;
		});
</script>
EOF;
		echo $return;
	}

	add_action( 'admin_print_footer_scripts', 'easyreservations_widget_open_event', 20 );