<?php
/**
 * Foo_Widget Class
 */
class easyReservations_form_widget extends WP_Widget {
	/** constructor */
	function __construct() {
		parent::__construct(
		/* Base ID */'easyReservations_form_widget',
		/* Name */'easyReservations Widget',
			array( 'description' => 'easyReservations form and calendar widget', 'classname' =>  'easy-widget '.str_replace('easy-ui-container', '', RESERVATIONS_STYLE)) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		global $post;

		$resources_array = ER()->resources()->get();

		wp_enqueue_style('datestyle');
		wp_enqueue_style('easy-form-little', false, array(), false, 'all');
		wp_enqueue_script('jquery-ui-datepicker');
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$calendar = esc_attr( $instance[ 'calendar' ] );
		$calendar_half = isset($instance[ 'calendar_half' ]) ? esc_attr( $instance[ 'calendar_half' ] ) : 0;
		$calendar_past = isset($instance[ 'calendar_past' ]) ? esc_attr( $instance[ 'calendar_past' ] ) : 0;
		$calendar_colors = isset($instance[ 'calendar_colors' ]) ? esc_attr( $instance[ 'calendar_colors' ] ) : 'default empty';
		$calendar_price = esc_attr( $instance[ 'calendar_price' ] );
		$calendar_width = esc_attr( $instance[ 'calendar_width' ] );
		$calendar_room = esc_attr( $instance[ 'calendar_room' ] );
		if(array_key_exists($post->ID, $resources_array)) $calendar_room = $post->ID;
		$form_url = esc_attr( $instance[ 'form_url' ] );
		$form_button = esc_attr( $instance[ 'form_button' ] );
		$form_template = esc_attr( $instance[ 'form_template' ] );
		$calendar_width = (float) $calendar_width;
		if($calendar_width > 100) $calendar_width = 100;
		if($calendar_price == "on") $showPrice = 1;
		else $showPrice = 0;

		if(isset($before_widget)) echo $before_widget;
		if($title && !empty($title) && isset($before_title) && isset($after_title)) echo $before_title.$title.$after_title;
		if($calendar == "on"){
			$array = array('width' => $calendar_width, 'price' => $showPrice, 'header' => 0, 'req' => 0,'half' => 0,'past' => 0, 'interval' => 1, 'months' => 1, 'select' => 2, 'resource' => $calendar_room, 'id' => rand(1,99999), 'date' => 0);
			if($calendar_half == 1) $array['half'] = 1;
			if($calendar_past == 1) $array['past'] = 1;
			$class = str_replace('easy-ui-container', '', RESERVATIONS_STYLE);

			$colors = explode(' ', $calendar_colors);
			$class .= ' calendar-'.$colors[0];
			if($colors[1] == 'empty' || $colors[1] == 'both') $class .= ' calendar-empty-color';
			if($colors[1] == 'occupied' || $colors[1] == 'both') $class .= ' calendar-occupied-color';

			wp_enqueue_script( 'easyreservations_send_calendar' );
			wp_enqueue_style('easy-calendar');
			?><form name="widget_formular" id="CalendarFormular-<?php echo $array['id']; ?>" class="easy-calendar calendar-widget <?php echo $class; ?>"></form><?php
			$cal = 'new easyCalendar("'.wp_create_nonce( 'easy-calendar' ).'", '.json_encode($array).', "widget");';
			if(!function_exists('wpseo_load_textdomain')) er_enqueue_js( 'if(window.easyCalendar) '.$cal.' else ' );
			er_enqueue_js( 'jQuery(window).ready(function(){'.$cal.'});' );
		}
		if(!empty($form_template) && $form_template !== 'none'){
			$form_template = str_replace('reservations_form_', '', $form_template);
			$form_editor = apply_filters( 'easy_widget_content', get_option('reservations_form_'.$form_template));
			$form_content = stripslashes($form_editor);
			$fields = er_form_template_parser($form_content, true);

			foreach($fields as $field){
                $tags = shortcode_parse_atts( $field );

				$form_field = apply_filters('easyreservations_widget_form_field', er_form_generate_field($field, 'easy-widget-', '1', $calendar_room, '', ''), $tags);
				$form_content = str_replace('['.$field.']', $form_field, $form_content);
			}
		}

		if(isset($form_content)){
			add_action('wp_print_footer_scripts', 'easyreservatons_call_datepickers');
			wp_enqueue_script( 'easy-ui' );

			if(isset($form_url) && !empty($form_url)){
				if($form_url == 'res' || $form_url == 'resource'){
					$array = array();
					foreach($resources_array as $resource){
						$array[$resource->ID] = get_permalink($resource->ID);
						if($resource->ID == $calendar_room) $form_url = get_permalink($calendar_room);
					}
					er_enqueue_js( 'var easyResourcePermalinkArray = '.json_encode($array).'; var easyWidgetResField = jQuery(\'#easy_widget_form select[id$="form_resource"]\'); easyWidgetResField.bind(\'change\', function(){jQuery(\'form[name=easy_widget_form]\').attr(\'action\', easyResourcePermalinkArray[easyWidgetResField.val()]);});' );
				} ?>
				<form method="post" action="<?php echo esc_url(__($form_url)); ?>" class="easy-ui" name="easy_widget_form" id="easy_widget_form">
					<?php echo htmlspecialchars_decode($form_content); ?>
					<input type="submit" class="easy-button" value="<?php echo $form_button; ?>">
				</form><?php
			} else {
				echo htmlspecialchars_decode($form_content);
			}
		}
		if(isset($after_widget)) echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['calendar'] = strip_tags($new_instance['calendar']);
		$instance['calendar_colors'] = strip_tags($new_instance['calendar_colors']);
		$instance['calendar_width'] = strip_tags($new_instance['calendar_width']);
		$instance['calendar_half'] = strip_tags($new_instance['calendar_half']);
		$instance['calendar_past'] = strip_tags($new_instance['calendar_past']);
		$instance['calendar_price'] = strip_tags($new_instance['calendar_price']);
		$instance['form_template'] = strip_tags($new_instance['form_template']);
		$instance['calendar_room'] = strip_tags($new_instance['calendar_room']);
		$instance['form_url'] = strip_tags($new_instance['form_url']);
		$instance['form_editor'] = $new_instance['form_editor'];
		$instance['form_button'] = strip_tags($new_instance['form_button']);
		return $instance;
	}

	/** @see WP_Widget::form */
	function form($instance){
		if($instance){
			$title = esc_attr( $instance[ 'title' ] );
			$calendar_width = esc_attr( $instance[ 'calendar_width' ] );
			$calendar_width = (float) $calendar_width;
			if($calendar_width > 100) $calendar_width = 100;
			$calendar_room = esc_attr( $instance[ 'calendar_room' ] );
			$calendar_half = isset($instance[ 'calendar_half' ]) ? esc_attr( $instance[ 'calendar_half' ] ) : 0;
			$calendar_color = isset($instance[ 'calendar_colors' ]) ? esc_attr( $instance[ 'calendar_colors' ] ) : 'default empty';
			$calendar_past = isset($instance[ 'calendar_past' ]) ? esc_attr( $instance[ 'calendar_past' ] ) : 0;
			$form_url = esc_attr( $instance[ 'form_url' ] );
			$form_button = esc_attr( $instance[ 'form_button' ] );
			$form_template = esc_attr( $instance[ 'form_template' ] );
			$calendar = esc_attr( $instance['calendar'] );
			$calendar_price = esc_attr( $instance['calendar_price'] );
		} else {
			$title = __('Reserve now!', 'easyReservations');
			$calendar_width = 100;
			$calendar_color = 'default empty';
			$calendar_room = 1;
			$calendar_half = 1;
			$calendar_price = 0;
			$calendar_past = 0;
			$calendar = 1;
			$form_url = __('Enter URL of a page with a real form', 'easyReservations');
			$form_button = __('Continue', 'easyReservations');
			$form_template = 'default-widget';
		}
		$color_scheme_options = easyreservations_get_color_schemes_options($calendar_color);
		$form_options = er_form_template_options($form_template);
		?>
		<p>
			<?php _e('The widget is not a real form and can only be used as pre-form to populate the real form. If you want to use the form or calendar as widget there are plugins to use shortcodes in text widgets.', 'easyReservations'); ?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'easyReservations'); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('calendar'); ?>"><?php _e('Show calendar', 'easyReservations'); ?>:
			<input id="<?php echo $this->get_field_id('calendar'); ?>" <?php checked( (bool) $calendar, true ); ?> name="<?php echo $this->get_field_name('calendar'); ?>" type="checkbox" /></label> 
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('calendar_colors'); ?>"><?php _e('Color scheme', 'easyReservations'); ?>:
			<select id="<?php echo $this->get_field_id('calendar_colors'); ?>" name="<?php echo $this->get_field_name('calendar_colors'); ?>" ><?php echo $color_scheme_options; ?></select></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('calendar_half'); ?>"><?php _e('Arrival and departure', 'easyReservations'); ?>:<br>
				<input id="<?php echo $this->get_field_id('calendar_half'); ?>" <?php checked( (bool) $calendar_half, true ); ?> value="1" name="<?php echo $this->get_field_name('calendar_half'); ?>" type="checkbox" />
				<?php _e("Display arrival and departure as half available dates", "easyReservations"); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('calendar_past'); ?>"><?php _e('Past', 'easyReservations'); ?>:<br>
				<input id="<?php echo $this->get_field_id('calendar_past'); ?>" <?php checked( (bool) $calendar_past, true ); ?> value="1" name="<?php echo $this->get_field_name('calendar_past'); ?>" type="checkbox" />
				<?php _e("Display past days availability", "easyReservations"); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('calendar_room'); ?>"><?php _e('Default resource', 'easyReservations'); ?>:
			<select id="<?php echo $this->get_field_id('calendar_room'); ?>" name="<?php echo $this->get_field_name('calendar_room'); ?>"><?php echo er_form_resources_options($calendar_room, false); ?></select></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('calendar_width'); ?>"><?php _e('Calendar width', 'easyReservations'); ?></label>:
			<select name="<?php echo $this->get_field_name('calendar_width'); ?>" id="<?php echo $this->get_field_id('calendar_width'); ?>"><?php echo er_form_number_options(1,100,$calendar_width); ?></select> %
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('calendar_price'); ?>"><?php _e('Show price in calendar', 'easyReservations'); ?>:
			<input id="<?php echo $this->get_field_id('calendar_price'); ?>" <?php checked( (bool) $calendar_price, true ); ?> name="<?php echo $this->get_field_name('calendar_price'); ?>" type="checkbox" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('form_template'); ?>"><?php _e('Widgets form template', 'easyReservations'); ?>:<br>
			<select id="<?php echo $this->get_field_id('form_template'); ?>" name="<?php echo $this->get_field_name('form_template'); ?>"><option value="none"><?php _e('No form', 'easyReservations'); ?></option><?php echo $form_options; ?></select></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('form_url'); ?>"><?php _e('Form', 'easyReservations'); ?> URL:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('form_url'); ?>" name="<?php echo $this->get_field_name('form_url'); ?>" type="text" value="<?php echo $form_url; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('form_button'); ?>"><?php _e('Submit button', 'easyReservations'); ?>:
			<input class="widefat" style="width:160px" id="<?php echo $this->get_field_id('form_button'); ?>" name="<?php echo $this->get_field_name('form_button'); ?>" type="text" value="<?php echo $form_button; ?>" /></label>
		</p>
		<?php 
	}
}
add_action( 'widgets_init', create_function( '', 'register_widget("easyReservations_form_widget");' ) );

function easyreservatons_call_datepickers(){
	easyreservations_build_datepicker(0, array("easy-widget-from", "easy-widget-to"), false, true);
}