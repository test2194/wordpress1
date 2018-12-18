<?php
	function easyreservations_calendar_shortcode($atts) {
		if(!in_the_loop() && did_action( 'wp_print_scripts' ) == 0) return '';
		wp_enqueue_script( 'easyreservations_send_calendar' );

		$atts = shortcode_atts(array(
			'resource' => 0,
			'date' => 0,
			'float' => 'full-width',
			'colors' => 'default empty',
			'past' => 1,
			'half' => 1,
			'price' => 0,
			'header' => 0,
			'req' => 0,
			'interval' => 1,
			'months' => 1,
			'monthes' => 1,
			'select' => 2,
			'notax' => 0,
			'id' => rand(1,99999)
		), $atts);

		if($atts['months'] === 1){
			$atts['months'] = $atts['monthes'];
		}

		if(isset($_POST['resource'])) $atts['resource'] = intval($_POST['resource']);

        if(file_exists(RESERVATIONS_URL . 'assets/css/custom/calendar.css')) wp_enqueue_style('easy-cal-custom', false, array(), false, 'all');
		else wp_enqueue_style('easy-calendar' , false, array(), false, 'all');

		$class = str_replace('easy-ui-container', '', RESERVATIONS_STYLE);
		$class .= ' float-'.sanitize_text_field($atts['float']);

		$colors = explode(' ', sanitize_text_field($atts['colors']));
		$class .= ' calendar-'.$colors[0];
		if($colors[1] == 'empty' || $colors[1] == 'both') $class .= ' calendar-empty-color';
		if($colors[1] == 'occupied' || $colors[1] == 'both') $class .= ' calendar-occupied-color';

		$return = '<form name="CalendarFormular" class="easy-calendar easy-box '.$class.'" id="CalendarFormular-'.$atts['id'].'">';
		$return .= '</form><!-- Provided by easyReservations free Wordpress Plugin http://www.easyreservations.org -->';

        $cal = 'new easyCalendar("'.wp_create_nonce( 'easy-calendar' ).'", '.json_encode($atts).', "shortcode");';
		if(!function_exists('wpseo_load_textdomain')) er_enqueue_js( 'if(window.easyCalendar) '.$cal.' else ' );
        er_enqueue_js( 'jQuery(window).ready(function(){'.$cal.'});' );

		return $return;
	}
?>