<?php

function er_date_get_interval_pattern( $interval, $only_time = false ){
    $time_pattern = '00:00';
    if($interval <= 3600) {
        $minute = ':00';
        if ( $interval < 3600 ) {
            $minute = ':i';
        }

        $time_format = explode( ' ', RESERVATIONS_TIME_FORMAT );

        if ( isset( $time_format[1] ) && ! empty( $time_format[1] ) ) {
            $time_pattern = "h" . $minute . ' ' . $time_format[1];
        } else {
            $time_pattern = "H" . $minute;
        }
    }

    if($only_time){
        return $time_pattern;
    } else {
        return RESERVATIONS_DATE_FORMAT.$time_pattern;
    }
}


function er_date_get_interval_label( $interval = 0, $singular = 0, $ucfirst = false){
    if($interval == 1800){
        $string = _n('half hour', 'half hours', $singular, 'easyReservations');
    } elseif($interval == 3600){
        $string = _n('hour', 'hours', $singular, 'easyReservations');
    } elseif($interval == 86400){
        $string = _n('day', 'days', $singular, 'easyReservations');
    } elseif($interval == 86401){
        $string = _n('night', 'nights', $singular, 'easyReservations');
    } elseif($interval == 604800){
        $string = _n('week', 'weeks', $singular, 'easyReservations');
    } elseif($interval == 2592000){
        $string = _n('month', 'months', $singular, 'easyReservations');
    } else {
        $string = _n('time', 'times', $singular, 'easyReservations');
    }

    if($ucfirst) return ucfirst($string);
    return $string;
}

/**
 * Get day or month names
 *
 * @since 1.8
 *
 * @param int $interval 0 for days, 1 for monthes
 * @param int $substr number of characters to display 0=full
 * @param int $date number of day/or month to return just that string
 * @return array/string with name of date
 */

function er_date_get_label( $interval = 0, $substr = 0, $date = false, $addslashes = false ){
    $name = array();
    if($interval == 0){
        $name[] = __('Monday', 'easyReservations');
        $name[] = __('Tuesday', 'easyReservations');
        $name[] = __('Wednesday', 'easyReservations');
        $name[] = __('Thursday', 'easyReservations');
        $name[] = __('Friday', 'easyReservations');
        $name[] = __('Saturday', 'easyReservations');
        $name[] = __('Sunday', 'easyReservations');
    } else {
        $name[] = __('January', 'easyReservations');
        $name[] = __('February', 'easyReservations');
        $name[] = __('March', 'easyReservations');
        $name[] = __('April', 'easyReservations');
        $name[] = __('May', 'easyReservations');
        $name[] = __('June', 'easyReservations');
        $name[] = __('July', 'easyReservations');
        $name[] = __('August', 'easyReservations');
        $name[] = __('September', 'easyReservations');
        $name[] = __('October', 'easyReservations');
        $name[] = __('November', 'easyReservations');
        $name[] = __('December', 'easyReservations');
    }

    if($substr > 0 && function_exists('mb_internal_encoding')) mb_internal_encoding("UTF-8");
    foreach($name as $key => $day){
        if($substr > 0 && function_exists('mb_substr')) $name[$key] = mb_substr($day, 0, $substr);
        elseif($substr > 0){
            $name[$key] = htmlentities(substr(html_entity_decode($day), 0, $substr));
        }
        if($addslashes) $name[$key] = addslashes($name[$key]);
    }

    if($date !== false) return $name[$date];
    else return $name;
}

/**
 * Get date format without year
 */
function er_date_get_format_without_year(){
    switch(RESERVATIONS_DATE_FORMAT){
        case 'd.m.Y':
            return 'd.m';
            break;
        case 'Y-m-d':
            return 'm-d';
            break;
        case 'd-m-Y':
            return 'd-m';
            break;
        case 'm/d/Y':
        case 'Y/m/d':
            return 'm/d';
            break;
    }
    return RESERVATIONS_DATE_FORMAT;
}