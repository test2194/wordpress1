<?php

/**
 * Parse form templates
 * @param string $content form template
 * @param bool $use_pattern whether to use default pattern
 * @param bool|string $define only return defined tags
 * @return array
 */
function er_form_template_parser( $content, $use_pattern = false, $define = false){
    if($use_pattern){
        $pattern = '\\[';						 // Opening bracket
        if($define){
            $pattern.= '(\\[?)'					 // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
                .	'('.$define.')';					 // 2: Shortcode name
        }
        $pattern .= '\\b'                        // Word boundary
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            .     '(?:'
            .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
            .     ')*?'
            . ')'
            . '(?:'
            .     '(\\/)'                        // 4: Self closing tag ...
            .     '\\]'                          // ... and closing bracket
            . '|'
            .     '\\]'                          // Closing bracket
            .     '(?:'
            .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .             '[^\\[]*+'             // Not an opening bracket
            .             '(?:'
            .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            .                 '[^\\[]*+'         // Not an opening bracket
            .             ')*+'
            .         ')'
            .         '\\[\\/\\2\\]'             // Closing shortcode tag
            .     ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
        preg_match_all( '/'. $pattern .'/s', $content, $match);
        if($define) $return = $match[3];
        else $return = $match[1];
        $return = array_merge($return, array());
    } else {
        preg_match_all( '/\[.*\]/U', $content, $match);
        $return = $match[0];
    }
    $return = str_replace(array('[',']'), '', $return);

    return $return;
}

/**
 * Get options for resources select
 * @param string|bool $selected
 * @param bool $check whether to check resources permission if current user can edit it
 * @param bool $exclude array of resource ids to exclude
 * @param bool $include array of resource ids to include
 * @param bool $addslashes
 * @return string
 */
function er_form_resources_options( $selected = false, $check = false, $exclude = false, $include = false, $addslashes = false){
    $resource_options = '';
    if($check){
        $resources = ER()->resources()->get_accessible();
    } else {
        $resources = ER()->resources()->get();
    }
    foreach( $resources as $resource ){
        if((!$exclude || !in_array($resource->ID, $exclude)) && (!$include || in_array($resource->ID, $include))){
            if($addslashes) $title = addslashes($resource->post_title);
            else $title = $resource->post_title;
            $select = $selected && $selected == $resource->ID ? ' selected="selected"' : '';
            $resource_options .= '<option value="'.$resource->ID.'"'.$select.'>'.__(stripslashes($title)).'</option>';
        }
    }
    return $resource_options;
}

/**
 * Get options for form template select
 * @param string|bool $selected
 * @return string
 */
function er_form_template_options( $selected = false){
    global $wpdb;
    $query = "SELECT option_name FROM ".$wpdb->prefix ."options WHERE option_name like 'reservations_form_%'"; // Get User made Forms
    $results = $wpdb->get_results($query);
    $selected = str_replace('reservations_form_', '', $selected);

    $return = '<option value="">'.__("Default form", "easyReservations").'</option>';
    foreach($results as $result){
        $cute = str_replace('reservations_form_', '', $result->option_name);
        if(!empty($cute)){
            $return.= '<option value="'.$cute.'" '.selected($selected, $cute, false).'>'.$cute.'</option>';
        }
    }
    return $return;
}

/**
 * Get options for country select
 * @param string|bool $selected
 * @return string
 */
function er_form_country_options( $selected = false ){

    $country_array = include RESERVATIONS_ABSPATH . 'i18n/countries.php';
    $country_options = '';
    foreach($country_array as $short => $country){
        $select = $short == $selected ? ' selected' : '';
        $country_options .= '<option value="'.$short.'"'.$select.'>'.htmlentities($country,ENT_QUOTES).'</option>';
    }

    return $country_options;
}

/**
 * Return numbered options for selects
 * @param $min
 * @param $max
 * @param string|int|bool $sel
 * @param int|float $increment
 * @return string
 */

function er_form_number_options( $min, $max, $sel = false, $increment = 1){
    $return = '';
    if(is_array($min)){
        $plus = $min[1];
        $min = $min[0];
    }
    for($num = $min; $num <= $max; $num = $num + ($increment ? $increment : 1)){
        $num_display = $num;
        $num_option = $num;

        if(strlen($min) == strlen($max) && $min < 10 && $max > 9 && $num < 10 && $num > $min){
            $num_display = '0'.$num;
        } elseif(isset($plus)){
            $num_option += $plus;
        }

        if($num_option === '00') $num_option = 0;

        $selected = !empty($sel) && $num_option == $sel ? 'selected="selected"' : '';
        $return .= '<option value="'.$num_option.'" '.$selected.'>'.$num_display.'</option>';
    }
    return $return;
}

function er_form_time_options( $selected = false, $increment = false, $range = false){
    $minmax = false;
    if($range){
        $minmax = array();
        $ranges = explode(';', $range);
        foreach($ranges as $range){
            if(!empty($range)){
                $range = explode('-', $range);
                $minmax[] = array($range[0], $range[1]);
            }
        }
    }
    $zero = strtotime('20.10.2010 00:00:00');
    $return = '';
    $max = $increment ? (60/$increment * 23) : 23;
    for($i = 0; $i <= $max; $i++){
        $value = $i;
        $time = $zero + ($i * 3600);
        $style = '';
        if($increment){
            $time = $zero + ($i * $increment * 60);
            $value = date('H-i', $time);
            if($max > 46 && is_int($time/3600)) $style = "font-weight:bold";
        }
        if($minmax){
            $passed = false;
            $hour = date('H', $time);
            foreach($minmax as $m){
                if($hour >= $m[0] && $hour <= $m[1]){
                    $passed = true;
                    break;
                }
            }
            if(!$passed){
                continue;
            }
        }
        $return .= '<option style="'.$style.'" value="'.$value.'" '.($selected == $value ? 'selected="selected"' : '').'>'.date(RESERVATIONS_TIME_FORMAT, $time).'</option>';
    }
    //if($time_format == 'h A') $return .= '</optgroup>';
    return $return;
}

function er_form_generate_field( $line, $input_id_prefix, $form_id, $default_resource, $price_action, $validate_action){
    $tag = shortcode_parse_atts( $line );
    $value = isset($tag['value']) ? $tag['value'] : '';
    $title = isset($tag['title']) ? $tag['title'] : '';
    $add_attr = isset($tag['placeholder']) ? 'placeholder="'.esc_attr($tag['placeholder']).'" ' : '';
    $add_attr .= isset($tag['style']) ? 'style="'.esc_attr($tag['style']).'" ' : '';
    $form_element = '';

    if(isset($tag['disabled'])){
        $array = array('units', 'nights', 'times', 'persons', 'adults', 'children', 'country', 'resources', 'rooms');
        if(in_array($tag[0], $array) || (($tag[0] == "custom" || $tag[0] == "price") && in_array($tag[1], array("check", "checkbox", "radio", "select")))) $add_attr .= 'disabled="disabled" ';
        else $add_attr .= 'readonly="readonly" ';
    }

    switch($tag[0]){
        case "date":
            $uid = uniqid();
            $form_element = er_get_template_html('date-selection.php' , array( 'uid' => $uid, 'time_selection' => 'bla' ));
            $opt = array(
                'resource' => isset($tag['resource']) ? intval($tag['resource']) : 0,
                'arrivalHour' => isset($tag['arrivalHour']) ? intval($tag['arrivalHour']) : false,
                'arrivalMinute' => isset($tag['arrivalMinute']) ? intval($tag['arrivalMinute']) : 0,
                'departureHour' => isset($tag['departureHour']) ? intval($tag['departureHour']) : false,
                'departureMinute' => isset($tag['departureMinute']) ? intval($tag['departureMinute']) : 0,
                'departure' => isset($tag['departure']) ? true : false,
                'init' => isset($_POST['from']) ? false : true,
                'form' => $form_id,
                'minDate' => 0,
                'time' => isset($tag['time']) ? true : false,
            );
            wp_enqueue_script( 'easy-date-selection' );
            er_enqueue_js( 'jQuery("#easy_selection_'.$uid.'").dateSelection('.json_encode($opt).');' );
            break;

        case "date-from":
            if(empty($value)) $value = date(RESERVATIONS_DATE_FORMAT, current_time( 'timestamp' )+86400);
            elseif(preg_match('/\+{1}[0-9]+/i', $value)){
                $cutplus = str_replace('+', '',$value);
                $value = date(RESERVATIONS_DATE_FORMAT, current_time( 'timestamp' )+($cutplus*86400));
            }
            $add_attr .= isset($tag["days"]) ? ' data-days="'.$tag["days"].'"' : '';
            $add_attr .= isset($tag["min"]) ? ' data-min="'.$tag["min"].'"' : '';
            $add_attr .= isset($tag["max"]) ? ' data-max="'.$tag["max"].'"' : '';
            $form_element =  '<span class="input-wrapper"><input id="'.$input_id_prefix.'from" type="text" name="from" value="'.$value.'" '.$add_attr.' title="'.$title.'" autocomplete="off" onchange="'.$price_action.$validate_action.'"><span class="input-box clickable"><span class="fa fa-calendar"></span></span></span>';
            break;

        case "date-to":
            if(empty($value)) $value = date(RESERVATIONS_DATE_FORMAT, current_time( 'timestamp' )+172800);
            elseif(preg_match('/\+{1}[0-9]+/i', $value)){
                $cutplus = str_replace('+', '',$value);
                $value = date(RESERVATIONS_DATE_FORMAT, current_time( 'timestamp' )+((int) $cutplus*86400));
            }
            $add_attr .= isset($tag["days"]) ? ' data-days="'.$tag["days"].'"' : '';
            $add_attr .= isset($tag["min"]) ? ' data-min="'.$tag["min"].'"' : '';
            $add_attr .= isset($tag["max"]) ? ' data-max="'.$tag["max"].'"' : '';
            $form_element = '<span class="input-wrapper"><input id="'.$input_id_prefix.'to" type="text" name="to" value="'.$value.'" '.$add_attr.' title="'.$title.'" autocomplete="off" onchange="'.$price_action.$validate_action.'"><span class="input-box clickable"><span class="fa fa-calendar"></span></span></span>';
            break;

        case "date-from-time":
        case "date-to-time":
            $increment = isset($tag["increment"]) ? $tag["increment"] : 15;
            $range = isset($tag['range']) ? $tag['range'] : false;
            $form_element = '<span class="select"><select id="'.$input_id_prefix.$tag[0].'" name="'.$tag[0].'" '.$add_attr.' title="'.$title.'" onchange="'.$price_action.$validate_action.'">'.er_form_time_options($value, $increment, $range).'</select></span>';
            break;

        case "date-from-hour":
        case "date-to-hour":
            $range = isset($tag['range']) ? $tag['range'] : false;
            $form_element = '<select id="'.$input_id_prefix.$tag[0].'" name="'.$tag[0].'" '.$add_attr.' title="'.$title.'" class="together" onchange="'.$price_action.$validate_action.'">'.er_form_time_options($value, false, $range).'</select>';
            break;

        case "date-from-min":
        case "date-to-min":
            $increment = isset($tag["increment"]) ? $tag["increment"] : 1;
            $form_element = '<select id="'.$input_id_prefix.$tag[0].'" name="'.$tag[0].'" '.$add_attr.' title="'.$title.'" class="together" onchange="'.$price_action.$validate_action.'">'.er_form_number_options("00", 59, 0, $increment).'</select>';
            break;

        case "units":
        case "nights":
        case "times":
            $append = '';
            $start = 1;
            if(isset($tag[1])) $start = $tag[1];
            if(isset($tag[2])) $end = $tag[2]; else $end = 6;
            if(isset($tag['interval'])){
                $append = '<input type="hidden" name="nights_interval" value="'.intval($tag['interval']).'">';
                $add_attr .= ' data-interval="'.intval($tag['interval']).'"';
            }
            $increment = isset($tag['increment']) ? floatval($tag['increment']) : 1;
            $form_element = '<span class="select"><select id="'.$input_id_prefix.'units" name="nights" title="'.$title.'" '.$add_attr.' onchange="'.$price_action.$validate_action.'">'.er_form_number_options($start, $end, $value, $increment).'</select></span>'.$append;
            break;

        case "persons":
        case "adults":
            $start = 1;
            if(isset($tag[1])) $start = $tag[1];
            if(isset($tag[2])) $end = $tag[2]; else $end = 6;
            $form_element = '<span class="select" '.$add_attr.'><select id="'.$input_id_prefix.'adults" name="adults" title="'.$title.'" onchange="'.$price_action.$validate_action.'">'.er_form_number_options($start,$end,$value).'</select></span>';
            break;

        case "childs":
        case "children":
            $start = 0;
            if(isset($tag[1])) $start = $tag[1];
            if(isset($tag[2])) $end = $tag[2]; else $end = 6;
            $form_element = '<span class="select" '.$add_attr.'><select name="children" title="'.$title.'" onchange="'.$price_action.$validate_action.'">'.er_form_number_options($start,$end,$value).'</select></span>';
            break;

        case "thename":
        case "name":
            $form_element = '<input type="text" id="'.$input_id_prefix.'name" name="reservation-name" '.$add_attr.' value="'.$value.'" title="'.$title.'" onchange="'.$validate_action.'">';
            break;

        case "email":
            $form_element = '<input type="text" id="'.$input_id_prefix.'email" name="email" '.$add_attr.' value="'.$value.'" title="'.$title.'" onchange="'.$price_action.$validate_action.'">';
            break;

        case "country":
            $form_element = '<span class="select" '.$add_attr.'><select id="'.$input_id_prefix.'country" name="country" title="'.$title.'">'.er_form_country_options($value).'</select></span>';
            break;

        case "show_price":
            if(isset($tag['before'])) $before = $tag['before'];
            else $before = '';
            $form_element = '<span class="'.$input_id_prefix.'price" title="'.$title.'" '.$add_attr.'>'.$before.'<span id="showPrice"><b></b></span></span>';
            break;

        case "captcha":
            require_once(RESERVATIONS_ABSPATH.'lib/captcha/captcha.php');
            $captcha = new easy_ReallySimpleCaptcha();
            if(isset($tag['color']) && $tag['color'] == 'white') $captcha->fg = array( 255, 255, 255 );
            $prefix = mt_rand();
            $url = $captcha->generate_image($prefix, $captcha->generate_random_word());
            $form_element = '<div class="row"><input type="text" class="captcha-input" title="'.$title.'" name="captcha_value" id="'.$input_id_prefix.'captcha" '.$add_attr.'><span class="captcha-image"><img id="'.$input_id_prefix.'captcha-img"	style="vertical-align:middle;margin-top: -5px;" src="'.RESERVATIONS_URL.'lib/captcha/tmp/'.$url.'"></span><input type="hidden" value="'.$prefix.'" name="captcha_prefix"></div>';
            break;

        case "hidden":
            if($tag[1] == "room" || $tag[1] == "resource"){
                if(!isset($tag[2]) && !is_numeric($tag[2])) $tag[2] = $default_resource;
                $resource = ER()->resources()->get(isset($_POST['resource']) ? intval($_POST['resource']) : intval($tag[2]));
                $form_element = '<input type="hidden" name="resource" value="'.$resource->ID.'">';
                if(isset($tag['display']) && $resource) $form_element .= '<strong>'.__(stripslashes($resource->post_title)).'</strong>';
            } elseif($tag[1] == "from"){
                $form_element = '<input type="hidden" name="from" value="'.esc_attr($tag[2]).'">';
                if(isset($tag['display'])) $form_element .= '<strong>'.sanitize_text_field(isset($_POST['from']) ? $_POST['from'] : $tag[2]).'</strong>';
            } elseif($tag[1] == "to"){
                $form_element = '<input type="hidden" name="to" value="'.esc_attr($tag[2]).'">';
                if(isset($tag['display'])) $form_element .= '<strong>'.sanitize_text_field(isset($_POST['to']) ? $_POST['to'] : $tag[2]).'</strong>';
            } elseif($tag[1] == "units" || $tag[1]=="times"){
                $form_element = '<input type="hidden" id="'.$input_id_prefix.'units" name="nights" value="'.esc_attr($tag[2]).'">';
                if(isset($tag['display'])) $form_element .= '<strong>'.floatval(isset($_POST['nights']) ? $_POST['nights'] : $tag[2]).'</strong>';
            } elseif($tag[1] == "persons" || $tag[1]=="adults"){
                $form_element = '<input type="hidden" name="adults" value="'.esc_attr($tag[2]).'">';
                if(isset($tag['display'])) $form_element .= '<strong>'.intval(isset($_POST['adults']) ? $_POST['adults'] : $tag[2]).'</strong>';
            } elseif($tag[1] == "childs" || $tag[1] == "children"){
                $form_element = '<input type="hidden" name="children" value="'.esc_attr($tag[2]).'">';
                if(isset($tag['display'])) $form_element .= '<strong>'.intval(isset($_POST['children']) ? $_POST['children'] : $tag[2]).'</strong>';
            } else {
                $form_element = '<input type="hidden" name="'.$tag[1].'" id="'.$tag[1].'" value="'.esc_attr($tag[2]).'">';
                if(isset($tag['display'])){
                    $value = sanitize_text_field(isset($_POST[$tag[1]]) ? $_POST[$tag[1]] : $tag[2]);
                    if($value !== '' && is_numeric($value) && $value >= 0 && $value < 10){
                        $value = '0'.$value;
                    }
                    $form_element .= '<strong>'.$value.'</strong>';
                }
            }
            break;

        case "rooms":
        case "resources":
            $exclude = isset($tag['exclude']) ? explode(',', $tag['exclude']) : '';
            $include = isset($tag['include']) ? explode(',', $tag['include']) : '';
            $form_element = '<span class="select" '.$add_attr.'><select name="resource" id="'.$input_id_prefix.'resource" onchange="'.$price_action.$validate_action.'">' . er_form_resources_options((empty($value)) ? $default_resource : $value, false, $exclude, $include, false) . '</select></span>';
            break;

        case "custom":
            if(isset($tag['id'])){
                $custom_fields = get_option('reservations_custom_fields');
                $form_field = '';
                if(isset($custom_fields['fields'][$tag['id']])){
                    $custom_field = $custom_fields['fields'][$tag['id']];
                    $onchange = '';
                    if(isset($custom_field['required'])) $onchange = $validate_action.';';
                    if(isset($custom_field['price'])) $onchange .= $price_action;
                    if(!empty($onchange)) $onchange = ' onchange="'.$onchange.'"';
                    $form_field = er_generate_custom_field( $tag['id'], false, $onchange.$add_attr, $value);
                }
                $form_element = $form_field;
            }
            break;

        case "submit":
            if(isset($tag['value'])) $value = $tag['value'];
            elseif(isset($tag[1])) $value = $tag[1];
            $action = '';
            if(!empty($validate_action)) $action .= 'easyreservations_send_validate(\'send\',\''.$form_id.'\'); return false';
            $form_element = '<input type="submit" title="'.$title.'" class="easy-button" value="'.$value.'" '.$add_attr.' onclick="'.$action.'"><span id="easybackbutton"></span>';
            break;

        default:
            $form_element = apply_filters('easy_form_field', $tag, $price_action, $validate_action, $form_id);
            break;
    }
    return $form_element;
}

function er_generate_custom_field( $id, $sel = false, $add_attr = '', $value = ''){
    $custom_fields = get_option('reservations_custom_fields');
    $form_field = '';
    if(isset($custom_fields['fields'][$id])){
        $custom_field = $custom_fields['fields'][$id];
        if($custom_field['type'] == 'text' || $custom_field['type'] == 'number'){
            $value = $sel ? $sel : '';
            $form_field = '<input type="'.$custom_field['type'].'" name="easy-new-custom-'.$id.'" id="easy-new-custom-'.$id.'" value="'.$value.'" '.$add_attr.'>';
        } elseif($custom_field['type'] == 'slider'){
            $value = $sel ? $sel : '';
            $slider = current($custom_field['options']);
            $form_field = '<input type="hidden" name="easy-new-custom-'.$id.'" id="easy-new-custom-'.$id.'" class="easy-slider-input" value="'.$value.'" ';
            if(isset($slider['min'])) $form_field .= 'data-min="'.$slider['min'].'" ';
            if(isset($slider['max'])) $form_field .= 'data-max="'.$slider['max'].'" ';
            if(isset($slider['label'])) $form_field .= 'data-label="'.$slider['label'].'" ';
            if(isset($slider['step'])) $form_field .= 'data-step="'.$slider['step'].'" ';
            $form_field .= $add_attr.'><span class="easy-slider-label-'.$id.'"></span>';
        } elseif($custom_field['type'] == 'area'){
            $value = $sel ? $sel : '';
            $form_field = '<textarea name="easy-new-custom-'.$id.'" id="easy-new-custom-'.$id.'"'.$add_attr.'>'.$value.'</textarea>';
        } elseif($custom_field['type'] == 'check'){
            foreach($custom_field['options'] as $opt_id => $option){
                $checked = $sel || (!$sel && isset($option['checked'])) ? ' checked="checked"' : '';
                $form_field .= '<label class="wrapper"><input type="checkbox" name="easy-new-custom-'.$id.'" id="easy-new-custom-'.$id.'" value="'.$opt_id.'" '.$checked.$add_attr.'>';
                $form_field .= '<span class="input"></span>'.$value.'</label>';
            }
        } elseif($custom_field['type'] == 'radio'){
            $form_field .= '<span class="radio">';
            foreach($custom_field['options'] as $opt_id => $option){
                $checked = ($sel && $sel == $opt_id) || (!$sel && $option['checked']) ? ' checked="checked"' : '';
                $form_field .= '<label class="wrapper"><input type="radio" name="easy-new-custom-'.$id.'" id="easy-new-custom-'.$id.'" value="'.$opt_id.'" '.$checked.$add_attr.'><span class="input"></span>'.$option['value'].'</label>';
            }
            $form_field .= '</span>';
        } elseif($custom_field['type'] == 'select'){
            $form_field = '<span class="select" '.$add_attr.'><select name="easy-new-custom-'.$id.'" id="easy-new-custom-'.$id.'">';
            foreach($custom_field['options'] as $opt_id => $option){
                $checked = $sel && $sel == $opt_id ? ' selected="selected"' : '';
                $form_field .= '<option value="'.$opt_id.'"'.$checked.'>'.$option['value'].'</option>';
            }
            $form_field .= '</select></span>';
        }
    }
    return $form_field;
}

function er_form_get_custom_submit(){
    if(isset($_POST['formname'])) $theForm = stripslashes(get_option('reservations_form_'.sanitize_text_field($_POST['formname'])));
    else $theForm = stripslashes(get_option("reservations_form"));
    if(empty($theForm)) $theForm = stripslashes(get_option("reservations_form"));

    $error = '';
    $theForm = apply_filters( 'easy_form_content', $theForm);
    $tags = er_form_template_parser($theForm, true);
    $custom_fields = get_option('reservations_custom_fields');
    $custom_form = array();

    foreach($tags as $fields){
        $field=shortcode_parse_atts( $fields);
        if($field[0]=="custom"){
            if(isset($field["id"])){
                if(isset($_POST['easy-new-custom-'.$field["id"]])){
                    $custom_form[] = array( 'id' => $field["id"], 'value' => sanitize_text_field(stripslashes($_POST['easy-new-custom-'.$field["id"]])));
                } elseif(isset($custom_fields[$field["id"]]['required'])){
                    $error.= '<li>'.sprintf(__('%s is required', 'easyReservations'), $custom_fields[$field["id"]]['title']).'</li>';
                }
            }
        }
    }
    return array($custom_form, $error);
}