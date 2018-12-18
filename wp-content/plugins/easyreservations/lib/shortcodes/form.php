<?php
function easyreservations_form_shortcode($atts){
	if(!in_the_loop() && did_action( 'wp_print_scripts' ) == 0) return '';
	$final_form = '';
	$error = '';
	if(isset($atts[0])){
		$form_content = stripslashes(get_option('reservations_form_'.$atts[0]));
		$form_name='<input type="hidden" name="formname" value="'.$atts[0].'">';
	} else {
		$form_content = stripslashes (get_option("reservations_form"));
		$form_name = '';
	}
	$form_id = 'easy-form-'.rand(0,99999);
	if(empty($form_content)) $form_content = stripslashes(get_option("reservations_form"));

	$atts = shortcode_atts(array(
		'resource' => 0,
		'price' => 1,
		'multiple' => 0,
		'resourcename' => __('Resource', 'easyReservations'),
		'cancel' => __('Cancel', 'easyReservations'),
		'credit' => __('Your reservation is complete', 'easyReservations'),
		'submit' => __('Your reservation was sent', 'easyReservations'),
		'validate' =>__('Reservation was validated successfully', 'easyReservations'),
		'subcredit' => '',
		'discount' => 100,
		'subsubmit' => '',
		'subvalidate' => '',
		'reset' => 1,
		'inline' => 0,
		'width' => '',
		'bg' => '#fff',
		'pers' => 0,
		'payment' => 1,
		'datefield' => ''
	), $atts);
	$atts['width'] = (float) $atts['width'];
	if($atts['width'] > 100 || $atts['width'] < 3) $atts['width'] = 100;

	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('datestyle' , false, array(), false, 'all');
	wp_enqueue_style('easy-frontend' , false, array(), false, 'all');
	wp_enqueue_script('easyreservations_send_form');
	wp_enqueue_script('easyreservations_data');
	wp_enqueue_script( 'easyreservations_send_validate' );
	wp_enqueue_script( 'easyreservations_send_price' );

    if(file_exists(RESERVATIONS_URL . 'assets/css/custom/form.css')) wp_enqueue_style('easy-form-custom' , false, array(), false, 'all');
	else wp_enqueue_style('easy-form' , false, array(), false, 'all');

	$validate_action = 'easyreservations_send_validate(false,\''.$form_id.'\');';
	$price_action = 'easyreservations_send_price(\''.$form_id.'\');';

	if(isset($_POST['easynonce'])){ // Check and Set the Form Inputs
		if (!wp_verify_nonce(sanitize_text_field($_POST['easynonce']), 'easy-user-add' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__('Back', 'easyReservations').')</a>' );
		//if(isset($_POST['captcha_value'])) $captcha = array( 'captcha_prefix' => $_POST['captcha_prefix'], 'captcha_value' => $_POST['captcha_value'] );
		//else $captcha = '';
        $arrival   = isset( $_POST['from'] ) ? ER_DateTime::createFromFormat( RESERVATIONS_DATE_FORMAT . ' H:i:s', sanitize_text_field( $_POST['from'] ) . ' 00:00:00' ) : current_time( 'timestamp' );
        $name_form = isset( $_POST['reservation-name'] ) ? sanitize_text_field( $_POST['reservation-name'] ) : '';
        $persons   = isset( $_POST['adults'] ) ? intval( $_POST['adults'] ) : 1;
        $children  = isset( $_POST['children'] ) ? intval( $_POST['children'] ) : 0;
        $email     = isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';
        $country   = isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : '';
        $resource  = ER()->resources()->get( isset( $_POST['resource'] ) ? intval( $_POST['resource'] ) : false );

		$arrivalplus = 0;
		if(isset($_POST['date-from-hour'])) $arrivalplus += (int) $_POST['date-from-hour'] * 60;
		if(isset($_POST['date-from-min'])) $arrivalplus += (int) $_POST['date-from-min'];
		if($arrivalplus > 0) $arrivalplus = $arrivalplus * 60;
		$departureplus = 0;
		if(isset($_POST['date-to-hour'])) $departureplus += (int) $_POST['date-to-hour'] * 60;
		if(isset($_POST['date-to-min'])) $departureplus += (int) $_POST['date-to-min'];
		if($departureplus > 0) $departureplus = $departureplus*60;
		if(isset($_POST['to'])) $departure = ER_DateTime::createFromFormat( RESERVATIONS_DATE_FORMAT . ' H:i:s', sanitize_text_field($_POST['to']) . ' 00:00:00');
		else {
			$departure = $arrival;
			if(isset($_POST['nights'])){
				$interval = $resource->interval;
				if(isset($_POST['nights_interval']) && $_POST['nights_interval'] > 0) $interval = intval($_POST['nights_interval']);
				if($departureplus == 0) $departure = ER_DateTime::addSeconds($arrival, (floatval($_POST['nights']) * $interval) + $arrivalplus);
				else $departure = ER_DateTime::addSeconds($arrival, floatval($_POST['nights']) * $interval);
			}	elseif($departureplus == 0){
				$departure = ER_DateTime::addSeconds($arrival, $arrivalplus + $resource->interval);
			}
		}
		$arrival = ER_DateTime::addSeconds($arrival, $arrivalplus);
		$departure = ER_DateTime::addSeconds($departure, $departureplus);

		$current_user = wp_get_current_user();
		$array = array('name' => $name_form, 'email' => $email, 'arrival' => $arrival,'departure' => $departure,'resource' => $resource, 'space' => 0,'country' => $country, 'adults' => $persons, 'children' => $children,'reserved' => date('Y-m-d H:i:s', time()),'status' => '','user' => $current_user->ID);
        if(isset($_POST['slot']) && $_POST['slot'] > -1){
            $array['slot'] = intval($_POST['slot']);
        }
        $custom = er_form_get_custom_submit();
        $customs = $custom[0];
        $error .= $custom[1];

		$res = new ER_Reservation(false, $array, false);

		try {
			if(isset($_POST['coupon'])){
                $explode = explode(',', sanitize_text_field($_POST['coupon']));
                $coupons = array();
                foreach($explode as $coupon){
                    $coupons[] = array( 'value' => $coupon );
                }
                $res->set_temporary_meta('coupon', $coupons);
                $res = apply_filters('easy_reservation_add_ajax', $res);
			}
            $res->set_temporary_meta('custom', $customs);

            $theID = $res->addReservation(array('reservations_email_to_admin', 'reservations_email_to_user'), array(false, $res->email));
			if($theID){
				foreach($theID as $key => $terror){
					if( $key %2 == 0 ) $error.=  '<li><label for="'.$terror.'">';
					else $error .= $terror.'</label></li>';
				}
			}
		} catch(Exception $e){
			$error .=  '<li><label>'.$e->getMessage().'</label></li>';
		}

		if(empty($error) && isset($arrival)){ //When Check gives no error Insert into Database and send mail
			do_action('reservation_successful_guest', $res);
			$final_form.= '<div class="'.RESERVATIONS_STYLE.' border" id="easy_form_success">';
			if(!empty($atts['submit'])) $final_form .= '<h1 class="easy_submit">'.$atts['submit'].'</h1>';
			$final_form .= '<div class="easy-content">';
			if(!empty($atts['subsubmit'])) $final_form .= '<span>'.$atts['subsubmit'].'</span>';
			if($atts['price'] == 1) $final_form .= '<span class="easy_show_price_submit">'.__('Price','easyReservations').': <b>'.er_format_money($res->get_price(), 1).'</b></span>';
			$final_form .= '</div>';

			if(function_exists('easyreservations_generate_payment_form') && $atts['payment'] > 0){
				$final_form .= easyreservations_generate_payment_form($res, $res->price, ($atts['payment'] == 2) ? true : false, (is_numeric($atts['discount']) && $atts['discount'] < 100) ? $atts['discount'] : false);
			}
            er_enqueue_js( 'jQuery(".easy-calendar,#showHourlyCalendar,.receipt-container,.calendar-resource-box").remove();window.location.hash = \'easy_form_success\';' );
            er_enqueue_js( stripslashes( get_option('easyreservations_successful_script', '') ) );
            return $final_form.'</div>';
		}
	}

	$form_content = stripslashes($form_content);
	$form_content = apply_filters( 'easy_form_content', $form_content);
	$resource_field_in_form = 0;
	$departure_field_in_form = false;

	$fields = er_form_template_parser($form_content, true);
	foreach($fields as $field){
		$tags = shortcode_parse_atts( $field );

		if($tags[0] == "date-to"){
			$departure_field_in_form = true;
		} elseif($tags[0]=="units" || $tags[0]=="nights" || $field[0]=="times"){
			$departure_field_in_form = true;
		} elseif($tags[0]=="hidden"){
			if($tags[1] == "room" || $tags[1] == "resource"){
				$resource_field_in_form=1;
			} elseif($tags[1] == "to"){
				$departure_field_in_form = true;
			} elseif($tags[1] == "units" || $tags[1]=="times"){
				$departure_field_in_form = true;
			}
		} elseif($tags[0] == "rooms" || $tags[0]=="resources"){
			$resource_field_in_form = 1;
		}

		switch($tags[0]){
			case "easy_receipt":
				$form_content = str_replace('['.$field.']', do_shortcode('['.$field.']'), $form_content);
				break;
            case "error":
                if(strlen($error) > 3){
                    $form_error = $error;
                    $class = '';
                } else {
                    $form_error = '';
                    $class = ' hidden';
                }
                $error = '';
                if(isset($tag['error_title'])) $error_title = $tag['error_title'];
                else $error_title='Errors found in the form';
                if(isset($tag['error_message'])) $error_message = $tag['error_message'];
                else $error_message='There is a problem with the form, please check and correct the following:';
                $form_content = str_replace('['.$field.']', '<div class="easy-show-error-div'.$class.'" id="easy-show-error-div"><h2>'.$error_title.'</h2>'.$error_message.'<ul id="easy-show-error">'.$form_error.'</ul></div>', $form_content);
                break;

            default:
                $form_field = apply_filters('easyreservations_form_field', er_form_generate_field($field, 'easy-form-', $form_id, $atts['resource'], $price_action, $validate_action), $tags);
                $form_content = str_replace('['.$field.']', $form_field, $form_content);
				break;
		}
	}

	if(!empty($error)){
        $final_form = '<div class="easy-show-error-div" id="easy-show-error-div"><ul id="easy-show-error">'.$error.'</ul></div>';
    }

	if($resource_field_in_form == 0 && isset($atts['resource']) && $atts['resource'] > 0) $form_content .= '<input type="hidden" name="resource" value="'.$atts['resource'].'">';
	elseif($resource_field_in_form == 0 && isset($_POST['resource'])) $form_content .= '<input type="hidden" name="resource" value="'.$_POST['resource'].'">';
	if(!$departure_field_in_form) $form_content .= '<input type="hidden" name="nights" id="easy-form-units" value="1">';
	$finalformedgesremoved = str_replace(array('[', ']'), '', $form_content);

    $form_class = RESERVATIONS_STYLE.' border';
	if($atts['inline'] == 1){
		$form_class = str_replace('easy-ui-container', 'inline', RESERVATIONS_STYLE);
	}

	$final_form .= '<div class="easyFrontendFormular" id="'.$form_id.'" style="width:'.$atts['width'].'%"><form method="post" id="easyFrontendFormular" name="easyFrontendFormular" class="'.$form_class.'">'.$form_name.'<input name="easynonce" type="hidden" value="'.wp_create_nonce('easy-user-add').'"><input name="pricenonce" type="hidden" value="'.wp_create_nonce('easy-price').'">'.$finalformedgesremoved.'<!-- Provided by easyReservations free Wordpress Plugin http://www.easyreservations.org --></form></div>';
	if(isset($_POST) && !empty($_POST))	er_enqueue_js( 'var posted_array = '.json_encode(array_map('sanitize_text_field',$_POST)).';for(var i in posted_array){ if(jQuery("*[name="+i+"]").attr("type") == "checkbox") jQuery("*[name="+i+"]").attr("checked", "checked"); else jQuery("*[name="+i+"]").val(posted_array[i]); } ' );
	if(!empty($price_action)){
        er_enqueue_js( 'jQuery(document).ready(function(){easyreservations_send_price(\''.$form_id.'\');});' );
	}

	$popup_template = '<div class="'.RESERVATIONS_STYLE.' border"><h1 class="easy_validate_message">'.$atts['validate'].'</h1><div class="easy-content">';
	if(!empty($atts['subvalidate'])) $popup_template.= '<span class="easy_validate_message_sub">'.$atts['subvalidate'].'</span>';
	$popup_template.= '<table id="easy_overlay_table"><thead><tr>';
	$popup_template.= '<th>'.__('Time', 'easyReservations').'</th>';
	$popup_template.= '<th>'.__($atts['resourcename']).'</th>';
	if($atts['pers'] && $atts['pers'] == 1) $popup_template.= '<th>'.__('Persons', 'easyReservations').'</th>';
	$popup_template.= '<th>'.__('Price', 'easyReservations').'</th>';
	$popup_template.= '<th></th></tr></thead><tbody id="easy_overlay_tbody"></tbody></table></div>';
	$popup_template.= '<div class="footer"><input type="button" class="easy-button" onclick="easyAddAnother();" value="'.sprintf(__('Add %s', 'easyReservations'), __('another reservation', 'easyReservations')).'">';
	$popup_template.= '<input type="button" class="green easy-button" onclick="easyFormSubmit(1);" value="'.__('Submit all reservations', 'easyReservations').'"></div></div>';

    er_enqueue_js( str_replace(array("\n","\r"), '', trim('var easyReservationAtts = '.json_encode($atts).';var easyInnerlayTemplate = "'.addslashes($popup_template).'";')));
	if(!empty($atts['datefield'])) define('EASYDATEFIELD', $atts['datefield']);
	add_action('wp_print_footer_scripts', 'easyreservations_make_datepicker');

	return $final_form;
}

function easyreservations_make_datepicker(){
	$array = array('easy-form-from', 'easy-form-to');

	if(defined('EASYDATEFIELD')){
		$newfields = explode(',', EASYDATEFIELD);
		$array = array_merge($array, $newfields);
	}
	easyreservations_build_datepicker(0, $array);
}