<?php

//Prevent direct access to file
if(!defined('ABSPATH'))
	exit;


/**
 * Reservations Class
 * *
 * @author Feryaz Beer (support@easyreservations.org)
 * @version 1.0
 *
 * Usage:
 * new Reservation(52); //by ID, load from database
 * new Reservation(false, $array); //$array from foreach with all informations or custom one for new reservation
 */
if(!class_exists('ER_Reservation')) :

class ER_Reservation {
    public $id;
    public $arrival = 0;
    public $departure = 0;
    public $name = '';
    public $email = '';
    public $country = '';
    public $adults = 1;
    public $children = 0;
    public $meta = array();
    public $status = '';
    /**
     * @var ER_Resource
     */
    public $resource = 0;
    public $slot = -1;
    public $space = 0;
    public $reserved = 0;
    public $times = 1; // hours/days/weeks of reservation by resource interval
    public $user = 0; // reservation connected to wp_user; unused information
    public $price = null;
    public $paid = 0;
    public $history = array(); // If called Calculate(true); this will contain a calculation history
    public $admin = true;

    //----------------------------------------- Initialize -------------------------------------------------//

    /**
     * Construction
     * @param int/bool $id ID of reservation
     * @param array/bool $array reservation data
     * @param bool $admin false for frontend
     * @throws easyException #1, #2
     */
    public function __construct($id = false, $array = false, $admin = true){
        $this->id = $id;
        $this->admin = $admin;

        if($this->id && ($this->id < 1 || !is_numeric($this->id))){
            throw new easyException( 'ID must be Integer and > 0; ID: '.$this->id, 1 );
        } elseif($id && $array){
            $this->array_to_reservation($this->clean_sql($array));
        } elseif($this->id){
            $this->get_data($this->id);
        } elseif($array){
            if(isset($array[0]) && $array[0] == 'dontclean'){
                unset($array[0]);
                $this->array_to_reservation($array);
                return;
            } else {
                $this->array_to_reservation($this->clean_sql($array));
            }
        } else {
            throw new easyException( 'Need either reservations ID or array with information', 2 );
        }

        if($this->resource && is_numeric($this->resource)){
            $this->resource = ER()->resources()->get($this->resource);
        }
        $this->times = $this->getTimes();
    }

    private function get_data($id){
        global $wpdb;
        $reservation = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, name, approve, arrival, departure, resource, space, adults, children, country, email, price, paid, reserved, user FROM ".$wpdb->prefix ."reservations WHERE id=%d", $id
            )
        );

        if(isset($reservation[0]) && $reservation[0] && $reservation[0] !== 0){
            $this->array_to_reservation($this->clean_sql((array) $reservation[0]));
            return true;
        } else {
            throw new easyException( 'Reservation isn\'t existing ID: '.$id, 3 );
        }
    }

    /**
     * Change keys of database array to object informations names
     */
    private function clean_sql( $array){
        if(isset($array['approve'])) $array['status'] = $array['approve'];
        if(isset($array['arrival']) && !is_numeric($array['arrival'])) $array['arrival'] = strtotime($array['arrival']);
        if(isset($array['departure']) && !is_numeric($array['departure'])) $array['departure'] = strtotime($array['departure']);
        if(isset($array['reserved']) && !is_numeric($array['reserved'])) $array['reserved'] = strtotime($array['reserved']);
        unset($array['approve']);
        return $array;
    }

    /**
     * Informations from fake/db array to class data; check if resource exists; get resource interval
     * @throws easyException #4, #5
     */
    private function array_to_reservation( $array){
        if(!empty($array)){
            foreach($array as $key => $information){
                if(isset($this->$key) || in_array($key, array('fake', 'coupon', 'price'))) $this->$key = $information;
            }
        }
    }

    public function set_resource($resource){
        $this->resource = $resource;
        $this->times = $this->getTimes();

        $slot = get_reservation_meta($this->id, 'slot', true);
        if($slot){
            $this->slot = $slot;
        }
    }

    //----------------------------------------- Functions -------------------------------------------------//

    public function get_price(){
        if(is_null($this->price)){
            $this->price = $this->Calculate();
        }
        return $this->price;
    }

    public function get_history(){
        if(is_numeric($this->id) && $this->id > 0){
            $history = get_reservation_meta($this->id, 'history', true);
            if($history && !empty($history) && is_array($history)){
                return $history;
            }
        }
        if(empty($this->history)){
            $this->Calculate();
        }
        return $this->history;
    }

    /**
     *	Calculate reservation; access with $obj->price
     * @param bool $apply_taxes if taxes should be calculated
     * @return float calculated price
     */
    public function Calculate($apply_taxes = true){
        $interval = $this->resource->interval;
        $real_price = $this->price;
        $this->price = 0;
        $this->history = array();
        $filters = $this->resource->filter;
        $base_price = $this->resource->base_price;
        $taxes = $this->resource->taxes;
        if($this->departure == 0) $this->departure = $this->arrival + $interval;

        $general_settings = get_option("reservations_settings");
        $prices_include_tax = isset($general_settings['prices_include_tax']) ? $general_settings['prices_include_tax'] : 0;

        $date_array = array();
        $children_array = array();
        $children_price = 0;

        $times = $this->slot < 0 ? $this->times : 1;

        if(!empty($filters)){
            foreach($filters as $num => $filter){
                if($filter['type'] == 'price'){
                    if($this->Filter($filter)){
                        for($t = 0; $t < $times; $t++){
                            $i = $this->arrival + ($t * $this->resource->interval);
                            if($this->resource->once && count($this->history) > 0) break;
                            if((!in_array($i, $date_array) && !empty($filter['price'])) || (!in_array($i, $children_array) && isset($filter['children-price']) && !empty($filter['children-price']))){
                                if(!isset($filter['cond']) || (isset($filter['cond']) && $this->resource->time_condition($filter,$i))){
                                    if(isset($filter['children-price']) && !empty($filter['children-price']) && !in_array($i, $children_array)){
                                        if(strpos($filter['children-price'], '%') !== false) $amount = round($base_price/100*str_replace('%',  '', $filter['children-price']), RESERVATIONS_DECIMAL);
                                        else $amount = $filter['children-price'];
                                        $children_price += $amount;
                                        $children_array[] = $i;
                                    }
                                    if(!empty($filter['price']) && !in_array($i, $date_array)){
                                        if(strpos($filter['price'], '%') !== false) $amount = round($base_price/100*str_replace('%',  '', $filter['price']), RESERVATIONS_DECIMAL);
                                        else $amount = $filter['price'];
                                        $this->price += $amount;
                                        $date_array[] = $i;
                                        $this->history[] = array('date' => $i, 'price' => $amount, 'type' => 'filtered', 'name' => __($filter['name']));
                                    }
                                }
                            }
                        }
                    }
                    unset($filters[$num]);
                } else {
                    break;
                }
            }
        }

        if($this->slot < 0){
            while(count($this->history) < $this->times){
                if($this->resource->once && count($this->history) > 0) break;
                $this->price += $base_price;
                $ifDateHasToBeAdded=0;
                if(isset($date_array)){
                    $getrightday = true;
                    while($getrightday){
                        if(is_array($date_array) && !empty($date_array) && in_array($this->arrival+($ifDateHasToBeAdded*$interval), $date_array))
                            $ifDateHasToBeAdded++;
                        else
                            $getrightday = false;
                    }
                    $date_array[]=$this->arrival+($ifDateHasToBeAdded*$interval);
                }
                $this->history[] = array('date' => $this->arrival+($ifDateHasToBeAdded*$interval), 'price' => $base_price, 'type' => 'base');
            }

        } else {
            if(isset($this->resource->slots[$this->slot])){
                $slot = $this->resource->slots[$this->slot];

                if(empty($this->history)){
                    $base_price = floatval($slot['base-price']);
                    $children_price = floatval($slot['children-price']);

                    $this->price += $base_price;
                    $date_array[]=$this->arrival;
                    $this->history[] = array('date' => $this->arrival, 'price' => $base_price, 'type' => 'base', 'name' => __($slot['name']));
                }
            }
        }


        $checkprice = $this->price;
        if($this->resource->per_person == 1 && ($this->adults > 1 || $this->children > 0)){  // Calculate Price if  "Calculate per person"  was chosen
            if($this->adults > 1){
                $price_adults = $checkprice*$this->adults;
                $this->price += $price_adults-$checkprice;
                $this->history[] = array('date'=>$this->arrival+(count($this->history)*$interval), 'price'=>$price_adults-$checkprice,'type'=> 'adults', 'name' => $this->adults);
            }
            if( !empty($this->children) && $this->children > 0){
                $children_base_price = $this->resource->children_price;
                if($children_price !== 0 || ($children_base_price && !empty($children_base_price) && $children_base_price !== 0)){
                    if(substr($children_base_price, -1) == "%"){
                        $children_base_price = $checkprice/100*floatval(str_replace("%", "", $children_base_price));
                    }
                    if(!$this->resource->once) $children_price += floatval($children_base_price) * ($this->times - count($children_array));
                    elseif($children_price == 0) $children_price = floatval($children_base_price);

                    if($children_price !== 0){
                        $price_children = $children_price * $this->children;
                        $this->price += $price_children;
                        $this->history[] = array('date'=>$this->arrival+(count($this->history)*$interval), 'price'=>$price_children, 'type'=> 'children', 'name' => $this->children);
                    }
                }
            }
        }

        $checkprice = $this->price;
        if(!empty($filters)){
            $full = array();
            foreach($filters as $filter){
                if($this->Filter($filter, $full)){
                    $full[] = $filter['type'];
                    if(isset($filter['modus'])) $amount = $this->multiplyAmount($filter['modus'],$filter['price'],$checkprice);
                    else $amount = $filter['price'];
                    $this->price += $amount;
                    if(!isset($filter['cond'])) $filter['cond'] = '';
                    if($amount !== 0) $this->history[] = array('date'=>$this->arrival+(count($this->history)*$interval), 'price'=> $amount, 'type'=> 'filter-'.$filter['type'], 'name' => __($filter['name']), 'cond' => $filter['cond'] );
                }
            }
        }

        $custom_amount_total = 0;
        $res_custom_array = $this->get_meta('custom');
        if(!empty($res_custom_array)){
            $custom_fields = get_option('reservations_custom_fields');
            foreach($res_custom_array as $custom){
                if(isset($custom['id']) && isset($custom_fields['fields'][$custom['id']]) && isset($custom_fields['fields'][$custom['id']]['price'])){
                    $custom_field = $custom_fields['fields'][$custom['id']];
                    if(isset($custom_field['options'][$custom['value']]) || $custom_field['type'] == 'number'|| $custom_field['type'] == 'slider'){
                        $amount = $this->calculateCustom($custom['id'], $custom['value'], $res_custom_array);
                        if(($amount > 0 || $amount < 0)){
                            $this->history[] = array('date'=>$this->arrival+(count($this->history)*$interval), 'name' => $custom_field['title'], 'price' => $amount, 'type' => 'custom', 'id' => $custom['id'], 'value' => isset($custom_field['options'][$custom['value']]) ? $custom_field['options'][$custom['value']]['value'] : $custom['value']);
                        }
                        $custom_amount_total += $amount;
                    }
                }
            }
        }

        $this->price += $custom_amount_total;
        apply_filters('easy-calc-pricefields', $this);

        $checkprice = $this->price;
        $checkprice_both = 0;
        if(($apply_taxes || $prices_include_tax) && !empty($taxes)){
            $this->taxrate = 0;
            $this->taxamount = 0;
            foreach($taxes as $tax){
                $tax_amount = 0;

                if(!isset($tax[2]) || $tax[2] == 'both'){
                    if($checkprice_both == 0) $checkprice_both = $this->price;
                    $theprice = $checkprice_both;
                    $plus = 20;
                } elseif($tax[2] == 'stay'){
                    $theprice = $checkprice - $custom_amount_total;
                    $plus = 5;
                } elseif($tax[2] == 'prices'){
                    $theprice = $custom_amount_total;
                    $plus = 15;
                }
                if($prices_include_tax > 0){
                    foreach($this->history as $key => $entry){
                        if($entry['type'] == 'tax' || ($tax[2] == 'stay' && $entry['type'] == 'custom') || ($tax[2] == 'prices' && $entry['type'] !== 'custom')){
                            continue;
                        }
                        $taxe = $entry['price'] - ($entry['price'] / ( 1 + $tax[1] / 100 ));
                        if($apply_taxes){
                            $tax_amount += $taxe;
                        }
                        $this->history[$key]['price'] -= $taxe;
                    }
                } else {

                    $tax_amount = $theprice * ( $tax[1] / 100 );
                    $this->price += $tax_amount;
                }
                if($tax_amount !== 0){
                    $this->taxamount += $tax_amount;
                    $this->taxrate += $tax[1];
                    $this->history[] = array('date'=>$this->arrival+((count($this->history)+$plus)*$interval), 'price'=>$tax_amount, 'type' => 'tax', 'name' => __($tax[0]), 'amount' => $tax[1], 'class' => (isset($tax[2])) ? $tax[2] : 0);
                }
            }
        }

        if(!empty($this->history)){
            $dates = null;
            foreach($this->history as $key => $row) $dates[$key]  = $row['date'];
            array_multisort($dates, SORT_ASC, $this->history);
        }

        $this->price = round($this->price, RESERVATIONS_DECIMAL);

        $calculated_price = $this->price;
        $this->price = $real_price;

        return $calculated_price;
    }

    function calculateCustom($id, $selected, $all){
        $custom_fields = get_option('reservations_custom_fields');
        $amount = 0;

        if(isset($custom_fields['fields'][$id])){
            $field = $custom_fields['fields'][$id];
            if($field['type'] == 'number' || $field['type'] == 'slider'){
                $option = current($field['options']);

                $amount = $option['price'] * floatval($selected);

            } else {
                $option = $field['options'][$selected];
                $amount = $option['price'];
            }
            if(isset($option['clauses'])){
                $last_next = false;
                foreach($option['clauses'] as $clause){
                    $true = false;
                    if($last_next){
                        if($last_next[0] == "and" && !$last_next[1]){
                            if(is_numeric($clause['price'])) $last_next = false;
                            else $last_next[0] = $clause['price'];
                            continue;
                        }
                        if($last_next[0] == "or" && $last_next[1]){
                            $true = true;
                        }
                    }
                    if(!$true){
                        if($clause['type'] == 'field'){
                            if(isset($all['c'.$clause['operator']])){
                                if($clause['cond'] == "any" || $clause['cond'] == $all['c'.$clause['operator']]['value']) $true = true;
                            } else {
                                foreach($all as $filter){
                                    if(isset($filter['id']) && $filter['id'] == $clause['operator']){
                                        if($clause['cond'] == "any" || $clause['cond'] == $filter['value']){
                                            $true = true;
                                        }
                                        break;
                                    }
                                }
                            }
                        } else {
                            if($clause['type'] == 'resource') $comparator = $this->resource->ID;
                            elseif($clause['type'] == 'units') $comparator = $this->times;
                            elseif($clause['type'] == 'value') $comparator = floatval($selected);
                            elseif($clause['type'] == 'adult') $comparator = $this->adults;
                            elseif($clause['type'] == 'child') $comparator = $this->children;
                            elseif($clause['type'] == 'arrival'){
                                $comparator = date('Y-m-d', $this->arrival);
                                $clause['cond'] = date('Y-m-d', strtotime($clause['cond']));
                            } elseif($clause['type'] == 'departure'){
                                $comparator = date('Y-m-d', $this->departure);
                                $clause['cond'] = date('Y-m-d', strtotime($clause['cond']));
                            } elseif($clause['type'] == 'arrival_every'){
                                $comparator = date('m.d', $this->arrival);
                            } elseif($clause['type'] == 'departure_every'){
                                $comparator = date('m.d', $this->departure);
                            }
                            switch($clause['operator']){
                                case "equal":
                                    if($clause['cond'] == $comparator) $true = true;
                                    break;
                                case "notequal":
                                    if($clause['cond'] !== $comparator) $true = true;
                                    break;
                                case "greater":
                                    if($clause['cond'] < $comparator) $true = true;
                                    break;
                                case "greaterequal":
                                    if($clause['cond'] <= $comparator) $true = true;
                                    break;
                                case "smaller":
                                    if($clause['cond'] > $comparator) $true = true;
                                    break;
                                case "smallerequal":
                                    if($clause['cond'] >= $comparator) $true = true;
                                    break;
                            }
                        }
                    }

                    if($true){
                        if(is_numeric($clause['price'])){

                            if(($field['type'] == 'number' || $field['type'] == 'slider') && !isset($option['mode'])){
                                $amount = current($field['options']);
                            } else {
                                $amount = $clause['price'];
                            }

                            if(substr($amount, -1) == "%")
                                $amount = $this->price/100*str_replace("%", "", $amount);

                            if($clause['mult'] && $clause['mult'] !== 'x')
                                $amount = $this->multiplyAmount($clause['mult'], $amount);
                        }
                    }
                    if(is_numeric($clause['price'])) $last_next = false;
                    else $last_next = array($clause['price'], $true);
                }
            }
        }
        return $amount;
    }

    public function multiplyAmount($mode, $amount, $full = 0){
        if(!isset($mode) || !$mode || $mode == "price_res"){
            return $amount;
        } elseif($mode == "price_pers"){
            return $amount * ($this->adults + $this->children);
        } elseif($mode == "price_adul"){
            return $amount * $this->adults;
        } elseif($mode == "price_child"){
            return $amount * $this->children;
        } elseif($mode == "price_both"){
            return $amount * ($this->adults + $this->children) * $this->times;
        } elseif($mode == "price_day_adult"){
            return $amount * $this->adults * $this->times;
        } elseif($mode == "price_day_child"){
            return $amount * $this->children * $this->times;
        } elseif($mode == "price_halfhour"){
            return $amount *  $this->getTimes(1800, 0);
        } elseif($mode == "price_hour"){
            return $amount *  $this->getTimes(3600, 0);
        } elseif($mode == "price_realday"){
            return $amount *  $this->getTimes(86400, 0);
        } elseif($mode == "price_night"){
            return $amount *  $this->getTimes(86400, 3);
        } elseif($mode == "price_week"){
            return $amount *  $this->getTimes(604800, 0);
        } elseif($mode == "price_month"){
            return $amount *  $this->getTimes(2592000, 0);
        } elseif($mode == "price_day"){
            return $amount *  $this->times;
        } elseif($mode == '%' || $mode == 'price_perc'){
            return $full /100* (int) $amount;
        }
        return $amount;
    }

    /**
    * Check availability
    * @global obj $wpdb database connection
    * @param int $mode 0: returns number; 1: returns unavail dates string
    * @param bool $filter
    * @return int/string availability information
    */
    public function checkAvailability($mode = 0, $filter = true, $display_interval = false, $ids = false){
        global $wpdb;
        if($mode == 1) $error = array();
        else $error = 0;

        $settings = get_option( "reservations_settings" );
        $interval = $display_interval ? $display_interval : $this->resource->frequency;
        $times = $this->getTimes($interval);
        $res_number = '';
        $arrival = "arrival";
        $departure = "departure";
        $merge_res = 0;

        if($interval < 3601) $date_pattern = RESERVATIONS_DATE_FORMAT.' H:00';
        else $date_pattern = RESERVATIONS_DATE_FORMAT;

        if(isset( $settings['mergeres']) && is_array( $settings['mergeres']) && ($mode != 0 || !$this->admin)){
            if(isset( $settings['mergeres']['blockbefore']) && $settings['mergeres']['blockbefore'] > 0){
                $block_before = (int) $settings['mergeres']['blockbefore'] * 60;
                $arrival = "arrival - INTERVAL ".($block_before)." SECOND";
            }
            if(isset( $settings['mergeres']['blockafter']) && $settings['mergeres']['blockafter'] > 0){
                $block_after = (int) $settings['mergeres']['blockafter'] * 60;
                $departure = "departure + INTERVAL ".($block_after)." SECOND";
            }
            $merge_res = $settings['mergeres']['merge'];
        }

        $by_person = false;
        $by_person_amount = 0;
        if($merge_res > 0){
            $resource_quantity = $merge_res;
            $res_sql = '';
        } else {
            if( $this->space > 0) $res_number = " space='$this->space' AND";
            $res_sql = "resource='".$this->resource->ID."' AND";
            $resource_quantity = $this->resource->quantity;
            if($this->resource->availability_by !== 'unit'){
                if($this->resource->availability_by == 'pers'){
                    $by_person = 'adults+children';
                    $by_person_amount = $this->children + $this->adults;
                } elseif($this->resource->availability_by == 'adult'){
                    $by_person = 'adults';
                    $by_person_amount = $this->adults;
                } elseif($this->resource->availability_by == 'children'){
                    $by_person = 'children';
                    $by_person_amount = $this->children;
                }
            }
        }

        if($ids){
            $or = '';
            if(is_array($ids)){
                foreach($ids as $v) if(is_numeric($v)) $or .= $v.',';
                if(!empty($or)) $or = substr($or, 0, -1);
            } else {
                $or = $ids;
            }
            $approve = "(approve='yes' or id in ($or))";
        } else {
            $approve = "approve='yes'";
        }

        $approve = apply_filters('easy_reservation_availability_check_status', $approve);

        if($this->id) $id_sql = " id != '$this->id' AND";
        else $id_sql = '';
        if($filter) $error = $this->filter_availability($resource_quantity, $mode);
        if($mode < 3){
            if($mode == 0) {
                $date_to_check = date( "Y-m-d H:i:s", $this->arrival + 60 );
                $enddate       = date( "Y-m-d H:i:s", $this->departure - 60 );
                if( $by_person ) {
                    $prepare = $wpdb->prepare( "SELECT SUM($by_person) FROM " . $wpdb->prefix . "reservations WHERE $approve AND $res_sql $id_sql %s <= $departure AND %s >= $arrival", $date_to_check, $enddate );
                    $count   = $wpdb->get_var( $prepare );
                    if( $count == NULL || $count < 1 )
                        $count = 0;
                    $count = $count + $by_person_amount;
                    if( $count > $resource_quantity )
                        $error += $count;
                } else {
                    $sql   = $wpdb->prepare( "SELECT COUNT(DISTINCT space) FROM " . $wpdb->prefix . "reservations WHERE $approve AND $res_sql $res_number $id_sql %s <= $departure AND %s >= $arrival", $date_to_check, $enddate );
                    $count = $wpdb->get_var( $sql );
                    if( !empty( $res_number ) || $count >= $resource_quantity )
                        $error += $count;
                }
            } elseif($mode == 2){
                $eate = date("Y-m-d H:i:s", $this->arrival);
                $enddate = date("Y-m-d H:i:s", $this->departure);
                if($by_person){
                    $sql = $wpdb->prepare( "SELECT SUM(CASE WHEN DATE(arrival) = DATE(%s) THEN $by_person END) AS arrival, SUM(CASE WHEN DATE(departure) = DATE(%s) THEN $by_person END) AS departure, SUM($by_person) AS count_all, MAX(CASE WHEN DATE(arrival) = DATE(%s) THEN arrival END) AS max_arrival, MIN(CASE WHEN DATE(departure) = DATE(%s) THEN departure END) as min_departure FROM " . $wpdb->prefix . "reservations WHERE $approve AND $res_sql $id_sql %s <= $departure AND %s >= $arrival", $enddate, $enddate, $enddate, $enddate, date( "Y-m-d", $this->arrival ) . ' 00:00:00', date( "Y-m-d", $this->departure ) . ' 23:59:59' );
                    $results = $wpdb->get_results($sql);
                    $results[0]->filter = $error;
                    return $results[0];
                } else {
                    $sql = $wpdb->prepare( "SELECT COUNT(CASE WHEN DATE(arrival) = DATE(%s) THEN 1 END) AS arrival, COUNT(CASE WHEN DATE(departure) = DATE(%s) THEN 1 END) AS departure, COUNT(DISTINCT space) AS count_all, MAX(CASE WHEN DATE(arrival) = DATE(%s) THEN arrival END) AS max_arrival, MIN(CASE WHEN DATE(departure) = DATE(%s) THEN departure END) as min_departure FROM " . $wpdb->prefix . "reservations WHERE $approve AND $res_sql $id_sql %s <= $departure AND %s >= $arrival", $eate, $eate, $eate, $eate, date( "Y-m-d", $this->arrival ) . ' 00:00:00', date( "Y-m-d", $this->departure ) . ' 23:59:59' );
                    $results = $wpdb->get_results($sql);

                    $results[0]->filter = $error;
                    return $results[0];
                }
            } else {
                $excluded_spaces = array();
                for($t = 0; $t <= $times; $t++){
                    if($t == $times){
                        $i = $this->departure-61;
                    } else {
                        $i = $this->arrival + ($t*$interval);
                    }
                    $date_to_check = date("Y-m-d H:i:s", $i+60);

                    //TODO check
                    if($interval < 3600) $add_start = "AND TIME($departure) < TIME('$this->departure')";
                    elseif($interval < 3601) $add_start = "AND HOUR($departure) != HOUR('$this->departure')";
                    else $add_start = '';

                    if($by_person){
                        $count = $wpdb->get_var($wpdb->prepare("SELECT SUM($by_person) FROM ".$wpdb->prefix ."reservations WHERE $approve AND $res_sql $id_sql ((%s < $departure AND %s > $arrival)) $add_start", array($date_to_check, $date_to_check)));
                        if($count < 1) $count = 0;
                        $count = $count+$by_person_amount;
                        //$error[] = $wpdb->prepare("SELECT SUM($by_person) FROM ".$wpdb->prefix ."reservations WHERE $approve AND $res_sql $id_sql ((%s < $departure AND %s > $arrival)) $add_start", array($date_to_check, $date_to_check));
                        if($count > $resource_quantity) $error[] = $i;
                    } else {
                        $count = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT(space) as roomnumb, SUM(adults+children) as persosn FROM ".$wpdb->prefix ."reservations WHERE $approve AND $res_sql $id_sql ((%s < $departure AND %s > $arrival)) GROUP BY roomnumb", array($date_to_check, $date_to_check)));
                        $excluded_spaces = array_unique(array_merge($count, $excluded_spaces));
                        if(count($count) >= $resource_quantity || count($excluded_spaces) >= $resource_quantity) $error[] = $i;
                    }
                }
            }
        } else {
            //Check availability for one specific timeslot

            $arrival_possible_until = 23;
            if(isset($this->resource->requirements) && isset($this->resource->requirements['start-h'])){
                $arrival_possible_until = $this->resource->requirements['start-h'][1];
            }

            $date_to_check = date('Y-m-d H:i:s', $this->arrival);
            $departure_query = '';
            if($by_person){
                if($interval < 86400){
                    $query = "'$date_to_check' BETWEEN $arrival AND $departure - INTERVAL 1 SECOND";
                    $departure_query = " AND HOUR($departure) = HOUR('$date_to_check') AND TIMEDIFF($departure, '$date_to_check') < $interval";

                } else {
                    $query = "DATE('$date_to_check') BETWEEN DATE($arrival) AND DATE($departure)";
                    $query .= " AND (DATE($arrival) != DATE($departure) OR HOUR($departure) >= 11)";
                }

                $query .= " AND (DATE($departure) != DATE('$date_to_check')$departure_query OR HOUR($departure) >= $arrival_possible_until)";

                $count = $wpdb->get_var("SELECT sum($by_person) as count FROM ".$wpdb->prefix ."reservations WHERE $approve AND $res_sql $id_sql $query");
                if($mode == 5){
                    $error = array($error+$count, $error+$count);
                } else {
                    $count = $error+$count;
                }
            } else {
                $arrival_query = '';
                $hour_query = '';
                if($interval < 86400){
                    $query = "'$date_to_check' BETWEEN $arrival AND $departure - INTERVAL 1 SECOND";

                    if($interval < 3600){
                        $arrival_query = " AND HOUR($arrival) = HOUR('$date_to_check') AND TIMEDIFF($arrival, '$date_to_check') < $interval";
                        $departure_query = " AND HOUR($departure) = HOUR('$date_to_check') AND TIMEDIFF($departure, '$date_to_check') < $interval";
                        $hour_query =  " AND TIMEDIFF($departure, '$date_to_check') < $interval";
                    } else {
                        $arrival_query = " AND HOUR($arrival) = HOUR('$date_to_check')";
                        $departure_query = " AND HOUR($departure) = HOUR('$date_to_check')";
                        $hour_query =  " AND HOUR($arrival) != HOUR($departure)";
                    }
                } else {
                    $query = "DATE('$date_to_check') BETWEEN DATE($arrival) AND DATE($departure)";
                    $query .= " AND (DATE($arrival) != DATE($departure) OR HOUR($departure) > 11)";
                }
                $case = "Case When DATE($departure) = DATE('$date_to_check')$departure_query AND HOUR($departure) <= $arrival_possible_until Then 0 Else 1 End";
                $case_happens = "Case When DATE($departure) = DATE('$date_to_check')$departure_query AND DATE($departure) != DATE($arrival)$hour_query  THEN 1";
                $case_happens .= " When DATE($arrival) = DATE('$date_to_check')$arrival_query AND DATE($departure) != DATE($arrival)$hour_query THEN 1 ELSE 0 END";
                $case_shorts = "DATE($departure) = DATE($arrival) AND TIMESTAMPDIFF(SECOND,$arrival,$departure) < $interval ";

                $count = $wpdb->get_results("SELECT sum($case) as count,
                    sum($case_happens) as happens,
                    sum($case_shorts) as shorts
                    FROM ".$wpdb->prefix ."reservations WHERE $approve AND $res_sql $id_sql $query", ARRAY_A);

                if($mode == 5){
                    $error = array($error+$count[0]["count"], $count[0]["happens"], $count[0]["shorts"]);
                } else {
                    $count = $count[0]["count"];
                }
            }

            if($mode == 4 && $count >= $resource_quantity) $error += $count;
            elseif($mode == 3) $error += $count;
        }
        if($interval < 3600) $date_pattern = RESERVATIONS_DATE_FORMAT.' H:i';

        if(is_array($error) && empty($error)) $error = false;
        else {
            if($mode !== 5 && is_array($error)){
                $started = false;
                $string = '';
                foreach($error as $key => $date){
                    if(!$started){
                        $string .= date($date_pattern, $date).' -';
                        $started = true;
                    } elseif(!isset($error[$key+1]) || $error[$key+1] != $date+$interval){
                        $string .= ' '.date($date_pattern, $date).', ';
                        $started = false;
                    }
                }
                $error = $string;
            }
            if($mode == 1) $error = substr($error,0,-2);
        }
        return $error;
    }

    public function filter_availability($quantity = 1, $mode = 0){
        if($mode == 1) $error = array();
        else $error = 0;
        if(!empty($this->resource->filter)){
            foreach($this->resource->filter as $filter){
                if($filter['type'] == 'unavail'){
                    //TODO : availability departure? ??
                    for($i = 0; $i <= $this->times; $i++){
                        $date = $this->arrival + $i * $this->resource->interval;
                        $check = $this->resource->time_condition($filter, $date);
                        if($check){
                            if($mode == 1 && is_string($check)) $error .= $check;
                            elseif($mode == 1) $error[] = $date;
                            else $error += $quantity;
                        }
                    }
                }
            }
        }
        return $error;
    }

    private function Filter($filter, $full = false){
        if($filter['type'] == 'price'){
            if(isset($filter['cond'])) $time_cond = 'cond';
            if(isset($filter['basecond'])) $cond_cond = 'basecond';
            if(isset($filter['condtype'])) $cond_type = 'condtype';
        } elseif($filter['type'] == 'req' || $filter['type'] == 'unavail' ){
            return false;
        } else {
            if(isset($filter['timecond'])) $time_cond = 'timecond';
            if(isset($filter['cond'])) $cond_cond = 'cond';
            if(isset($filter['type'])) $cond_type = 'type';
        }

        if(isset($cond_cond) && isset($cond_type)){
            $discount_add = 0;
            if(!$full || empty($full) || (is_array($full) && !in_array($filter[$cond_type], $full))){
                if($filter[$cond_type] == 'stay'){
                    if((int) $filter[$cond_cond] <= (int) $this->times){
                        $discount_add = 1;
                    }
                } elseif($filter[$cond_type] == 'loyal'){// Loyal Filter
                    if(is_email($this->email)){
                        global $wpdb;
                        $items1 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND email='%s'",$this->email)); //number of total rows in the database
                        if($filter[$cond_cond] <= $items1){
                            $discount_add = 1;
                        }
                    }
                } elseif($filter[$cond_type] == 'pers'){// Persons Filter
                    if($filter[$cond_cond] <= ($this->adults + $this->children)){
                        $discount_add = 1;
                    }
                } elseif($filter[$cond_type] == 'adul'){
                    if($filter[$cond_cond] <= $this->adults){
                        $discount_add = 1;
                    }
                } elseif($filter[$cond_type] == 'child'){
                    if($filter[$cond_cond] <= $this->children){
                        $discount_add = 1;
                    }
                } elseif($filter[$cond_type] == 'early'){// Early Bird Discount Filter
                    if( $this->reserved == 0) $this->reserved = current_time( 'timestamp' );
                    $dayBetween=round( ($this->arrival-$this->reserved) / $this->resource->interval,2);
                    if($filter[$cond_cond] <= $dayBetween){
                        $discount_add = 1;
                    }
                }
            }
            if($discount_add == 0) return false;
        }

        $use_filter = false;
        if($filter['type'] == 'price'){
        } elseif(isset($time_cond)){
            if($this->resource->time_condition($filter, $this->arrival, $time_cond)){
                $use_filter = true;
            }
            if(isset($use_filter)) return $use_filter;
        }
        return true;
    }

    public function getCustomsValue($custom){
        $custom_fields = get_option('reservations_custom_fields');
        $return = $custom['value'];
        if(isset($custom['id']) && isset($custom_fields['fields'][$custom['id']])){
            $custom_field = $custom_fields['fields'][$custom['id']];
            if($custom_field['type'] == 'check') return $custom_field['title'];
            elseif($custom_field['type'] !== 'text' && $custom_field['type'] !== 'area' ){
                if(isset($custom_field['options']) && isset($custom_field['options'][$custom['value']])){
                    return $custom_field['options'][$custom['value']]['value'];
                }
            }
        }
        return $return;
    }

    function formatPrice($color_paid = false, $display_currency = true, $amount = false){
        if(!$amount && $amount !== 0) $amount = $this->get_price();
        $price = er_format_money($amount, $display_currency ? 1 : 0);

        if($color_paid){
            if($this->paid == $this->price) $class = 'color-green';
            elseif($this->paid > $this->price) $class = 'color-purple';
            elseif($this->paid > 0) $class = 'color-orange';
            else $class = 'color-red';
            $price = '<b class="'.$class.'" style="padding:1px;font-weight:bold !important;">'.$price.'</b>';
        }
        return $price;
    }

    public function getTimes($interval = false, $billing_method = false){
        $interval = $interval ? $interval : $this->resource->interval;
        $diff = 0;
        if( version_compare( PHP_VERSION, '5.3.0' ) >= 0 && is_numeric( $this->departure ) ) {
            $timezone    = new DateTimeZone( date_default_timezone_get() );
            $transitions = $timezone->getTransitions( $this->arrival, $this->departure );
            if( isset( $transitions[1] ) && $transitions[0]['offset'] != $transitions[1]['offset'] ) {
                if( $transitions[0]['offset'] > $transitions[1]['offset'] ) {
                    $diff = $transitions[0]['offset'] - $transitions[1]['offset'];
                } else $diff = $transitions[1]['offset'] - $transitions[0]['offset'];
            }
        }
        if(!$billing_method){
            $billing_method = $this->resource->billing_method;
        }
        $number = ( $this->departure - $this->arrival - $diff ) / $interval;
        if( $billing_method == 0 ) {
            $times = is_numeric( $number )  ? ceil( ceil( $number / 0.01 ) * 0.01 )  : false;
        } elseif( $billing_method == 3 ) {
            $date1 = new DateTime( date( "d.m.Y", $this->arrival ) );
            $date2 = new DateTime( date( "d.m.Y", $this->departure ) );
            $times = intval( $date2->diff( $date1 )->format( "%a" ) );
        } else {
            $times = floor( $number );
        }
        return $times < 1 ? 1 : $times;
    }

    public function Validate($mode = 'send', $avail = 1, $ids = false){
        $errors = array();
        $this->name = trim($this->name);
        if(strlen($this->name) > 50 || ($mode == 'send' && (empty($this->name) || strlen($this->name) <= 1))){
            if(!$this->admin) $errors[] = 'easy-form-name';
            $errors[] = sprintf(__('Please enter %s', 'easyReservations'), __('your name', 'easyReservations'));
        }

        $this->email = trim($this->email);
        if($mode == 'send'  && (!is_email( $this->email) || empty($this->email))){
            if(!$this->admin) $errors[] = 'easy-form-email';
            $errors[] =  sprintf(__('Please enter %s', 'easyReservations'), __('a valid email address', 'easyReservations'));
        }

        if($this->departure < 1000000 ||  $this->arrival < 1000000){
            if(!$this->admin) $errors[] = 'date';
            $errors[] =  sprintf(__('Please enter %s', 'easyReservations'), __('arrival and departure as valid date format', 'easyReservations'));
            $daterror = true;
        }

        if($this->departure < $this->arrival){
            if(!$this->admin)  $errors[] = 'easy-form-to';
            $errors[] = __('The departure date has to be after the arrival date', 'easyReservations');
            $daterror = true;
        }

        if(!is_numeric($this->adults) || $this->adults < 1){
            if(!$this->admin)  $errors[] = 'easy-form-adults';
            $errors[]  = sprintf(__('%s has to be numeric', 'easyReservations'), __('Adults', 'easyReservations'));
        }

        if(!is_numeric($this->children)){
            if(!$this->admin)  $errors[] = 'easy-form-children';
            $errors[]  = sprintf(__('%s has to be numeric', 'easyReservations'), __('Children', 'easyReservations'));
        }

        if(!isset($daterror)){
            $availability = $this->checkAvailability($avail, ($this->admin) ? false : true, false, $ids);

            if($availability){
                if(!$this->admin){
                    $errors[] = 'date';
                    if($avail > 0) $errors[] = __('Not available at', 'easyReservations').' '.$availability;
                    else $errors[] = __('Selected time is occupied', 'easyReservations');
                } else $errors[] = __('Selected time is occupied', 'easyReservations');
            }
        }

        if(!$this->admin){
            if($this->arrival < current_time( 'timestamp' )-86400){ /* check arrival Date */
                $errors[] = 'easy-form-from';
                $errors[] = __('The arrival date has to be in the future', 'easyReservations');
            }
            if($this->slot < 0){
                $checked = false;

                if($this->resource->filter && !empty($this->resource->filter)){
                    foreach($this->resource->filter as $filter){
                        if($filter['type'] == 'req'){
                            if($this->resource->time_condition($filter, $this->arrival)){
                                $checked = true;
                                $errors = $this->checkRequirements($filter['req'], $errors);
                                if(!empty($errors)) return $errors;
                            }
                        }
                    }
                }
                if(!$checked){
                    $resource_req = $this->resource->requirements;
                    if(!$resource_req || !is_array($resource_req)) $resource_req = array('nights-min' => 0, 'nights-max' => 0, 'pers-min' => 1, 'pers-max' => 0);
                    $errors = $this->checkRequirements($resource_req, $errors);
                }
            } else {
                //TODO: SLOTS REQUIREMENTS
            }
        }
        if(empty($errors)) return false;
        else return $errors;
    }

    private function checkRequirements( $req, $errors){
        if( $req['pers-min'] > ($this->adults+$this->children)){
            $errors[] = 'easy-form-adults';
            $errors[] =  sprintf(__('At least %1$s persons in %2$s', 'easyReservations'), $req['pers-min'], __($this->resource->post_title));
        }
        if( $req['pers-max'] > 0 && $req['pers-max'] < ($this->adults+$this->children)){
            $errors[] = 'easy-form-adults';
            $errors[] =  sprintf(__('Maximum %1$s persons in %2$s', 'easyReservations'), $req['pers-max'], __($this->resource->post_title));
        }
        if( $req['nights-min'] > $this->times){
            $errors[] = 'date';
            $errors[] =  sprintf(
                __('At least %1$s %2$s in %3$s', 'easyReservations'),
                $req['nights-min'],
                er_date_get_interval_label($this->resource->interval, $req['nights-min']),
                __($this->resource->post_title)
            );
        }
        if( $req['nights-max'] > 0 && $req['nights-max'] < $this->times){
            $errors[] = 'date';
            $errors[] = sprintf(
                __( 'Maximum %1$s %2$s in %3$s', 'easyReservations' ),
                $req['nights-max'],
                er_date_get_interval_label( $this->resource->interval, $req['nights-max'] ),
                __( $this->resource->post_title )
            );
        }
        $day_names = er_date_get_label(0, 3);
        if(isset( $req['start-on'])){
            if( $req['start-on'] == 8) {
                $errors[] = 'easy-form-from';
                $errors[] = sprintf(
                    __('Arrival not possible on %s', 'easyReservations'),
                    date(RESERVATIONS_DATE_FORMAT_SHOW, $this->arrival)
                );
            } elseif( !empty($req['start-on']) && !in_array(date("N", $this->arrival), $req['start-on'])){
                $errors[] = 'easy-form-from';
                $start_days = '';
                foreach( $req['start-on'] as $starts){
                    $start_days .= $day_names[$starts-1].', ';
                }
                $errors[] = sprintf(__('Arrival only possible on %s', 'easyReservations'), substr($start_days,0,-2));
            }
        }
        if(isset( $req['end-on'])){
            if( $req['end-on'] == 8) {
                $errors[] = 'easy-form-to';
                $errors[] = sprintf(__('Departure not possible on %s', 'easyReservations'), date(RESERVATIONS_DATE_FORMAT_SHOW, $this->departure));
            } elseif( !empty($req['end-on']) && !in_array(date("N", $this->departure), $req['end-on'])){
                $errors[] = 'easy-form-to';
                $end_days = '';
                foreach( $req['end-on'] as $ends){
                    $end_days .= $day_names[$ends-1].', ';
                }
                $errors[] = sprintf(__('Departure only possible on %s', 'easyReservations'), substr($end_days,0,-2));
            }
        }
        $zero = strtotime('20.10.2010 00:00:00');
        if( isset( $req['start-h'] ) && is_array( $req['start-h'] ) ) {
            if( date( "G", $this->arrival ) < $req['start-h'][0] ) {
                $errors[] = 'easy-form-from';
                $errors[] = sprintf(
                    __( 'Arrival only possible from %s', 'easyReservations' ),
                    date( RESERVATIONS_TIME_FORMAT, $zero + ( $req['start-h'][0] * 3600 ) )
                );
            }
            if( date( "G", $this->arrival ) > $req['start-h'][1] ) {
                $errors[] = 'easy-form-from';
                $errors[] = sprintf(
                    __( 'Arrival only possible till %s', 'easyReservations' ),
                    date( RESERVATIONS_TIME_FORMAT, $zero + ( $req['start-h'][1] * 3600 ) )
                );
            }
        }
        if( isset( $req['end-h'] ) && is_array( $req['end-h'] ) ) {
            if( date( "G.i", $this->departure ) < $req['end-h'][0] ) {
                $errors[] = 'easy-form-to';
                $errors[] = sprintf(
                    __( 'Departure only possible from %s', 'easyReservations' ),
                    date( RESERVATIONS_TIME_FORMAT, $zero + ( $req['end-h'][0] * 3600 ) )
                );
            }
            if(date("G.i", $this->departure) > $req['end-h'][1]){
                $errors[] = 'easy-form-to';
                $errors[] = sprintf(
                    __( 'Departure only possible till %s', 'easyReservations' ),
                    date( RESERVATIONS_TIME_FORMAT, $zero + ( $req['end-h'][1] * 3600 ) )
                );
            }
        }
        return $errors;
    }

    /**
     * Send Mail
     * @param string $options_name name of email option
     * @param string $to (optional) Receiver's email - default: $this->email
     * @param string $attachment (optional) URL of Attachment - default: false
     * @return bool true on success
     */
    public function sendMail($options_name, $to = false, $attachment = false){
        if(is_array($options_name)){
            $option = $options_name;
            $options_name = 'none';
        } else $option = get_option($options_name);

        if(isset($option['active']) && $option['active'] == 1){
            $theForm = $option['msg'];
            $subj = $option['subj'];
            $local = false;
            if(isset($_POST['easy-set-local'])){
                $oldlocal = get_locale();
                $local = $_POST['easy-set-local'];
                setlocale(LC_TIME, $local);
            }
            if(isset($_POST["approve_message"]) && !empty($_POST["approve_message"])) $theForm = stripslashes($_POST["approve_message"]).'-!DIVMESSAGE!-'.$theForm;
            $theForm = $theForm.'-!DIVSUBJECT!-'.$subj;

            $tags = er_form_template_parser($theForm, true);
            foreach($tags as $fields){
                $field=shortcode_parse_atts( $fields);
                if(!isset($field[0])) continue;
                if($field[0]=="adminmessage"){
                    $explode = explode('-!DIVMESSAGE!-',$theForm);
                    if(isset($explode[1])){
                        $message = $explode[0];
                        $theForm = $explode[1];
                    } elseif(isset($_POST["approve_message"])){
                        $message = $_POST["approve_message"];
                    }
                    $theForm=preg_replace('/\['.$fields.']/U', $message, $theForm);
                } elseif($field[0]=="taxes"){
                    $theForm=str_replace('[taxes]', er_format_money($this->taxamount), $theForm);
                } elseif($field[0]=="coupon"){
                    $theForm=str_replace('[coupon]', $this->coupon, $theForm);
                } elseif($field[0]=="editlink"){
                    $the_link = get_option("reservations_edit_url");
                    if(!empty($the_link)){
                        $nonce =  substr(wp_hash(wp_nonce_tick() .'|easyusereditlink|0', 'nonce'), -12, 10);
                        $the_edit_link = trim($the_link).'?edit&id='.$this->id.'&email='.urlencode($this->email).'&ernonce='.$nonce;
                        $theForm = str_replace('[editlink]', $the_edit_link, $theForm);
                    } else $theForm = str_replace('[editlink]', '', $theForm);
                } elseif($field[0]=="paypal"){
                    $link = '';
                    if(function_exists('easyreservations_generate_paypal_button')){
                        $percent = false;
                        $price = $this->price;
                        if(isset($field[1]) && is_numeric($field[1])) $percent = $field[1];
                        elseif(isset($field[1]) && $field[1] == "due") $price = $this->price - $this->paid;
                        $link = easyreservations_generate_paypal_button($this, $price, true, true, $percent);
                        if(isset($field['title'])) $link = '<a href="'.$link.'">'.str_replace('"', '', $field['title']).'</a>';
                    }
                    $theForm = str_replace('['.$fields.']', $link, $theForm);
                } else {
                    $theForm = str_replace('['.$fields.']', er_reservation_parse_tag($field, $this), $theForm);
                }
            }

            $explode = explode('-!DIVSUBJECT!-', $theForm);
            $theForm = apply_filters( 'easy_email_content', $explode[0], $local);
            $subj = apply_filters( 'easy_email_content', $explode[1], $local);
            $support_mail = get_option("reservations_support_mail");

            if(function_exists('easyreservations_send_multipart_mail')) $msg = easyreservations_send_multipart_mail($theForm);
            else {
                $theForm = explode('<--HTML-->', $theForm);
                $msg = htmlspecialchars_decode(str_replace('<br>', "\n",str_replace(']', '',  str_replace('[', '', $theForm[0]))));
            }

            if(empty($support_mail)) return false;
            elseif(is_array($support_mail)) $send_from = $support_mail[0];
            else{
                if(preg_match('/[\,]/', $support_mail)){
                    $support_mail  = explode(',', $support_mail);
                    $send_from = $support_mail[0];
                } else $send_from = $support_mail;
            }

            $headers = "From: \"".str_replace(array(','), array(''), get_bloginfo('name'))."\" <".$send_from.">\n";
            if(!$attachment && function_exists('easyreservations_insert_attachment')) $attachment = easyreservations_insert_attachment($this, str_replace('reservations_email_', '', $options_name));

            if(!$to || empty($to)){
                $to = $support_mail;
                $headers = "From: \"".$this->name."\" <".$this->email.">\n";
            }

            $mail = @wp_mail($to,$subj,$msg,$headers,$attachment);
            if(isset($oldlocal)) setlocale(LC_TIME, $oldlocal);
            if($attachment) unlink($attachment);
            return $mail;
        }
        return false;
    }

    public function getStatus($color = false){
        $all = array(
            'yes' => array(
                'label' => __('approved', 'easyReservations'),
                'color' => 'green'
            ),
            'no' => array(
                'label' => __('rejected', 'easyReservations'),
                'color' => 'red'
            ),
            'del' => array(
                'label' => __('trashed', 'easyReservations'),
                'color' => 'purple'
            ),
            '' => array(
                'label' => __('pending', 'easyReservations'),
                'color' => 'lightblue'
            )
        );

        $status = $all[$this->status];
        if($color) $status = '<b class="color-'.$all[$this->status]['color'].'" style="text-transform:capitalize">'.$status['label'].'</b>';

        return apply_filters('easy-status-out', $status, $this);
    }




    //----------------------------------------- Save -------------------------------------------------//

    public function add_temporary_meta($type, $value){
        if(!isset($this->meta[$type])){
            $this->meta[$type] = array();
        }
        $this->meta[$type][] = $value;
    }

    public function set_temporary_meta($type, $value){
        $this->meta[$type] = $value;
    }

    private function save_temporary_meta(){
        foreach($this->meta as $key => $meta_data){
            delete_reservation_meta($this->id, $key);
            foreach($meta_data as $meta){
                add_reservation_meta($this->id, $key, $meta);
            }
        }
    }

    public function get_meta($type){
        if(isset($this->meta[$type])){
            return $this->meta[$type];
        } else {
            return get_reservation_meta($this->id, $type);
        }
    }

    public function get_single_meta($type, $key_to_find){
        if(isset($this->meta[$type]) && isset($this->meta[$type][$key_to_find])){
            return $this->meta[$type][$key_to_find];
        } else {
            return $this->_get_single_meta($type, $key_to_find);
        }
    }

    private function _get_single_meta($type, $key_to_find){
        $meta = get_reservation_meta($this->id, $type);
        foreach($meta as $key => $value){
            if($key == $key_to_find){
                return $value;
            }
        }

        return '';
    }

    function update_single_meta($type, $key, $value){
        $current_value = $this->_get_single_meta($type, $key);
        if($current_value){
            return update_reservation_meta($this->id, $type, $value, $current_value);
        }
        return false;
    }

    public function delete_single_meta($type, $key){
        $current_value = $this->_get_single_meta($type, $key);
        if($current_value){
            return delete_reservation_meta($this->id, $type, $current_value);
        }
        return false;
    }

    /**
     * Call to edit reservation
     * @param array $data data to edit
     * @param bool $validate false to not validate reservation
     * @param bool/string $mail (optional) name of email's option
     * @return bool true on success
     */
    public function editReservation($data = array('all'), $validate = true, $mail = false, $to = false){
        if(is_array($data) && !empty($data)){
            $array = array();
            if($data[0] == 'all') $data = array('name', 'email', 'arrival', 'departure', 'resource', 'space', 'adults', 'children', 'country', 'status', 'reserved', 'user', 'price', 'paid');
            foreach($data as $key){
                if(isset($this->$key)) $array[$key] = $this->$key;
            }
            if($this->admin && $this->status !== 'yes') $theval = false;
            else $theval = $this->Validate('send', 0);
            if(!$validate || !$theval){
                $edit = $this->edit($this->ReservationToArray($array));
                if($this->slot > -1){
                    update_reservation_meta($this->id, 'slot', $this->slot, true );
                }

                if($mail && !$edit){
                    if(!is_array($mail)) $mail = array($mail);
                    if(!is_array($to)) $to = array($to);
                    foreach($mail as $key => $themail) if($to[$key] !== true)  $this->sendMail($mail[$key], $to[$key]);
                }
                return $edit;
            } else return $theval;
        }
    }

    /**
     * Call to add reservation
     * @param mixed $mail (optional) name of email's option
     * @return int ID of new reservation
     */
    public function addReservation($mail = false, $to = false, $ids = false){
        if($this->admin && $this->status !== 'yes') $validate = false;
        else $validate = $this->Validate('send', 1, $ids);
        if(!$validate){
            $this->get_price();

            $array = array();
            foreach($this as $key => $information){
                $array[$key] = $information;
            }

            $add = $this->add($this->ReservationToArray($array));
            if(!$add){
                add_reservation_meta($this->id, 'history', $this->history, true );
                if($this->slot > -1){
                    add_reservation_meta($this->id, 'slot', $this->slot, true );
                }
                if($mail){
                    if(!is_array($mail)) $mail = array($mail);
                    if(!is_array($to)) $to = array($to);
                    foreach($mail as $key => $the_mail) if($to[$key] !== true) $this->sendMail($mail[$key], $to[$key]);
                }
            }
            return $add;
        } else return $validate;
    }

    /**
     *	Object array to database array
     * @param array $array the array to edit
     */
    private function ReservationToArray($array){
        if(isset($array['custom']) && is_array($array['custom'])) $array['custom'] = maybe_serialize($array['custom']);
        if(isset($array['resource'])) $array['resource'] = $array['resource']->ID;
        if(isset($array['status'])) $array['approve'] = $array['status'];
        if(isset($array['reserved'])) $array['reserved'] = date('Y-m-d H:i:s', $array['reserved']);
        if(isset($array['arrival'])) $array['arrival'] = date('Y-m-d H:i:s', $array['arrival']);
        if(isset($array['departure'])) $array['departure'] = date('Y-m-d H:i:s', $array['departure']);
        if(isset($array['price']) && is_null($array['price'])) $array['price'] = 0;
        if(isset($array['paid']) && is_null($array['paid'])) $array['paid'] = 0;
        unset($array['status']);
        return $array;
    }

    /**
     * Edit reservation
     *
     * @global obj $wpdb database connection
     * @return bool true on success
     * @throws easyException mysql error
     */
    private function edit($array){
        if(!empty($array)){
            global $wpdb;
            $sql = array();
            foreach($array as $key => $info){
                $sql[$key] = $info;
            }
            $return = $wpdb->update( $wpdb->prefix.'reservations', $sql, array('id' => $this->id));
            if(!$return && !is_numeric($return)){
                throw new easyException( 'Reservation could not be edited. Error: '.$wpdb->last_error );
                return true;
            } else {
                $this->save_temporary_meta();
                return false;
            }
        }
    }

    /**
     * Add reservation
     *
     * @global obj $wpdb database connection
     * @return int ID of new reservation
     * @throws easyException mysql error
     */
    private function add($array){
        global $wpdb;
        $keys = array('arrival', 'name', 'email', 'departure', 'resource', 'space', 'adults', 'children', 'country', 'approve', 'reserved', 'user', 'price', 'paid');
        $rarray = array();
        foreach($array as $key => $info){
            if(!in_array($key, $keys)) unset($array[$key]);
            else {
                $rarray[$key] = $info;
            }
        }
        $return = $wpdb->insert( $wpdb->prefix.'reservations', $rarray);
        if(!$return){
            var_dump($wpdb->print_error());
            throw new easyException( 'Reservation could not be added. Error: '.$wpdb->last_error );
            return true;
        } else {
            $this->id = $wpdb->insert_id;
            $this->save_temporary_meta();
            if(!$this->admin) do_action('easy_reservation_add', $this, 1);
            return false;
        }
    }

    public function deleteReservation(){
        global $wpdb;
        $return =$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."reservations WHERE id='%d'", $this->id) );
        if(!$return){
            throw new easyException( 'Reservation couldn\'t be deleted. Error: '.$wpdb->last_error );
            return true;
        }
    }
}

endif;

class easyException extends Exception {}
?>