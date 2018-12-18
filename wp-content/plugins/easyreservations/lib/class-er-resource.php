<?php

class ER_Resource {
	public $ID;
	public $post_title;
	public $post_name;
	public $post_content;
	public $post_excerpt;
	public $menu_order = 0;
	public $interval = 86400;
	public $frequency = 86400;
	public $quantity = 1;
	public $base_price = 0;
	public $children_price = 0;
	public $billing_method = 0;
	public $permission = '';
	public $per_person = 0;
	public $once = 0;
	public $taxes = null;
	public $requirements = null;
	public $availability_by = 'unit';
	public $filter = false;
	public $slots = false;

	public function __construct( $post_data ) {
		$this->ID           = $post_data->ID;
		$this->post_title   = __( $post_data->post_title );
		$this->menu_order   = $post_data->menu_order;
		$this->post_name    = $post_data->post_name;
		$this->post_content = $post_data->post_content;
		$this->post_excerpt = $post_data->post_excerpt;

		$this->interval       = intval(get_post_meta( $this->ID, 'easy-resource-interval', true ));
        $this->interval = max(1800, $this->interval);
		$this->base_price     = get_post_meta( $this->ID, 'reservations_groundprice', true );
		$this->children_price = get_post_meta( $this->ID, 'reservations_child_price', true );
		$this->billing_method = get_post_meta( $this->ID, 'easy-resource-billing-method', true );
		$this->permission     = get_post_meta( $this->ID, 'easy-resource-permission', true );
		$this->taxes          = get_post_meta( $this->ID, 'easy-resource-taxes', true );
		$this->requirements   = get_post_meta( $this->ID, 'easy-resource-req', true );
		$this->filter         = get_post_meta( $this->ID, 'easy_res_filter', true );
		$this->frequency      = get_post_meta( $this->ID, 'er_resource_frequency', true );
        if(empty($this->frequency)){
            $this->frequency = min(86400, $this->interval);
        }

		$slots = get_post_meta( $this->ID, 'easy-resource-slots', true );
		if( $slots && ! empty( $slots ) && is_array( $slots ) ) {
			$this->slots = $slots;
		}

		$quantity = get_post_meta( $this->ID, 'roomcount', true );
		if( is_array( $quantity ) ) {
			$this->availability_by = $quantity[1];
			$quantity              = $quantity[0];
		}
		$this->quantity = $quantity;

		$price_rules = get_post_meta( $this->ID, 'easy-resource-price', true );
		if( ! $price_rules || ! is_array( $price_rules ) ) {
			$price_rules = array( $price_rules, 0 );
		}

		$this->per_person = $price_rules[0];
		$this->once       = $price_rules[1];
	}

	public function get_billing_unit($arrival, $departure, $limit = false){
        $number = ($departure-$arrival) / ($limit ? $this->frequency : $this->interval);
        $significance = 0.01;
        return ( is_numeric($number)) ? (ceil(ceil($number/$significance)*$significance)) : false;
    }

	public function get_space_name($space){
		$space = $space - 1;
		if(empty($space) && $space < 0) return $space;
		$resource_space_names = get_post_meta($this->ID, 'easy-resource-roomnames', TRUE);
		if(isset($resource_space_names[$space]) && !empty($resource_space_names[$space])){
			return __($resource_space_names[$space]);
		}	else {
			return $space+1;
		}
	}

	public function get_spaces_options($selected = false, $add_resource_to_value = false){
		$resource_space_names = get_post_meta($this->ID, 'easy-resource-roomnames', TRUE);
		$options = '';
		for($i=0; $i < $this->quantity; $i++){
			$name = isset($resource_space_names[$i]) && !empty($resource_space_names[$i]) ? __($resource_space_names[$i]) : $i+1;
			$selected = $selected && $selected == $i+1 ? 'selected="selected"' : '';
			$options .= '<option value="'.($add_resource_to_value ? $this->ID.'-' : '').''.($i+1).'" '.$selected.'>'.addslashes($name).'</option>';
		}
		return $options;
	}

    public function time_condition($filter, $time, $cond = 'cond'){
        if($filter[$cond] == 'unit'){
            if(!$this->unit_condition($filter, $time)) return false;
        }
        if(isset($filter['from'])){
            if(isset($filter['every'])){
                $filter['from'] = date('m-d', $filter['from']);
                $filter['to']   = date('m-d', $filter['to']);
                $time           = date('m-d', $time);

                $start_ts = strtotime('2000-'.$filter['from']);
                $end_ts = strtotime('2000-'.$filter['to']);
                $test_ts = strtotime( '2000-' . $time);

                if ($start_ts > $end_ts) {
                    $end_ts = strtotime('2001-'.$filter['to']);
                    if( $time < $filter['from'] ){
                        $test_ts = strtotime( '2001-' . $time);
                    }
                }
                return $test_ts >= $start_ts && $test_ts <= $end_ts;
            }
            if( $time >= $filter['from'] && $time <= $filter['to'] ){
                return true;
            } else return false;
        }
        return true;
    }

    private function unit_condition($filter, $time){
        if(!isset($filter['year']) || empty($filter['year']) || in_array(date("Y", $time), explode(",", $filter['year']))){
            if(!isset($filter['quarter']) || empty($filter['quarter']) || in_array(ceil( date("m", $time) / 3), explode(",", $filter['quarter']))){
                if(!isset($filter['month']) || empty($filter['month']) || in_array(date("n", $time), explode(",", $filter['month']))){
                    if(!isset($filter['cw']) || empty($filter['cw']) || in_array(date("W", $time), explode(",", $filter['cw']))){
                        if(!isset($filter['day']) || empty($filter['day']) || in_array(date("N", $time), explode(",", $filter['day']))){
                            if(!isset($filter['hour']) || empty($filter['hour']) || in_array(date("H", $time), explode(",", $filter['hour']))){
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
}

?>