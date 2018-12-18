<?php

defined( 'ABSPATH' ) || exit;

/**
 * WC_Ajax class.
 */
class ER_AJAX {

    /**
     * Hook in ajax handlers.
     */
    public static function init() {
        self::add_ajax_events();
    }

    /**
     * Hook in methods - uses WordPress ajax handlers (admin-ajax).
     */
    public static function add_ajax_events() {
        // woocommerce_EVENT => nopriv.
        $ajax_events = array(
            'calendar' => true,
            'send_calendar' => true,
            'send_price' => true,
            'send_form' => true,
            'send_validate' => true,
            'send_fav' => false,
            'get_custom' => false,
            'send_table' => false,
        );

        foreach ( $ajax_events as $ajax_event => $nopriv ) {
            add_action( 'wp_ajax_easyreservations_' . $ajax_event, array( __CLASS__, $ajax_event ) );

            if ( $nopriv ) {
                add_action( 'wp_ajax_nopriv_easyreservations_' . $ajax_event, array( __CLASS__, $ajax_event ) );
            }
        }
    }

    public static function calendar(){
        check_ajax_referer( 'easy-date-selection', 'security' );

        $date    = !is_numeric( $_POST['date'] ) ? ER_DateTime::createFromFormat( RESERVATIONS_DATE_FORMAT, sanitize_text_field( $_POST['date'] )) : new DateTime();
        $arrival = false;
        if( intval( $_POST['arrival'] ) !== 0 ) {
            $arrival = ER_DateTime::createFromFormat( RESERVATIONS_DATE_FORMAT, sanitize_text_field( $_POST['arrival'] ) );
        }

        $adults       = isset( $_POST['adults'] ) ? intval( $_POST['adults'] ) : 1;
        $children     = isset( $_POST['children'] ) ? intval( $_POST['children'] ) : 0;
        $resource     = ER()->resources()->get( intval( $_POST['resource'] ) );
        $resource_req = $resource->requirements;

        $date->setTime( 0, 0, 0 );
        if( $arrival === false && ( empty( $_POST['minDate'] ) || $date->format( 'm.Y' ) !== date( 'm.Y', current_time( 'timestamp' ) ) ) ) {
            $date->modify( 'first day of this month' );
        }
        $end = clone $date;
        $end = $end->modify( 'first day of next month' );

        if( intval( $_POST['months'] ) > 1 ) {
            $end->modify( '+' . ( intval( $_POST['months'] ) - 1 ) . ' month' );
        }
        $end = $end->getTimestamp();

        $days            = array();
        $was_unavailable = false;
        while($date->getTimestamp() <= $end){

            if($resource->slots){

                $matrix = er_resource_get_slot_matrix($resource, $date->getTimestamp(), true, $adults, $children);

                $days[$date->format(RESERVATIONS_DATE_FORMAT)] = empty($matrix) ? array(0) : $matrix;

            } else {

                $left = false;

                $req = $resource_req;

                if(!$was_unavailable){
                    if($resource->filter && !empty($resource->filter)){
                        foreach($resource->filter as $filter){
                            if($filter['type'] == 'req'){
                                if($resource->time_condition($filter, $arrival ? $arrival->getTimestamp() : $date->getTimestamp())){
                                    $req = $filter['req'];
                                    break;
                                }
                            }
                        }
                    }
                }

                $latest_possible_arrival	 = isset($req['start-h']) ? $req['start-h'][1] : 23;
                $earliest_possible_departure = isset($req['end-h']) ? $req['end-h'][0] : 0;

                if(!$arrival){
                    $time = isset($req['start-h']) ? $req['start-h'] : array(0, 23);
                } else {
                    $time = isset($req['end-h']) ? $req['end-h'] : array(0, 23);
                }

                if(!$arrival){

                    if(isset($req['start-on']) && $req['start-on'] !== 0 && ($req['start-on'] == 8 ||  !in_array($date->format("N"), $req['start-on']))){
                        $avail = $resource->quantity;
                    } else {
                        if($resource->frequency < 86400){
                            $left = array();
                            $date_to_check = $date->getTimestamp() + $time[0] * 3600;
                            $until = $date->getTimestamp() + $time[1] * 3600 + 3599;
                            while($date_to_check < $until){
                                $avail = $resource->quantity;

                                if(is_null( $_POST['minDate'] ) || $date_to_check >= current_time( 'timestamp' )){

                                    $res = new ER_Reservation( false, array(
                                        'arrival' => $date_to_check,
                                        'departure' => $date_to_check + $resource->frequency,
                                        'resource' => $resource,
                                        'adults' => $adults,
                                        'children' => $children
                                    ), false );

                                    $avail = $res->checkAvailability(0);
                                }

                                $left[date(RESERVATIONS_TIME_FORMAT, $date_to_check)] = $resource->quantity - $avail;

                                $date_to_check += $resource->frequency;
                            }
                        } else {
                            $res = new ER_Reservation( false, array(
                                'arrival' => $date->getTimestamp() + $latest_possible_arrival * 3600,
                                'departure' => $date->getTimestamp() + $latest_possible_arrival * 3600 + $req['nights-min'] * $resource->interval,
                                'resource' => $resource,
                                'adults' => $adults,
                                'children' => $children
                            ), false );

                            $avail = $res->checkAvailability(2);
                            if($avail->filter > 0){
                                $avail = $resource->quantity;
                            } else {
                                if($avail->count_all >= $resource->quantity){
                                    $avail->count_all = $avail->count_all - $avail->departure + $avail->arrival;
                                    if(!empty($avail->max_arrival)){
                                        $hour = date('H', strtotime($avail->max_arrival));
                                        $time[1] = $hour < $time[1] ? $hour : $time[1];
                                    }
                                    if(!empty($avail->min_departure)){
                                        $hour = date('H', strtotime($avail->min_departure));
                                        $time[0] = $hour > $time[0] ? $hour : $time[0];
                                    }
                                    if($time[0] == $time[1]){
                                        $avail->count_all = $resource->quantity;
                                    }
                                }

                                $avail = $avail->count_all;
                            }
                        }
                        if(!$left){
                            $left = $resource->quantity - $avail;
                        }

                    }

                } else {
                    $arrival_time = $latest_possible_arrival * 3600;
                    if(is_numeric($_POST['arrivalTime'])){
                        $arrival_time = intval($_POST['arrivalTime']);
                    }

                    $arrival_stamp = $arrival->getTimestamp() + $arrival_time;

                    if(isset($req['end-on']) && $req['end-on'] !== 0 && ($req['end-on'] == 8 ||  !in_array($date->format("N"), $req['end-on']))){
                        $left = 0;
                    } else {
                        if($resource->frequency < 86400){
                            if(!$was_unavailable){
                                $left = array();
                                $date_to_check = $date->getTimestamp() + $time[0] * 3600;
                                $until = $date->getTimestamp() + $time[1] * 3600 + 3599;
                                while($date_to_check < $until){
                                    $res = new ER_Reservation( false, array(
                                        'arrival' => $arrival_stamp,
                                        'departure' => $date_to_check,
                                        'resource' => $resource,
                                        'adults' => $adults,
                                        'children' => $children
                                    ), false );

                                    if($req['nights-min'] <= $res->times  && !$was_unavailable){
                                        if(($req['nights-max'] > 0 && $req['nights-max'] < $res->times) && !$was_unavailable){
                                            $was_unavailable = $date->format( RESERVATIONS_DATE_FORMAT );
                                        }

                                        $avail = $resource->quantity;

                                        if((is_null( $_POST['minDate'] ) || $date_to_check >= current_time( 'timestamp' )) && $date_to_check > $arrival_stamp && !$was_unavailable){

                                            $avail = $res->checkAvailability(0);

                                            if($resource->quantity - $avail < 1){
                                                $was_unavailable = $date->format(RESERVATIONS_DATE_FORMAT);
                                            }
                                        }
                                        $left[date(RESERVATIONS_TIME_FORMAT, $date_to_check)] = $resource->quantity - $avail;

                                    } else {
                                        $left[date(RESERVATIONS_TIME_FORMAT, $date_to_check)] = 0;
                                    }
                                    $date_to_check += $resource->frequency;
                                }
                            } else {
                                $left = 0;
                            }

                        } else {
                            $res = new ER_Reservation( false, array(
                                'arrival' => $arrival_stamp,
                                'departure' => $date->getTimestamp() + $earliest_possible_departure * 3600,
                                'resource' => $resource,
                                'adults' => $adults,
                                'children' => $children
                            ), false );

                            if(($req['nights-max'] > 0 && $req['nights-max'] < $res->times) && !$was_unavailable){
                                $was_unavailable = $date->format( RESERVATIONS_DATE_FORMAT );
                            }

                            if($req['nights-min'] <= $res->times && !$was_unavailable){
                                if(isset($req['end-on']) && $req['end-on'] !== 0 && ($req['end-on'] == 8 ||  !in_array($date->format("N"), $req['end-on']))){
                                    $avail = $resource->quantity;
                                } else {


                                    $avail = $res->checkAvailability(2);
                                    if($avail->filter > 0){
                                        $avail = $resource->quantity;
                                    } else {
                                        if($avail->count_all >= $resource->quantity){
                                            $avail->count_all = $avail->count_all - $avail->arrival + $avail->departure;
                                            if(!empty($avail->max_arrival)){
                                                $hour = date('H', strtotime($avail->max_arrival));
                                                $time[1] = $hour < $time[1] ? $hour : $time[1];
                                            }
                                            if(!empty($avail->min_departure)){
                                                $hour = date('H', strtotime($avail->min_departure));
                                                $time[0] = $hour > $time[0] ? $hour : $time[0];
                                            }
                                            if($time[0] == $time[1]){
                                                $avail->count_all = $resource->quantity;
                                            }
                                        }

                                        $avail = $avail->count_all;

                                        if($resource->quantity - $avail < 1){
                                            $was_unavailable = $date->format(RESERVATIONS_DATE_FORMAT);
                                        }
                                    }
                                }

                                $left = $resource->quantity - $avail;

                            } else {
                                $left = 0;
                            }
                        }
                    }
                }

                $days[$date->format(RESERVATIONS_DATE_FORMAT)] = array($left, $time, $req['nights-min']);
            }

            $date->modify('+1 day');
        }

        if($resource->slots){

            wp_send_json($days);

        } else {

            $days['max'] = $was_unavailable;
            wp_send_json($days);

        }
    }

    public static function send_calendar(){
        $settings = get_option( "reservations_settings" );
        check_ajax_referer( 'easy-calendar', 'security' );
        $atts = array_map( 'wp_filter_post_kses', $_POST['atts'] );

        $adults = 1; $children = 0; $reserved = 0; $last = null;
        $rand = $atts['id'];
        $resource = ER()->resources()->get(intval($_POST['resource']));
        if(isset($_POST['adults'])) $adults = intval($_POST['adults']);
        if(isset($_POST['children'])) $children = intval($_POST['children']);
        if(isset($_POST['reserved'])) $reserved = intval($_POST['reserved']);
        if(isset( $settings['mergeres'])){
            if(is_array( $settings['mergeres']) && isset( $settings['mergeres']['merge']) && $settings['mergeres']['merge'] > 0) $resource_quantity = $settings['mergeres']['merge'];
            elseif(is_numeric( $settings['mergeres']) && $settings['mergeres'] > 0) $resource_quantity = $settings['mergeres'];
        }
        if(!isset($resource_quantity)){
            $resource_quantity = $resource->quantity;
        }
        $month_names = er_date_get_label(1);
        $day_names = er_date_get_label(0,2);
        $requirements = false;
        if($atts['req'] == 1) $requirements = $resource->quantity;
        if(isset($_POST['where']) && $_POST['where'] == "widget") $where = 'widget';
        else $where = 'shortcode';
        $divider = 1;
        $months = 1;

        if(isset($atts['months']) && $where == 'shortcode' && preg_match('/^[0-9]+x{1}[0-9]+$/i', $atts['months'])){
            $explode_months = explode('x', $atts['months']);
            $months = $explode_months[0] * $explode_months[1];
            $divider = $explode_months[0];
        }

        if(function_exists('easyreservations_generate_multical') && $where == 'shortcode' && $months != 1) $timenows = easyreservations_generate_multical($_POST['date'] + $atts['date'], $months);
        else $timenows=array(strtotime("+".($_POST['date']+$atts['date'])." month", strtotime(date("01.m.Y", current_time( 'timestamp' )) )));

        if(!isset($timenows[1])) $month = $month_names[date("n", $timenows[0])-1].' '.date("Y", $timenows[0]);
        else {
            $anf =  $timenows[0];
            $end = $timenows[count($timenows)-1];
            if(date("Y", $anf) == date("Y", $end) ){
                $month=$month_names[date("n", $anf)-1].' - '.$month_names[date("n", $end)-1].' '.date("Y", $anf);
            } else {
                $month=$month_names[date("n", $anf)-1].' '.date("y", $anf).' - '.$month_names[date("n", $end)-1].' '.date("y", $end);
            }
        }

        echo '<table class="calendar-table '.($months > 1 ? 'multiple' : '').'" cellpadding="0" cellspacing="0">';
        echo '<thead><tr class="calendarheader">';
        echo '<th class="calendar-header-month-prev" onClick="easyCalendars['.$rand.'].change(\'date\', \''.($_POST['date']-$atts['interval']).'\');"><span class="fa fa-chevron-left"></span></th>';
        echo '<th colspan="1" class="calendar-header-show-month" style="position:relative"><div class="let-me-fly" style="text-align: center;	padding:0;margin:0;	 display: inline-block;">'.$month.'</div></th>';
        echo '<th class="calendar-header-month-next" onClick="easyCalendars['.$rand.'].change(\'date\', \''.($_POST['date']+$atts['interval']).'\');"><span class="fa fa-chevron-right"></span></th>';
        echo '</tr></thead>';
        echo '<tbody style="text-align:center;white-space:nowrap;padding:0">';
        echo '<tr><td colspan="3" style="white-space:nowrap;padding:0;margin:0;border:0">';

        if(count($timenows) > 1){
            $atts['width'] = ((float) $atts['width']) / $divider;
            $percent = 100 / $divider;
        } else $percent = 100;
        $month_count=0;

        foreach($timenows as $timenow){
            $month_count++;
            $diff=1;
            $setet=0;
            $year_now=date("Y", $timenow);
            $month_now=date("m", $timenow);
            $key = $year_now.$month_now;
            if(function_exists('cal_days_in_month')) $num = cal_days_in_month(CAL_GREGORIAN, $month_now, $year_now); // 31
            else $num = date("d", mktime(0, 0, 0, $month_now +1, 0, $year_now));

            if($month_now-1 <= 0){
                $month_now_fix = 13;
                $year_now_fix = $year_now-1;
            } else {
                $month_now_fix = $month_now;
                $year_now_fix = $year_now;
            }

            if(function_exists('cal_days_in_month')) $num2 = cal_days_in_month(CAL_GREGORIAN, $month_now_fix-1, $year_now_fix); // 31
            else $num2 = date("d", mktime(0, 0, 0, $month_now_fix, 0, $year_now_fix));
            $float = $month_count % $divider == 0 ? '' : 'float:left';

            echo '<table class="calendar-direct-table '.str_replace(':left', '', $float).'" style="width:'.$percent.'%;'.$float.'">';
            echo '<thead>';
            if($atts['header'] == 1) echo '<tr><th class="calendar-header-month" colspan="7">'.$month_names[date("n", $timenow)-1].'</th></tr>';
            echo '<tr>';
            echo '<th class="calendar-header-cell">'.$day_names[0].'</th>';
            echo '<th class="calendar-header-cell">'.$day_names[1].'</th>';
            echo '<th class="calendar-header-cell">'.$day_names[2].'</th>';
            echo '<th class="calendar-header-cell">'.$day_names[3].'</th>';
            echo '<th class="calendar-header-cell">'.$day_names[4].'</th>';
            echo '<th class="calendar-header-cell">'.$day_names[5].'</th>';
            echo '<th class="calendar-header-cell">'.$day_names[6].'</th>';
            echo '</tr></thead>';
            echo '<tbody style="text-align:center;padding;0;margin:0">';
            $row_count=0;
            while($diff <= $num){
                $date_of_day=strtotime($diff.'.'.$month_now.'.'.$year_now);
                $day_index=date("N", $date_of_day);
                if($setet==0 || $setet==7 || $setet==14 || $setet==21 || $setet==28 || $setet==35){ echo '<tr style="text-align:center">'; $row_count++; }
                if($setet==0 && $diff==1 && $day_index != "1"){
                    echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$day_index+2).'</span></td>'; $setet++;
                    if($setet==1 && $diff==1 && $day_index != "2"){
                        echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$day_index+2+$setet).'</span></td>'; $setet++;
                        if($setet==2 && $diff==1 && $day_index != "3"){
                            echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$day_index+2+$setet).'</span></td>'; $setet++;
                            if($setet==3 && $diff==1 && $day_index != "4"){
                                echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$day_index+2+$setet).'</span></td>'; $setet++;
                                if($setet==4 && $diff==1 && $day_index != "5"){
                                    echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$day_index+2+$setet).'</span></td>'; $setet++;
                                    if($setet==5 && $diff==1 && $day_index != "6"){
                                        echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$day_index+2+$setet).'</span></td>'; $setet++;
                                        if($setet==6 && $diff==1 && $day_index != "7"){
                                            echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$day_index+2+$setet).'</span></td>'; $setet++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $res = new ER_Reservation(false, array( 'email' => 'mail@test.com', 'arrival' => $date_of_day + 43200, 'departure' =>  $date_of_day, 'resource' => intval($_POST['resource']), 'adults' => $adults, 'children' => $children, 'reserved' => current_time( 'timestamp' ) - ( $reserved * 86400)), false);
                try {
                    $final_price = '';
                    if($atts['past'] == 0 && strtotime(date('d.m.Y', $date_of_day)) < strtotime(date('d.m.Y', current_time( 'timestamp' )))){
                        $background_td = ' calendar-cell-past';
                        if(intval($atts['price']) !== 0) $final_price = '<span class="calendar-cell-price">-</b>';
                    } else {
                        if($atts['price'] > 0 && is_numeric($atts['price'])){
                            $price = $res->Calculate(isset($atts['notax']) && $atts['notax'] > 0 ? false : true);
                            if($atts['price'] == 1 || $atts['price'] == 2 || $atts['price'] == 5){ $explode = explode('.', $price); $price = $explode[0]; }
                            if($atts['price'] == 1) $price_display = $price.'&'.RESERVATIONS_CURRENCY.';';
                            elseif($atts['price'] == 2) $price_display = $price;
                            elseif($atts['price'] == 3) $price_display = er_format_money($price, 1);
                            elseif($atts['price'] == 4) $price_display = er_format_money($price);
                            else $price_display = '&'.RESERVATIONS_CURRENCY.';'.$price;
                            $final_price = '<span class="calendar-cell-price">'.$price_display.'</b>';
                        }

                        $avail = $res->checkAvailability(5);
                        if($atts['price'] == "avail") $final_price = '<span class="calendar-cell-price">'.($resource_quantity-$avail[0]).'</b>';

                        if($avail[0] >= $resource_quantity) $background_td = " calendar-cell-full";
                        elseif($avail[0] > 0 || $avail[2] > 0) $background_td = " calendar-cell-occupied";
                        else $background_td = " calendar-cell-empty";

                        $new = $background_td;
                        if($last == null){
                            $res->arrival -= 86400;
                            $lastavail = $res->checkAvailability(5);
                            if($lastavail[0] >= $resource_quantity) $last = " calendar-cell-full";
                            elseif($lastavail[0] > 0 || $lastavail[2] > 0) $last = " calendar-cell-occupied";
                            else $last = " calendar-cell-empty";
                        }
                        if($atts['half'] == 1 && $last !== $new && $avail[1] > 0){
                            $background_td .= $last.'2';
                            $background_td .= " calendar-cell-halfend";
                        } elseif($atts['half'] < 1 && $last !== $new && $avail[0] >= $resource_quantity){
                            //$background_td = " calendar-cell-occupied";
                        }
                        $last = $new;

                        //onclick="easyreservations_click_calendar(this,\''.date(RESERVATIONS_DATE_FORMAT, $dateofeachday).'\', \''.$rand.'\', \''.$key.'\');"
                        if($atts['req'] == 1 && $requirements && ((isset($requirements['start-on']) && is_array($requirements['start-on']) && $requirements['start-on'] != 0) || (isset($requirements['end-on']) && is_array($requirements['end-on']) && $requirements['end-on'] != 0))){
                            $das = true;
                            if(isset($requirements['start-on']) && is_array($requirements['start-on']) && $requirements['start-on'] != 0 && !in_array(date("N", $date_of_day), $requirements['start-on'])){
                                $background_td.= " reqstartdisabled reqdisabled";
                                $das = false;
                            }
                            if(isset($requirements['end-on']) && is_array($requirements['end-on']) && $requirements['end-on'] != 0 && !in_array(date("N", $date_of_day), $requirements['end-on'])){
                                $background_td.= " reqenddisabled";
                                $das = false;
                            }
                            if($das) $background_td.= " notreqdisabled";
                        }

                    }

                    if(date("d.m.Y", $date_of_day) == date("d.m.Y", current_time( 'timestamp' ))) $background_td.=" today";

                    if(isset($atts['style']) && $atts['style'] == 3 && $diff < 10) $show = '0'.$diff;
                    else $show = $diff;

                    if($date_of_day > current_time( 'timestamp' )-86401 && $atts['select'] > 0) $onclick = 'date="'.date(RESERVATIONS_DATE_FORMAT, $date_of_day).'"';
                    else $onclick ='style="cursor:default"';

                    echo '<td class="calendar-cell'.$background_td.'" '.$onclick.' id="easy-cal-'.$rand.'-'.$diff.'-'.$key.'" axis="'.$diff.'"><div><span>'.$show.''.$final_price.'</span></div></td>'; $setet++; $diff++;
                    if($setet==0 || $setet==7 || $setet==14 || $setet==21 || $setet==28) echo '</tr>';
                } catch(Exception $e){
                    return false;
                }
            }

            if(($diff-1==$num && $setet/7 != $row_count) || $setet < 36){
                if($divider == 1) $calc=($row_count*7)-($setet+1);
                else $calc=42-($setet+1);
                for($countits=0; $countits < $calc+1; $countits++){
                    $fix = $countits==0 ? ' calendar-cell-lastfixer' : '';
                    if($setet+$countits==35){ echo '</tr><tr>'; $setet++; }
                    echo '<td class="calendar-cell calendar-cell-last'.$fix.'"><div>&nbsp;</div><span>'.($countits+1).'</span></td>';
                }
            }
            echo '</tr></tbody></table>';
        }

        echo '</td></tr></tbody></table>';
        exit;
    }

    /**
     *	Callback for the price calculation
     */

    public static function send_form(){
        if(isset($_POST['delete'])){
            if(!empty($_POST['delete'])){
                if(isset($_POST['cancel'])){
                    $explode = array(intval($_POST['cancel']));
                } else {
                    $explode = explode(',', sanitize_text_field($_POST['delete']));
                    unset($explode[count($explode)]);
                }

                foreach($explode as $id){
                    if(is_numeric($id)){
                        $res = new ER_Reservation((int) $id);
                        $res->deleteReservation();
                    }
                }
            }
        } else {
            if (!wp_verify_nonce(sanitize_text_field($_POST['easynonce']), 'easy-user-add' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__('Back', 'easyReservations').')</a>' );
            global $current_user;
            $error = '';

            if(isset($_POST['ids']) && !empty($_POST['ids'])) $ids = array_map('intval', $_POST['ids']);
            else $ids = array();
            $id_to_return = false;
            $price_to_return = false;

            if(isset($_POST['reservation-name'])){
                $arrival = isset($_POST['from']) ? ER_DateTime::createFromFormat( RESERVATIONS_DATE_FORMAT . ' H:i:s', sanitize_text_field($_POST['from']) . ' 00:00:00') : current_time( 'timestamp' );
                $name_form = isset($_POST['reservation-name']) ? sanitize_text_field($_POST['reservation-name']) : '';
                $persons = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
                $email = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
                $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
                $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';
                $resource = ER()->resources()->get(isset($_POST['resource']) ? intval($_POST['resource']) : false);
                $arrivalplus = 0;
                if(isset($_POST['date-from-hour'])) $arrivalplus += (int) $_POST['date-from-hour'] * 60;
                else $arrivalplus += 0;
                if(isset($_POST['date-from-min'])) $arrivalplus += (int) $_POST['date-from-min'];
                if($arrivalplus > 0) $arrivalplus = $arrivalplus * 60;
                $departureplus = 0;
                if(isset($_POST['date-to-hour'])) $departureplus += (int) $_POST['date-to-hour'] * 60;
                if(isset($_POST['date-to-min'])) $departureplus += (int) $_POST['date-to-min'];
                if($departureplus > 0) $departureplus = $departureplus*60;
                if(isset($_POST['to'])) $departure = ER_DateTime::createFromFormat( RESERVATIONS_DATE_FORMAT . ' H:i:s', $_POST['to'] . ' 00:00:00');
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

                if(isset($_POST['captcha']) && !empty($_POST['captcha'])){
                    require_once(RESERVATIONS_ABSPATH.'lib/captcha/captcha.php');
                    $captcha_instance = new easy_ReallySimpleCaptcha();
                    $correct = $captcha_instance->check(sanitize_text_field($_POST['captcha_prefix']), sanitize_text_field($_POST['captcha']));
                    $captcha_instance->cleanup(120); // delete all >1h old captchas image & .php file; is the submit a right place for this or should it be in admin?
                    if($correct != 1)	$error.=  '<li><label for="easy-form-captcha">'.sprintf(__('Please enter %s', 'easyReservations'), __('the correct captcha', 'easyReservations')).'</label></li>';
                }

                $current_user = wp_get_current_user();
                $array = array(
                    'name' => $name_form,
                    'email' => $email,
                    'arrival' => $arrival,
                    'departure' => $departure,
                    'resource' => $resource,
                    'space' => 0,
                    'country' => $country,
                    'adults' => $persons,
                    'children' => $children,
                    'reserved' => date('Y-m-d H:i:s', current_time( 'timestamp' )),
                    'status' => '',
                    'user' => $current_user->ID
                );

                if($_POST['slot'] > -1){
                    $array['slot'] = intval($_POST['slot']);
                }

                $custom = er_form_get_custom_submit();
                $customs = $custom[0];
                $error .= $custom[1];

                if(isset($_POST['edit'])){
                    $res = new ER_Reservation((int) $_POST['edit'], $array, false);
                    try {
                        $res->set_temporary_meta('custom', $customs);

                        $theID = $res->editReservation();
                        if(!$theID) {
                            echo json_encode(array($res->id, round($res->get_price(), RESERVATIONS_DECIMAL)));
                            exit;
                        }
                        else echo 'error';
                    } catch(Exception $e){
                        echo '<li><label>'.$e->getMessage().'</label></li>';
                        exit;
                    }
                } else {
                    $res = new ER_Reservation(false, $array, false);
                    try {
                        $res->coupon = false;
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

                        $theID = $res->addReservation(false, false, $ids);
                        if($theID){
                            foreach($theID as $key => $terror){
                                if($key%2==0) $error.=  '<li><label for="'.$terror.'">';
                                else $error .= $terror.'</label></li>';
                            }
                            echo $error;
                            exit;
                        }

                        $ids[] = $res->id;
                        $id_to_return = $res->id;

                        $price_to_return = round($res->get_price(), RESERVATIONS_DECIMAL);
                    } catch(Exception $e){
                        echo '<li><label>'.$e->getMessage().'</label></li>';
                        exit;
                    }
                }
            }

            if(!empty($ids)){
                if(isset($_POST['submit'])){
                    $prices = 0;
                    $final_form = '';
                    $atts = array_map( 'wp_filter_post_kses', $_POST['atts'] );

                    foreach($ids as $id){
                        $new = new ER_Reservation((int) $id);
                        $new->sendMail( 'reservations_email_to_admin', false);
                        $new->sendMail( 'reservations_email_to_user', $new->email);
                        do_action('reservation_successful_guest', $new);
                        $prices += $new->get_price();
                    }

                    $prices = round($prices, RESERVATIONS_DECIMAL);

                    $final_form.= '<div class="'.RESERVATIONS_STYLE.' border" id="easy_form_success">';
                    if(!empty($atts['submit'])) $final_form .= '<h1 class="easy_submit">'.$atts['submit'].'</h1>';
                    $final_form .= '<div class="easy-content">';
                    if(!empty($atts['subsubmit'])) $final_form .= '<span>'.$atts['subsubmit'].'</span>';
                    if($atts['price'] == 1) $final_form .= '<span class="easy_show_price_submit">'.__('Price','easyReservations').': <b>'.er_format_money($prices, 1).'</b></span>';
                    $final_form .= '</div>';

                    if(function_exists('easyreservations_generate_payment_form') && $atts['payment'] > 0){
                        $final_form .= easyreservations_generate_payment_form($ids, $prices, ($atts['payment'] == 2) ? true : false, (is_numeric($atts['discount']) && $atts['discount'] < 100) ? $atts['discount'] : false);
                    }

                    $final_form .= '</div>';
                    $script = get_option('easyreservations_successful_script');
                    if($script && !empty($script)) $final_form.= '<script type="text/javascript">'.stripslashes($script).'</script>';

                    echo json_encode(array($id_to_return, $price_to_return, $final_form));
                } else {
                    echo json_encode(array($id_to_return, $price_to_return, 2));
                }
            } else {
                echo '<li><label>'.__('Add reservations first', 'easyReservations').'</label></li>';
            }
        }
        exit;
    }

    /**
     *	Callback for the ajax validation (here it checks the values)
     *
     */

    public static function send_validate(){
        check_ajax_referer( 'easy-price', 'security' );
        $mode = sanitize_text_field($_POST['mode']);
        $error = array();

        $resource = ER()->resources()->get(intval($_POST['resource']));

        do_action('easyreservations_check_resource_availability', $resource->ID);
        if(!empty($_POST['from'])) $val_from = ER_DateTime::createFromFormat( RESERVATIONS_DATE_FORMAT . ' H:i:s', sanitize_text_field($_POST['from']) . ' 00:00:00');
        else $val_from = false;
        if(!$val_from instanceof DateTime){
            header( "Content-Type: application/json" );
            $error[] = 'easy-form-from';
            $error[] =  __('Wrong date format', 'easyReservations');
            echo json_encode($error);
            exit;
        }
        $real_from = ER_DateTime::addSeconds($val_from, intval($_POST['fromplus']));
        if(intval($_POST['toplus']) == -1) $_POST['toplus'] = 0;
        if(!empty($_POST['to'])){
            $val_to = ER_DateTime::createFromFormat( RESERVATIONS_DATE_FORMAT . ' H:i:s', sanitize_text_field($_POST['to']) . ' 00:00:00');
            if(!$val_to instanceof DateTime){
                $error[] = 'easy-form-to';
                $error[] =  __('Wrong date format', 'easyReservations');
                echo json_encode($error);
                exit;
            }
            $val_to = ER_DateTime::addSeconds($val_to, intval($_POST['toplus']));
        } elseif($_POST['nights'] !== '') {
            $interval = $resource->interval;
            if(isset($_POST['nights_interval']) && $_POST['nights_interval'] > 0) $interval = intval($_POST['nights_interval']);
            if($_POST['toplus'] > 0) $val_to = ER_DateTime::addSeconds($val_from, floatval($_POST['nights']) * $interval + intval($_POST['toplus']));
            else $val_to = ER_DateTime::addSeconds($val_from, floatval($_POST['nights']) * $interval + intval($_POST['fromplus']));
        } else {
            if((int) $_POST['toplus'] > 0)
                $val_to = ER_DateTime::addSeconds($val_from, intval($_POST['toplus'] + $resource->interval));
            else
                $val_to = ER_DateTime::addSeconds($val_from, $resource->interval);
        }

        if(isset($_POST['id']) && !empty($_POST['id'])) $id = $_POST['id'];
        else $id = false;
        try {
            $res = new ER_Reservation($id, array(
                'name' =>  sanitize_text_field($_POST['name']),
                'email' => sanitize_text_field($_POST['email']),
                'arrival' => $real_from,
                'departure' => $val_to,
                'resource' => $resource,
                'adults' => (int) $_POST['adults'],
                'children' => (int) $_POST['children'],
                'reserved' => current_time( 'timestamp' ),
                'status' => ''
            ), false);

            if($_POST['slot'] > -1){
                $res->slot = intval($_POST['slot']);
            }

            if(isset($_POST['ids'])) $ids = array_map('intval', $_POST['ids']);
            else $ids = false;

            $error = $res->Validate($mode, 1, $ids);
        } catch(Exception $e){
            $error[] = '';
            $error[] = $e->getMessage();
        }

        if($mode == 'send'){
            if(!empty($_POST['new_custom'])){
                $custom_fields = get_option('reservations_custom_fields');

                foreach($_POST['new_custom'] as $custom){
                    if(is_array($custom) && isset($custom['id']) && isset($custom['value']) && empty($custom['value'])){
                        $custom_id = intval($custom['id']);
                        if(isset($custom_fields['fields'][$custom_id]) && $custom_fields['fields'][$custom_id]['required']){
                            $error[] = 'easy-new-custom-'.$custom_id;
                            $error[] =  sprintf(__('%s is required', 'easyReservations'), $custom_fields['fields'][$custom_id]['title']);
                        }
                    }
                }
            }

            if($_POST['captcha'] !== 'x!'){
                if(empty($_POST['captcha'])){
                    $error[] = 'easy-form-captcha';
                    $error[] =  __('Captcha is required', 'easyReservations');
                } elseif(strlen($_POST['captcha']) != 4){
                    $error[] = 'easy-form-captcha';
                    $error[] =  __('Enter correct captcha', 'easyReservations');
                } else {
                    require_once(RESERVATIONS_ABSPATH.'lib/captcha/captcha.php');
                    $captcha_instance = new easy_ReallySimpleCaptcha();
                    $correct = $captcha_instance->check(sanitize_text_field($_POST['captcha_prefix']), sanitize_text_field($_POST['captcha']));
                    $captcha_instance->cleanup();
                    if($correct != 1){
                        $error[] = 'easy-form-captcha';
                        $error[] =  __('Enter correct captcha', 'easyReservations');
                    }
                }
            }
        }

        if( $error != '' ){
            header( "Content-Type: application/json" );
            echo json_encode($error);
        } else echo true;

        exit;
    }

    /**
     *	Callback for the price calculation (here it fakes a reservation and send it to calculation)
     *
     */

    public static function send_price(){
        check_ajax_referer( 'easy-price', 'security' );
        if(!isset($_POST['from']) || empty($_POST['from'])) $stop = 1;
        $resource = ER()->resources()->get(intval($_POST['resource']));

        $val_from = ER_DateTime::createFromFormat( RESERVATIONS_DATE_FORMAT . ' H:i:s', sanitize_text_field($_POST['from']) . ' 00:00:00');
        if(!$val_from instanceof DateTime) $stop = 1;

        $real_from = ER_DateTime::addSeconds($val_from, intval($_POST['fromplus']));
        if($_POST['toplus'] == -1) $_POST['toplus'] = 0;
        if(!empty($_POST['to'])){
            $val_to = ER_DateTime::createFromFormat( RESERVATIONS_DATE_FORMAT . ' H:i:s', sanitize_text_field($_POST['to']) . ' 00:00:00');
            $val_to = ER_DateTime::addSeconds($val_to, intval($_POST['toplus']));
        } elseif($_POST['nights'] !== '') {
            $interval = $resource->interval;
            if(isset($_POST['nights_interval']) && $_POST['nights_interval'] > 0) $interval = intval($_POST['nights_interval']);
            $val_to = ER_DateTime::addSeconds($val_from, floatval($_POST['nights']) * $interval + intval($_POST['toplus']) + intval($_POST['fromplus']));
        } else {
            if((int) $_POST['toplus'] > 0)
                $val_to = ER_DateTime::addSeconds($val_from, intval($_POST['toplus']) + $resource->interval);
            else
                $val_to = $real_from + $resource->interval;
        }
        if(isset($stop)){
            echo json_encode(array( er_format_money(0,1), 0));
            exit;
        }

        $email = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : 'test@test.de';
        $persons = isset($_POST['adults']) && !empty($_POST['adults']) ?  intval($_POST['adults']) : 1;
        $children = isset($_POST['children']) && !empty($_POST['children']) ?  intval($_POST['children']) : 0;

        $res_array = array('name' => 'abv', 'email' => $email, 'arrival' => $real_from,'departure' => $val_to,'resource' => $resource, 'adults' => (int) $persons, 'children' => $children, 'status' => '', 'reserved' => current_time( 'timestamp' ));

        $res = new ER_Reservation(false, $res_array, false);
        try {
            if($_POST['slot'] > -1){
                $res->slot = intval($_POST['slot']);
            }

            if(isset($_POST['coupon'])){
                $explode = explode(',', sanitize_text_field($_POST['coupon']));
                $coupons = array();
                foreach($explode as $coupon){
                    $coupons[] = array( 'value' => $coupon );
                }
                $res->set_temporary_meta('coupon', $coupons);
            }
            if(isset($_POST['new_custom']) && is_array($_POST['new_custom'])){
                foreach($_POST['new_custom'] as $custom){
                    if(isset($custom['id']) && isset($custom['value'])){
                        $res->add_temporary_meta('custom', array('id' => intval($custom['id']), 'value' => sanitize_text_field($custom['value'])));
                    }
                }
            }
            $res->get_price();

            $detailed = '';
            if(isset($_POST['receipt-atts']) && is_array($_POST['receipt-atts'])){
                $atts = array();
                foreach($_POST['receipt-atts'] as $key => $att){
                    $sanitized_key = strtolower(preg_replace('/([A-Z]+)/', "-$1", sanitize_text_field($key)));
                    $atts[$sanitized_key] = sanitize_text_field($att);
                }
                if(function_exists('easyreservations_receipt')){
                    $detailed = easyreservations_receipt($res, $atts);
                }
            }
            echo json_encode(array( er_format_money($res->price,1), round($res->price, RESERVATIONS_DECIMAL), $detailed));
        } catch(Exception $e){
            echo 'Error:'. $e->getMessage();
        }

        exit;
    }

    /**
     *	Table ajax callback
     */

    public static function send_table() {
        global $wpdb;
        check_ajax_referer( 'easy-table', 'security' );
        $zeichen = "AND departure > NOW() ";

        if(isset($_POST['typ'])) $typ = $_POST['typ'];
        else $typ = 'active';
        $custom_fields = get_option('reservations_custom_fields');

        $orderby = isset($_POST['orderby']) && !empty($_POST['orderby']) ? $_POST['orderby'] : '';
        $order = isset($_POST['order']) && !empty($_POST['order']) ? $_POST['order'] : '';
        $search = isset($_POST['search']) && !empty($_POST['search']) ? $_POST['search'] : '';
        $per_page = isset($_POST['perpage']) && !empty($_POST['perpage']) ? $_POST['perpage'] : get_option("reservations_on_page");
        $main_options = get_option("reservations_main_options");

        $table_options =  $main_options['table'];
        $regular_guest_array = er_get_important_guests();

        $selectors = '';
        if(!isset($table_options['table_fav']) || $table_options['table_fav'] == 1){
            global $current_user;
            $current_user = wp_get_current_user();
            $user = $current_user->ID;
            $favourite = get_user_meta($user, 'reservations-fav', true);
            if($favourite && !empty($favourite) && is_array($favourite)) $favourite_sql = 'id in('.implode(",", $favourite).')';
            else $favourite = array();
        }

        if($_POST['month_selector'] > 0){
            $month_selector = date("Y-m-d", strtotime(sanitize_text_field($_POST['month_selector'])));
            $selectors .= "AND MONTH('$month_selector') BETWEEN MONTH(arrival) AND MONTH(departure) ";
        }
        if($_POST['resource_selector'] > 0){
            $room_selector = intval($_POST['resource_selector']);
            $selectors .= "AND resource=$room_selector ";
        }
        if(isset($_POST['statusselector'] ) && !is_numeric($_POST['statusselector'])){
            $status_selector = $_POST['statusselector'];
            $selectors.="AND approve='$status_selector' ";
        }
        if($_POST['searchdate'] != ''){
            $search_date = $_POST['searchdate'];
            $search_date_mysql = ER_DateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $search_date.' 00:00:00')->format('Y-m-d');
            $selectors .= "AND ('$search_date_mysql' BETWEEN arrival AND departure OR DATE('$search_date_mysql') = DATE(arrival) OR DATE('$search_date_mysql') = DATE(departure)) ";
        }
        $rooms_sql  = '';
        $permission_selectors = '';
        if(!current_user_can('manage_options')){
            $accessible_resources = ER()->resources()->get_accessible();
            if(count($accessible_resources) > 0){
                $rooms_sql .= '( ';
                foreach($accessible_resources as $resource){
                    $rooms_sql .= " '$resource->ID', ";
                }
                $rooms_sql = substr( $rooms_sql,0,-2).' )';
            }
        }

        if(!empty($rooms_sql)) $permission_selectors.= ' AND resource in '.$rooms_sql;
        $orders = 'ASC';
        $orders_by = 'arrival';
        $search_str = '';

        if(!empty($search)){
            $explus = explode('+', $search);
            $exor = explode('|', $search);
            $st = 0;
            if(isset($explus[1])){
                $searches = $explus;
                $search_str .= 'AND (';
                $search_sign = 'AND';
            } elseif(isset($exor[1])){
                $searches = $exor;
                $search_str .= 'AND (';
                $search_sign = 'OR';
            } else {
                $search_str .= 'AND ';
                $searches = array($search);
            }

            $resources_array = ER()->resources()->get();
            foreach($searches as $search_res){
                if($st > 0)
                    $search_str .= ' '.(isset($search_sign) ? $search_sign : '').' ';
                if(preg_match('/^[0-9]+$/i', $search_res))
                    $search_str .= " id = $search_res";
                else {
                    $room_ids = '';
                    foreach($resources_array as $resource){
                        if(strpos(strtoupper(stripslashes($resource->post_title)), strtoupper($search_res)) !== false) $room_ids .= $resource->ID.', ';
                    }
                    $search_str .= "(name like %s OR email like %s)";
                }
                $st++;

            }
            if(isset($search_sign)) $search_str .= ')';
        }

        $esc_like = '%' . $wpdb->esc_like($search) . '%';
        $items1 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' $zeichen $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
        $items2 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='no' $zeichen $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
        $items3 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='' $zeichen $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
        $items4 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND departure < NOW() $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
        $items5 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='del' $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
        $items7 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND NOW() BETWEEN arrival AND departure $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
        $items6 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE 1=1 $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
        if(isset($favourite_sql)) $countfav = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE $favourite_sql $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
        else $favourite_sql = ' 1 = 1 ';
        if(!isset($typ) || $typ=='active' || $typ=='') { $type="approve='yes'"; $items=$items1; $orders="ASC";  $zeichen = "AND departure > NOW() "; } // If type is actice
        elseif($typ=="current") { $type="approve='yes'"; $items=$items7; $orders="ASC"; $zeichen ="AND NOW() BETWEEN arrival AND departure "; } // If type is current
        elseif($typ=="pending") { $type="approve=''"; $items=$items3; $orders_by='id'; $orders="DESC"; } // If type is pending
        elseif($typ=="deleted") { $type="approve='no'"; $items=$items2; } // If type is rejected
        elseif($typ=="old") { $type="approve='yes'"; $items=$items4; $zeichen="AND departure < DATE(NOW())";  } // If type is old
        elseif($typ=="trash") { $type="approve='del'"; $items=$items5; $zeichen=''; } // If type is trash
        elseif($typ=="all") { $type="1=1"; $items=$items6; $zeichen=''; } // If type is all
        elseif($typ=="favourite") { $type=$favourite_sql; $items=$countfav; $zeichen=''; } // If type is all
        if($order=="ASC") $orders="ASC";
        elseif($order=="DESC") $orders="DESC";
        if($orderby=="date") $orders_by="arrival";
        if($orderby=="persons") $orders_by="adults+(children*0.5)";
        if($orderby=="status") $orders_by="approve";
        elseif($orderby=="name") $orders_by="name";
        elseif($orderby=="resource"){
            $orders_by = "resource";
            $orders.=", space ".$orders;
        }
        elseif($orderby=="reserved") $orders_by="reserved";
        if(empty($orderby) && $typ=="pending") { $orders_by="id"; $orders="DESC"; }
        if(empty($orderby) && $typ=="old") { $orders_by="arrival"; $orders="DESC"; }
        if(empty($orderby) && $typ=="all") { $orders_by="arrival"; $orders="DESC"; }
        if(isset($month_selector) || isset($room_selector) || isset($status_selector)){
            $items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE $type $selectors $zeichen $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
        }
        if(!isset($room_selector)) $room_selector = '';
        if(!isset($status_selector)) $status_selector=0;
        $current_page = 1;
        if(isset($items) && $items > 0) {
            if ( isset( $_POST['paging'] ) ) {
                $current_page = intval( $_POST['paging'] );
            }
            $limit   = "LIMIT " . ( $current_page - 1 ) * $per_page . ", " . $per_page;
        } else $limit = 'LIMIT 0'; ?>
        <input type="hidden" id="easy_table_order" value="<?php echo $order;?>">
        <input type="hidden" id="easy_table_order_by" value="<?php echo $orderby;?>">
        <table class="easy-table-bar">
            <tr> <!-- Type Chooser //-->
                <td style="white-space:nowrap;width:auto;vertical-align: text-bottom" class="no-select" nowrap>
                    <ul id="easy-table-navi" class="subsubsub" style="float:left;white-space:nowrap">
                        <li><a onclick="easyreservations_send_table('active', 1)" <?php if(!isset($typ) || (isset($typ) && $typ == 'active')) echo 'class="current"'; ?> style="cursor:pointer"><?php _e('Upcoming', 'easyReservations');?><span class="count"> (<?php echo $items1; ?>)</span></a> |</li>
                        <li><a onclick="easyreservations_send_table('current', 1)" <?php if(isset($typ) && $typ == 'current') echo 'class="current"'; ?> style="cursor:pointer"><?php _e('Current', 'easyReservations');?><span class="count"> (<?php echo $items7; ?>)</span></a> |</li>
                        <li><a onclick="easyreservations_send_table('pending', 1)" <?php if(isset($typ) && $typ == 'pending') echo 'class="current"'; ?> style="cursor:pointer"><?php _e('Pending', 'easyReservations');?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
                        <li><a onclick="easyreservations_send_table('deleted', 1)" <?php if(isset($typ) && $typ == 'deleted') echo 'class="current"'; ?> style="cursor:pointer"><?php _e('Rejected', 'easyReservations');?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
                        <li><a onclick="easyreservations_send_table('all', 1)" <?php if(isset($typ) && $typ == 'all') echo 'class="current"'; ?> style="cursor:pointer"><?php _e('All', 'easyReservations');?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
                        <li><a onclick="easyreservations_send_table('old', 1)" <?php if(isset($typ) && $typ == 'old') echo 'class="current"'; ?> style="cursor:pointer"><?php _e('Old', 'easyReservations');?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
                        <?php if( $items5 > 0 ){ ?>| <li><a onclick="easyreservations_send_table('trash', <?php echo $current_page; ?>)" <?php if(isset($typ) && $typ == 'trash') echo 'class="current"'; ?> style="cursor:pointer"><?php echo ucfirst(__('trash', 'easyReservations'));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
                        <?php if( isset($countfav) && $countfav > 0 ){ ?><li>| <a onclick="easyreservations_send_table('favourite', <?php echo $current_page; ?>)" style="cursor:pointer"><img style="vertical-align:text-bottom" src="<?php echo RESERVATIONS_URL; ?>assets/css/images/star_full<?php if(isset($typ) && $typ == 'favourite') echo '_hover'; ?>.png"><span class="count"> (<span  id="fav-count"><?php echo $countfav; ?></span>)</span></a></li><?php } ?>
                    </ul>
                </td>
                <td style="text-align:center; font-size:12px;" id="idstatusbar" nowrap><!-- Begin of Filter //-->
                    <?php if($table_options['table_filter_offer'] == 1){?>
                        <span class="select">
          <select name="statusselector" id="easy-table-statusselector" class="postform" onchange="easyreservations_send_table('<?php echo $typ; ?>', 1)"><option value="0"><?php _e('View all states', 'easyReservations');?></option><option value="yes" <?php selected('yes', $status_selector) ?>><?php _e('Approved', 'easyReservations');?></option><option value=" <?php selected('', $status_selector) ?>"><?php _e('Pending', 'easyReservations');?></option><option value="no" <?php selected('no', $status_selector) ?> ><?php _e('Rejected', 'easyReservations');?></option><option value="del" <?php selected('del', $status_selector) ?>><?php _e('Trashed', 'easyReservations');?></option></select>
				</span>
                    <?php } if($table_options['table_filter_month'] == 1){ ?>
                        <span class="select">
						<select name="month_selector"  id="easy_table_month_selector" onchange="easyreservations_send_table('<?php echo $typ; ?>', 1)">
							<option value="0"><?php _e('Show all months', 'easyReservations');?></option>
                            <?php
                            $posts = "SELECT DISTINCT DATE_FORMAT(arrival, '%Y-%m') AS yearmonth FROM ".$wpdb->prefix ."reservations GROUP BY yearmonth ORDER BY yearmonth ";
                            $results = $wpdb->get_results($posts);
                            $datenames = er_date_get_label(1);

                            foreach( $results as $result ){
                                $dat=$result->yearmonth;
                                $zerst = explode("-",$dat);
                                $selected = isset($_POST['month_selector']) && $_POST['month_selector'] == $dat ? 'selected="selected"' : '';
                                echo '<option value="'.$dat.'" '.$selected.'>'.$datenames[$zerst[1]-1].' '.__($zerst[0]).'</option>';
                            } ?>
        </select></span>
                    <?php } if($table_options['table_filter_room'] == 1){ ?>
                        <span class="select">
          <select name="resource_selector" id="easy_table_resource_selector" class="postform" onchange="easyreservations_send_table('<?php echo $typ; ?>', 1)"><option value="0"><?php _e('View all Resources', 'easyReservations');?></option><?php echo er_form_resources_options($room_selector); ?></select>
				</span>
                    <?php } if($table_options['table_filter_days'] == 1){ ?>
                        <span class="input-wrapper">
					<input type="text" style="width:43px" id="easy-table-perpage-field" name="perpage" value="<?php echo $per_page; ?>" maxlength="3" onchange="easyreservations_send_table('<?php echo $typ; ?>', 1)">
					<span class="input-box"><span class="fa fa-list"></span></span>
				</span>
                    <?php } ?>
                </td>
                <td style="width:33%; margin-left: auto; margin-right:0; text-align:right;" nowrap>
                    <a id="easy-table-refreshimg" style="vertical-align:middle; font-size:14px" onclick="resetTableValues()" class="fa fa-refresh"></a>
                    <?php if($table_options['table_search'] == 1){ ?>
                        <span class="input-wrapper">
          <input type="text" onchange="easyreservations_send_table('all', 1)" style="width:100px;text-align:center" id="easy-table-search-date" value="<?php if(isset($search_date)) echo $search_date; ?>">
	        <span class="input-box clickable"><span class="fa fa-calendar"></span></span>
				</span>
                        <span class="input-wrapper">
          <input type="text" onchange="easyreservations_send_table('all', 1)" style="width:130px;" id="easy-table-search-field" name="search" value="<?php if(isset($search)) echo $search;?>" class="all-options">
	      </span>
                        <input class="easy-button grey" type="submit" value="<?php  _e('Search', 'easyReservations'); ?>" onclick="easyreservations_send_table('all', 1)">
                    <?php } ?>
                </td>
            </tr>
        </table>
        <form action="admin.php?page=reservations" method="get" name="frmAdd" id="frmAdd"><?php wp_nonce_field('easy-main-bulk'); ?>
            <table class="easy-reservations-table <?php echo RESERVATIONS_STYLE; ?> table" style="width:99%;"> <!-- Main Table //-->
                <thead> <!-- Main Table Header //-->
                <tr><?php $countrows = 1;
                    if($table_options['table_bulk'] == 1){ $countrows++; ?>
                        <th style="text-align:center"><input type="hidden" name="page" value="reservations"><label class="wrapper"><input type="checkbox" name="themainbulk" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')" style="margin-top:2px"><span class="input"></span></label></th>
                    <?php } if($table_options['table_from'] == 1){ $countrows++; ?>
                        <th colspan="2"><?php if($order=="ASC" and $orderby=="date") { ?><a class="asc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'date' )">
                                <?php } elseif($order=="DESC" and $orderby=="date") { ?><a class="desc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )">
                                    <?php } else { ?><a class="stand2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )"><?php } ?><?php _e('Date', 'easyReservations');?></a></th>
                    <?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ $countrows++; ?>
                        <th><?php if($order=="ASC" and $orderby=="name") { ?><a class="asc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'name' )">
                                <?php } elseif($order=="DESC" and $orderby=="name") { ?><a class="desc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )">
                                    <?php } else { ?><a class="stand2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )"><?php } ?><?php _e('Name', 'easyReservations');?></a></th>
                    <?php }  if($table_options['table_reservated'] == 1 || $table_options['table_reservated'] == 1){ $countrows++; ?>
                        <th style="text-align:center"><?php if($order=="ASC" and $orderby=="reservad") { ?><a class="asc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reserved' )">
                                <?php } elseif($order=="DESC" and $orderby=="reserved") { ?><a class="desc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'reserved' )">
                                    <?php } else { ?><a class="stand2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reserved' )"><?php } ?><?php _e('Reserved', 'easyReservations');?></a></th>
                    <?php }  if($table_options['table_status'] == 1){ $countrows++; ?>
                        <th style="text-align:center"><?php if($order=="ASC" and $orderby=="status") { ?><a class="asc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'status' )">
                                <?php } elseif($order=="DESC" and $orderby=="status") { ?><a class="desc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'status' )">
                                    <?php } else { ?><a class="stand2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'status' )"><?php } ?><?php _e('Status', 'easyReservations');?></a></th>
                    <?php } if($table_options['table_email'] == 1){ $countrows++; ?>
                        <th><?php _e('Email', 'easyReservations');?></th>
                    <?php } if($table_options['table_persons'] == 1){ $countrows++; ?>
                        <th style="text-align:center"><?php if($order=="ASC" and $orderby=="persons") { ?><a class="asc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'persons' )">
                                <?php } elseif($order=="DESC" and $orderby=="persons") { ?><a class="desc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'persons' )">
                                    <?php } else { ?><a class="stand2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'persons' )"><?php } ?><?php _e('Persons', 'easyReservations');?></a></th>
                    <?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){ $countrows++; ?>
                        <th><?php if($order=="ASC" and $orderby=="resource") { ?><a class="asc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'resource' )">
                                <?php } elseif($order=="DESC" and $orderby=="resource") { ?><a class="desc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'resource' )">
                                    <?php } else { ?><a class="stand2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'resource' )"><?php } ?><?php _e('Resource', 'easyReservations');?></a></th>
                    <?php }  if($table_options['table_country'] == 1){ $countrows++; ?>
                        <th><?php _e('Country', 'easyReservations'); ?></th>
                    <?php }  if($table_options['table_custom'] == 1){ $countrows++; ?>
                        <th><?php _e('Custom fields', 'easyReservations'); ?></th>
                    <?php }  if($table_options['table_price'] == 1){ $countrows++; ?>
                        <th style="text-align:right"><?php _e('Price', 'easyReservations');?></th>
                    <?php } ?>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <?php if($table_options['table_bulk'] == 1){ ?>
                        <td style="text-align:center"><input type="hidden" name="page" value="reservations"><label class="wrapper"><input type="checkbox" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')"><span class="input"></span></label></td>
                    <?php } if($table_options['table_from'] == 1){ ?>
                        <td colspan="2" style="text-align:left"><?php if($order=="ASC" and $orderby=="date") { ?><a class="asc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'date' )">
                                <?php } elseif($order=="DESC" and $orderby=="date") { ?><a class="desc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )">
                                    <?php } else { ?><a class="stand2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )"><?php } ?><?php _e('Date', 'easyReservations');?></a></td>
                    <?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ ?>
                        <td><?php if($order=="ASC" and $orderby=="name") { ?><a class="asc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'name' )">
                                <?php } elseif($order=="DESC" and $orderby=="name") { ?><a class="desc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )">
                                    <?php } else { ?><a class="stand2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )"><?php } ?><?php _e('Name', 'easyReservations');?></a></td>
                    <?php } if($table_options['table_reservated'] == 1 || $table_options['table_reservated'] == 1){ ?>
                        <td style="text-align:center"><?php if($order=="ASC" and $orderby=="reservad") { ?><a class="asc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reserved' )">
                                <?php } elseif($order=="DESC" and $orderby=="reserved") { ?><a class="desc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'reserved' )">
                                    <?php } else { ?><a class="stand2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reserved' )"><?php } ?><?php _e('Reserved', 'easyReservations');?></a></td>
                    <?php } if($table_options['table_status'] == 1){ ?>
                        <td style="text-align:center"><?php if($order=="ASC" and $orderby=="status") { ?><a class="asc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'status' )">
                                <?php } elseif($order=="DESC" and $orderby=="status") { ?><a class="desc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'status' )">
                                    <?php } else { ?><a class="stand2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'status' )"><?php } ?><?php _e('Status', 'easyReservations');?></a></td>
                    <?php } if($table_options['table_email'] == 1){ ?>
                        <td><?php _e('Email', 'easyReservations');?></td>
                    <?php } if($table_options['table_persons'] == 1){ ?>
                        <td style="text-align:center"><?php if($order=="ASC" and $orderby=="persons") { ?><a class="asc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'persons' )">
                                <?php } elseif($order=="DESC" and $orderby=="persons") { ?><a class="desc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'persons' )">
                                    <?php } else { ?><a class="stand2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'persons' )"><?php } ?><?php _e('Persons', 'easyReservations');?></a></td>
                    <?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){ ?>
                        <td><?php if($order=="ASC" and $orderby=="resource") { ?><a class="asc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'DESC', 'resource' )">
                                <?php } elseif($order=="DESC" and $orderby=="resource") { ?><a class="desc2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'resource' )">
                                    <?php } else { ?><a class="stand2" onclick="easyreservations_send_table('<?php echo $typ; ?>', 1, 'ASC', 'resource' )"><?php } ?><?php _e('Resource', 'easyReservations');?></a></td>
                    <?php }  if($table_options['table_country'] == 1){ ?>
                        <td><?php _e('Country', 'easyReservations'); ?></td>
                    <?php }  if($table_options['table_custom'] == 1){ ?>
                        <td><?php _e('Custom fields', 'easyReservations'); ?></td>
                    <?php }  if($table_options['table_price'] == 1){ ?>
                        <td style="text-align:right"><?php _e('Price', 'easyReservations');?></td>
                    <?php } ?>
                </tr>
                </tfoot>
                <tbody>
                <?php
                $nr=0;
                $export_ids = '';
                $sql = "SELECT * FROM ".$wpdb->prefix ."reservations
						WHERE $type $selectors $zeichen $search_str $permission_selectors ORDER BY $orders_by $orders $limit";  // Main Table query
                $result = $wpdb->get_results( $wpdb->prepare($sql, $esc_like, $esc_like, $esc_like));

                if(count($result) > 0 ){
                    foreach($result as $res){
                        $res = new ER_Reservation($res->id, (array) $res);
                        $class= $nr%2==0 ? 'alternate' : '';
                        $nr++;
                        $highlightClass = in_array($res->email, $regular_guest_array) ? 'highlighter' : '';
                        $export_ids .= $res->id.', ';

                        if(current_time( 'timestamp' ) - $res->arrival > 0 && current_time( 'timestamp' ) - $res->departure > 0) $sta = "color-blue";
                        elseif(current_time( 'timestamp' ) - $res->arrival > 0 && current_time( 'timestamp' ) - $res->departure <= 0) $sta = "color-green";
                        else $sta = "color-red";
                        if(isset($favourite)){
                            if(in_array($res->id, $favourite)){
                                $favclass = ' easy-fav';
                                $favid = 'fav-'.$res->id;
                                if($typ != 'favourite')$highlightClass = 'highlighter';
                            } else {
                                $favclass = ' easy-unfav';
                                $favid = 'unfav-'.$res->id;
                            }
                        } ?>
                        <tr class="<?php echo $class.' '.$highlightClass; ?>" height="47px"><!-- Main Table Body //-->
                            <?php if($table_options['table_bulk'] == 1 || isset($favourite)){ ?>
                                <td width="2%" style="vertical-align:middle;padding-right:0">
                                    <?php if($table_options['table_bulk'] == 1){ ?><label class="wrapper"><input name="bulkArr[]" id="bulkArr[]" type="checkbox" style="margin-left: 8px;" value="<?php echo $res->id;?>"><span class="input"></span></label><?php } ?>
                                    <?php if(isset($favourite)){ ?><div class="easy-favourite <?php echo $favclass; ?>" id="<?php echo $favid; ?>" onclick="easyreservations_send_fav(this)"> </div><?php } ?>
                                </td>
                            <?php } if($table_options['table_from'] == 1){
                                if(date('Y', $res->arrival) != date('Y') || date('Y', $res->departure) != date('Y')) $year = true; else $year = false; ?>
                                <td class="<?php echo $sta; ?>" style="width:24px;text-align: right;padding-right:5px">
                                    <div style="margin-bottom:5px;">
                <span style="font-weight: bold;font-size: 11px;text-align:right;">
	                <?php $round = round(($res->arrival-current_time( 'timestamp' ))/86400, 0); if($round > 0) $round = '+'.$round; if($round == 0) echo ' 0'; else echo $round;?>
                </span>
                                    </div>
                                    <div>
                <span style="font-weight: bold;font-size: 11px;text-align:right;">
	                <?php $round = round(($res->departure-current_time( 'timestamp' ))/86400, 0); if($round > 0) $round = '+'.$round; if($round == 0) echo ' 0'; else echo $round; ?>
                </span>
                                    </div>
                                </td>
                                <td class="<?php echo $sta; ?>" style="padding-left:0;width:70px;white-space: nowrap;">
                                    <div style="margin-bottom:5px;">
                                        <span style="color:#444;font-weight: bold;font-size:15px;"><?php echo date('d', $res->arrival); ?></span>
                                        <span style="color:#777;font-weight: bold;font-size: 13px;"><?php echo date('M', $res->arrival); ?></span>
                                        <?php if($year){ ?><span style="color:#777;font-weight: bold;font-size: 14px;"><?php echo date('Y', $res->arrival); ?></span><?php } ?>
                                        <?php if(RESERVATIONS_USE_TIME == 1){ ?><span style="color:#999;font-weight: bold;font-size: 11px;"><?php echo date('H:i', $res->arrival); ?></span><?php } ?>
                                    </div>
                                    <div>
                                        <span style="color:#444;font-weight: bold;font-size: 15px;"><?php echo date('d', $res->departure); ?></span>
                                        <span style="color:#777;font-weight: bold;font-size: 13px;"><?php echo date('M', $res->departure); ?></span>
                                        <?php if($year){ ?><span style="color:#777;font-weight: bold;font-size: 14px;"><?php echo date('Y', $res->departure); ?></span><?php } ?>
                                        <?php if(RESERVATIONS_USE_TIME == 1){ ?><span style="color:#999;font-weight: bold;font-size: 11px;"><?php echo date('H:i', $res->departure); ?></span><?php } ?>
                                    </div>
                                </td>
                            <?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ ?>
                                <td valign="top" class="row-title test" valign="top" nowrap>
                                    <b style="font-weight: bold">
                                        <?php if($table_options['table_name'] == 1){ ?>
                                            <a href="admin.php?page=reservations&view=<?php echo $res->id;?>"><?php echo $res->name;?></a>
                                        <?php } if($table_options['table_id'] == 1) echo ' (#'.$res->id.')'; ?>
                                    </b>
                                    <?php do_action('er_table_name_custom', $res); ?>
                                    <div class="test2" style="margin:8px 0 0 0;">
                                        <a href="admin.php?page=reservations&edit=<?php echo $res->id;?>"><?php _e('Edit', 'easyReservations');?></a>
                                        <?php if(isset($typ) && ($typ=="deleted" || $typ=="pending")) { ?>| <a class="color-green" href="admin.php?page=reservations&approve=<?php echo $res->id;?>"><?php _e('Approve', 'easyReservations');?></a>
                                        <?php } if(!isset($typ) || (isset($typ) && ($typ=="active" || $typ=="pending"))) { ?> | <a class="color-red" href="admin.php?page=reservations&delete=<?php echo $res->id;?>"><?php _e('Reject', 'easyReservations');?></a>
                                        <?php } if(isset($typ) && $typ=="trash") { ?>| <a href="admin.php?page=reservations&bulkArr[]=<?php echo $res->id;?>&bulk=2&_wpnonce=<?php echo wp_create_nonce('easy-main-bulk'); ?>"><?php _e('Restore', 'easyReservations');?></a> |
                                            <a style="color:#bc0b0b;" href="admin.php?page=reservations&easy-main-bulk=&bulkArr[]=<?php echo $res->id;?>&bulk=3&easy-main-bulk=<?php echo wp_create_nonce('easy-main-bulk'); ?>"><?php echo sprintf(__('Delete %s', 'easyReservations'), __('permanently', 'easyReservations')); ?></a>
                                        <?php } ?> |
                                        <a href="admin.php?page=reservations&sendmail=<?php echo $res->id;?>"><?php _e('Email', 'easyReservations');?></a>
                                    </div>
                                </td>
                            <?php } if($table_options['table_reservated'] == 1){ ?>
                                <td style="text-align:center"><?php echo human_time_diff( $res->reserved ) . ' ' . __('ago', 'easyReservations');?></td>
                            <?php } if($table_options['table_status'] == 1){ ?>
                                <td style="text-align:center;vertical-align: middle"><span class="easy-tag bg-<?php $status = $res->getStatus(); echo $status['color']; ?>"><?php echo $status['label'] ?></span></td>
                            <?php } if($table_options['table_email'] == 1){ ?>
                                <td><a href="admin.php?page=reservations&sendmail=<?php echo $res->id; ?>"><?php echo $res->email;?></a></td>
                            <?php } if($table_options['table_persons'] == 1){ ?>
                                <td style="text-align:center;color:#494949;!important;font-size:16px"><i class="fa fa-user" style="color:#cecece;font-size:18px"></i><?php echo $res->adults; ?> <i class="fa fa-child" style="color:#cecece;font-size:18px"></i><?php echo $res->children; ?></td>
                            <?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){  ?>
                                <td nowrap><?php if($table_options['table_room'] == 1) echo '<a href="admin.php?page=reservation-resources&room='.$res->resource->ID.'">'.__(stripslashes($res->resource->post_title)).'</a> '; if($table_options['table_exactly'] == 1 && isset($res->space)) echo '<b>' . $res->resource->get_space_name($res->space) . '</b>'; ?></td>
                            <?php }  if($table_options['table_country'] == 1){  ?>
                                <td nowrap><?php echo easyreservations_country_name( $res->country); ?></td>
                            <?php }  if($table_options['table_custom'] == 1){ ?>
                                <td><?php
                                    $customs = $res->get_meta('custom');
                                    if(!empty($customs)){
                                        foreach($customs as $custom){
                                            if(isset($custom_fields['fields'][$custom['id']])){
                                                $field = $custom_fields['fields'][$custom['id']];
                                                echo '<b>'.$field['title'].':</b> '.$res->getCustomsValue($custom);
                                                if(isset($field['price'])){
                                                    echo er_format_money(
                                                        $res->calculateCustom($custom['id'], $custom['value'], $customs),
                                                        1
                                                    );
                                                }
                                                echo '<br>';
                                            }
                                        }
                                    }
                                    ?></td>
                            <?php }  if($table_options['table_price'] == 1){ ?>
                                <td nowrap style="text-align:right">
                                    <div style="margin-bottom:6px;">
                                        <span style="font-weight: bold;font-size:12px;color:#555;;"><?php echo $res->formatPrice(true, 1); ?></span>
                                    </div>
                                    <div>
								<span style="font-weight: bold !important;font-size:12px;">
									<?php if($res->price == 0) echo 100; else echo round(100/$res->price*$res->paid, 0); ?>% <?php _e('Paid', 'easyReservations');?>
								</span>
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php }
                } else { ?> <!-- if no results form main quary !-->
                    <tr>
                        <td colspan="<?php echo $countrows; ?>"><b><?php _e('No reservations found', 'easyReservations');?></b></td> <!-- Mail Table Body if empty //-->
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <table  style="width:99%;">
                <tr>
                    <td style="width:33%;">
                        <?php if($table_options['table_bulk'] == 1){ ?>
                        <span class="select">
							<select name="bulk" id="bulk">
								<option select="selected" value="0"><?php _e('Bulk actions' ); ?></option>
                                <?php
                                if((isset($typ) AND $typ!="trash") OR !isset($typ)) { ?><option value="1"><?php _e('Move to trash', 'easyReservations');?></option><?php }
                                if(isset($typ) AND $typ=="trash") { ?><option value="2"><?php _e('Restore', 'easyReservations');?></option>
                                    <option value="3"><?php echo sprintf(__('Delete %s', 'easyReservations'), __('permanently', 'easyReservations')); ?></option><?php } ;?>
							</select>
						</span>
                        <input class="easy-button grey" type="submit" value="<?php _e('Apply', 'easyReservations');?>" /></form>
    <?php } ?>
        </td>
        <td style="width:33%;" class="tablenav" nowrap> <!-- Pagination  //-->
            <?php if ( $items > 0 ) { ?>
                <div class='tablenav-pages' style="text-align:center; width:100%;"><?php
                $pagination = paginate_links(array(
                    'base'               => '%_%',
                    'format'             => 'REPLACE%#%)',
                    'total'              => ceil( $items / $per_page ),
                    'current'            => $current_page,
                    'show_all'           => false,
                    'prev_next'          => false,
                    'prev_text'          => '«',
                    'next_text'          => '»',
                ));

                $pagination = str_replace( 'http://REPLACE', 'javascript:easyreservations_send_table("' . $typ . '",', $pagination );
                echo str_replace( "<a class='page-numbers' href=''>1</a>", "<a class='page-numbers' href='javascript:easyreservations_send_table(\"$typ\",1)'>1</a>", $pagination );

                ?></div><?php } ?>
        </td>
        <td style="width:33%;margin-left: auto;margin-right: 0;text-align: right;"> <!-- Num Elements //-->
            <span class="displaying-nums"><?php echo $nr;?> <?php _e('Elements', 'easyReservations');?></span>
        </td>
        </tr>
        </table>
        </form>
        <script type="text/javascript">
            jQuery(document).ready(function(){
                createTablePickers();
            });
            if(document.getElementById('easy-export-id-field')) document.getElementById('easy-export-id-field').value = '<?php echo $export_ids; ?>';
        </script><?php
        exit;
    }

    public static function send_fav(){
        check_ajax_referer( 'easy-favourite', 'security' );

        if(isset( $_POST['id'])){
            global $current_user;
            $current_user = wp_get_current_user();
            $user = $current_user->ID;
            $favourites = get_user_meta($user, 'reservations-fav', true);
            $save = $favourites;
            $id = intval($_POST['id']);
            $mode = sanitize_text_field($_POST['mode']);
            if(!is_array($favourites)){
                $favourites = array();
            }

            if(is_array($favourites) && $mode == 'add' && !in_array($id, $favourites)){
                $favourites[] = $id;
            } elseif(is_array($favourites) && $mode == 'del' && in_array($id, $favourites)){
                $key = array_search($id, $favourites);
                unset($favourites[$key]);
            }

            update_user_meta($user, 'reservations-fav', $favourites, $save);
        }
        die();
    }


    public static function get_custom(){
        //check_ajax_referer( 'easy-custom', 'security' );
        $custom_fields = get_option('reservations_custom_fields');
        $id = intval($_POST['id']);
        $field = er_generate_custom_field($id);
        echo json_encode(array($field, $custom_fields['fields'][$id]));
        die();
    }
}

ER_AJAX::init();
