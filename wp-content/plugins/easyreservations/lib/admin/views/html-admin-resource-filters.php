<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function easyreservations_get_filter_description($filter, $resource, $type){
	$price = 0;
	if(isset($filter['price'])) {
		$price = er_format_money( $filter['price'], 1 );
		if(isset($filter['children-price'])) $price .= '<br>'.er_format_money( $filter['children-price'], 1 );
	}

	$interval_label = er_date_get_interval_label($resource->interval, 1, true);

	if($filter['type'] == 'price'){
		if(isset($filter['cond'])) $timecond = 'cond';
		if(isset($filter['basecond'])) $condcond = 'basecond';
		if(isset($filter['condtype'])) $condtype = 'condtype';
		$explain = __('the base price changes to', 'easyReservations');
	} elseif($filter['type'] == 'req' || $filter['type'] == 'unavail' ){
		$timecond = 'cond';
		$explain = '';
	} else {
		if(isset($filter['timecond'])) $timecond = 'timecond';
		if(isset($filter['cond'])) $condcond = 'cond';
		if(isset($filter['type'])) $condtype = 'type';
		if(isset($filter['modus'])){
			$price .= ' '.__('each','easyReservations').' <br>';
			if($filter['modus']=='%') $price = $filter['price'].' %';
			elseif($filter['modus']=='price_res') $price .= __('Reservation','easyReservations');
			elseif($filter['modus']=='price_day') $price .= er_date_get_interval_label($resource->interval, 1, true);
			elseif($filter['modus']=='price_pers') $price .= ucfirst(__('person','easyReservations'));
			elseif($filter['modus']=='price_both') $price .= ucfirst(__('person','easyReservations')).' '.__('and','easyReservations').' '.$interval_label;
			elseif($filter['modus']=='price_adul') $price .= ucfirst(__('adult','easyReservations'));
			elseif($filter['modus']=='price_day_adult') $price .= ucfirst(__('adult','easyReservations')).' '.__('and','easyReservations').' '.$interval_label;
			elseif($filter['modus']=='price_day_child') $price .= ucfirst(__('children','easyReservations')).' '.__('and','easyReservations').' '.$interval_label;
			elseif($filter['modus']=='price_child') $price .= ucfirst(__('children','easyReservations'));
		}
		if((int) $filter['price'] >= 0) $explain = __('the price increases by', 'easyReservations');
		else $explain = __('the price decreases by', 'easyReservations');
	}

	if(isset($timecond)){
		$full = false;
		if($filter['type'] == 'price') {
			$the_condition = sprintf( __( "If %s to calculate is ", "easyReservations" ), strtolower( $interval_label ) );
		} elseif($filter['type'] == 'unavail') {
			$the_condition = __("Resource is unavailable ", "easyReservations");
		} else {
			$the_condition = __("If arrival is ", "easyReservations");
		}
		if(isset($filter['from'])){
			$the_condition .= ' '.sprintf(__('between %1$s and %2$s', 'easyReservations'), '<b>'.date(RESERVATIONS_DATE_FORMAT_SHOW, $filter['from']).'</b>', '<b>'.date(RESERVATIONS_DATE_FORMAT_SHOW, $filter['to']).'</b>', er_date_get_interval_label($resource->interval, 1), 0 ,1 );
			$full = true;
		}
		if($filter[$timecond] == 'unit') {
			if(isset($filter['hour']) && !empty($filter['hour'])){
				$timecondition = '';
				$times = explode(',', $filter['hour']);
				foreach($times as $time){
					$timecondition .= $time.'h, ';
				}
			}
			if(!empty($filter['day'])){
				$daycondition = '';
				$days = explode(',', $filter['day']);
				$daynames= er_date_get_label(0, 3);
				foreach($days as $day){
					$daycondition .= $daynames[$day-1].', ';
				}
			}
			if(!empty($filter['cw'])) $cwcondition = $filter['cw'];
			if(!empty($filter['month'])){
				$monthcondition = '';
				$months = explode(',', $filter['month']);
				$monthesnames= er_date_get_label(1, 3);
				foreach($months as $month){
					$monthcondition .= $monthesnames[$month-1].', ';
				}
			}
			if(!empty($filter['quarter'])) $qcondition = $filter['quarter'];
			if(!empty($filter['year'])) $ycondition = $filter['year'];
			$itcondtion = '';
			if(isset($timecondition) && $timecondition != '') $itcondtion .= sprintf(__('at %s', 'easyReservations')," <b>".substr($timecondition, 0, -2).'</b> ').'</b> '.__('and', 'easyReservations').' ';
			if(isset($daycondition) && $daycondition != '') $itcondtion .= '<b>'.substr($daycondition, 0, -2).'</b> '.__('and', 'easyReservations').' ';
			if(isset($cwcondition) && $cwcondition != '') $itcondtion .= sprintf(__('in %s', 'easyReservations'),__('calendar week', 'easyReservations'))." <b>".$cwcondition.'</b> '.__('and', 'easyReservations').' ';
			if(isset($monthcondition) && $monthcondition != '') $itcondtion .= sprintf(__('in %s', 'easyReservations')," <b>".substr($monthcondition, 0, -2).'</b> ').__('and', 'easyReservations').' ';
			if(isset($qcondition) && $qcondition != '') $itcondtion .= sprintf(__('in %s', 'easyReservations'),__('quarter', 'easyReservations'))." <b>".$qcondition.'</b> '.__('and', 'easyReservations').' ';
			if(isset($ycondition) && $ycondition != '') $itcondtion .= sprintf(__('in %s', 'easyReservations')," <b>".$ycondition.'</b> ').__('and', 'easyReservations').' ';
			if($full) $the_condition.=' '.__('and', 'easyReservations');
			$the_condition .= ' '.substr($itcondtion, 0, -4);
		}
	}
	$bg_color='#F4AA33';

	if(isset($condcond)){
		if($filter[$condtype]=="stay"){
			$type = __('Duration','easyReservations');
			$bg_color='#1CA0E1';
			$condition_string = sprintf(__('guest stays %s days or more','easyReservations'), '<b>'.$filter[$condcond].'</b>');
		} elseif($filter[$condtype] =="pers"){
			$type = ucfirst(__('person','easyReservations'));
			$bg_color='#3059C1';
			$condition_string = sprintf(__('%s or more persons reserve','easyReservations'), '<b>'.$filter[$condcond].'</b>');
		} elseif($filter[$condtype] =="adul"){
			$type = ucfirst(__('adult','easyReservations'));
			$bg_color='#3059C1';
			$condition_string = sprintf(__('%s or more adults reserve','easyReservations'), '<b>'.$filter[$condcond].'</b>');
		} elseif($filter[$condtype] =="child"){
			$type = ucfirst(__('children','easyReservations'));
			$bg_color='#3059C1';
			$condition_string = sprintf(__('%s or more children reserve','easyReservations'), '<b>'.$filter[$condcond].'</b>');
		} elseif($filter[$condtype] =="loyal"){
			$type = __('Loyal','easyReservations');
			$bg_color='#A823A8';
			if($filter[$condcond] == 1) $end = 'st';
			elseif($filter[$condcond] == 2) $end = 'nd';
			elseif($filter[$condcond] == 3) $end = 'rd';
			else $end = 'th';
			$condition_string = sprintf(__('guest comes the %1$s%2$s time','easyReservations'), '<b>'.$filter[$condcond].'</b>', $end);
		} elseif($filter[$condtype]=="early"){
			$type = __('Early bird','easyReservations');
			$bg_color='#F4AA33';
			$condition_string = sprintf(__('the guest reserves %s before his arrival','easyReservations'), '<b>'.$filter[$condcond].'</b> '.$interval_label);
		}
		if(isset($condition_string)){
			if(!empty($the_condition)) $the_condition = $the_condition.' '.__('and','easyReservations').'<br>'.strtolower($condition_string);
			else $the_condition = __('If','easyReservations').' '.$condition_string;
		}
	}
	if($filter['type'] == 'price') {
		$type     = __('Price', 'easyReservations');
		$bg_color = '#30B24A';
	} elseif($filter['type']=="discount"){
		$bg_color='#F4AA33';
		$type = __('Discount','easyReservations');
	} elseif($filter['type']=="charge"){
		$bg_color='#F4AA33';
		$type = __('Extra charge','easyReservations');
	} elseif($filter['type']=="unavail"){
		$bg_color='#F4AA33';
		$type = __('Unavailability','easyReservations');
	}

	return array('<code style="color:'.$bg_color.';font-weight:bold;display:inline-block">'.$type.'</code>', $the_condition.' '.$explain, $price);
}
?>
<table class="<?php echo RESERVATIONS_STYLE; ?> table" style="width: 100%">
	<thead>
		<tr>
			<th><?php _e('Filter', 'easyReservations'); ?></th>
			<th style="text-align:center;"><?php _e('Priority', 'easyReservations'); ?></th>
			<th><?php _e('Time', 'easyReservations'); ?></th>
			<th><?php _e('Price', 'easyReservations'); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody id="sortable">
		<script>var filter = new Array();</script>
		<?php
			foreach($all_filter as $key => $filter):
				if($filter['type'] == 'unavail' || $filter['type'] == 'req') continue;
				$count++;
				$filter_info = easyreservations_get_filter_description($filter, $resource, 1);
			?><tr>
					<script>
						filter[<?php echo $key; ?>] = new Object();
						filter[<?php echo $key; ?>] = <?php echo json_encode($filter); ?>;
					</script>
					<td class="resourceType">
						<?php echo $filter_info[0]; ?> <?php echo stripslashes($filter['name']); ?>
					</td>
					<td style="vertical-align:middle;text-align:center;width:40px">
						<?php if(isset( $filter['imp'])) echo $filter['imp']; ?>
					</td>
					<td><?php echo $filter_info[1]; ?></td>
					<td><?php echo $filter_info[2]; ?></td>
					<td style="vertical-align:middle;text-align:center">
						<a href="javascript:filter_edit(<?php echo $key; ?>);" class="fa fa-pencil easy-tooltip" title="<?php echo sprintf(__('Edit %s', 'easyReservations'), __('filter', 'easyReservations')); ?>"></a>
						<a href="javascript:filter_copy(<?php echo $key; ?>);" class="fa fa-copy easy-tooltip" title="<?php echo sprintf(__('Copy %s', 'easyReservations'), __('filter', 'easyReservations')); ?>"></a>
						<a href="<?php echo wp_nonce_url('admin.php?page=reservation-resources&resource='.$resource->ID.'&delete_filter=' . $key, 'easy-resource-delete-filter'); ?>#filters" class="fa fa-trash easy-tooltip" title="<?php echo sprintf(__('Delete %s', 'easyReservations'), __('filter', 'easyReservations')); ?>"></a>
					</td>
				</tr>
				<?php unset($all_filter[$key]);?>
			<?php endforeach;?>
			<?php if($count == 0): ?>
				<td colspan="5"><?php echo __('No price filter defined', 'easyReservations'); ?></td>
			<?php endif; ?>
	</tbody>
	<thead>
		<tr class="tmiddle">
			<th class="tmiddle"><?php _e('Filter', 'easyReservations'); ?></th>
			<th class="tmiddle" colspan="3"><?php _e('Condition', 'easyReservations'); ?></th>
			<th class="tmiddle"></th>
		</tr>
	</thead>
	<tbody>
	<?php
		if(count($all_filter) > 0):
			foreach($all_filter as $key => $filter): //foreach filter array
				$description = easyreservations_get_filter_description($filter, $resource, 1);
				if($filter['type'] =="unavail"){
					$bg_color='#D8211E';
					$condition_string = $description[1];
				} elseif($filter['type']=="req"){
					$bg_color='#F4AA33';
					$condition_string = $description[1].' '.__('resources requirements change to','easyReservations');
					$max_nights = ($filter['req']['nights-max'] == 0) ? '&infin;' : $filter['req']['nights-max'];
					$max_pers = ($filter['req']['pers-max'] == 0) ? '&infin;' : $filter['req']['pers-max'];
					$condition_string .=  '<br>'.ucfirst(__('persons','easyReservations')).': <b>'.$filter['req']['pers-min'].'</b> - <b>'.$max_pers.'</b>, '.er_date_get_interval_label($resource->interval, 2, true).': <b>'. $filter['req']['nights-min'].'</b> - <b>'.$max_nights.'</b><br>';
					$start_on = '';
					$end_on = '';
					if($filter['req']['start-on'] == 0) $start_on = __("All", 'easyReservations').', ';
					elseif($filter['req']['start-on'] == 8) $start_on = __("None", 'easyReservations').', ';
					else {
						for($i = 1; $i < 8; $i++){
							if(in_array($i,$filter['req']['start-on'])) $start_on .= '<b>'.substr($days[$i-1],0,2).'</b>, ';
						}
					}
					if(isset($filter['req']['start-h'])){
						$start_on = substr($start_on,0,-2);
						$start_on.= ' '.strtolower(sprintf($hour_string,'<b>'.$filter['req']['start-h'][0].'h</b>', '<b>'.$filter['req']['start-h'][1])).'h</b>, ';
					}
					if($filter['req']['end-on'] == 0) $end_on = __("All", 'easyReservations').', ';
					elseif($filter['req']['end-on'] == 8) $end_on = __("None", 'easyReservations').', ';
					else {
						for($i = 1; $i < 8; $i++){
							if(in_array($i,$filter['req']['end-on'])) $end_on .= '<b>'.substr($days[$i-1],0,2).'</b>, ';
						}
					}
					if(isset($filter['req']['end-h'])){
						$end_on = substr($end_on,0,-2);
						$end_on.= ' '.strtolower(sprintf($hour_string, '<b>'.$filter['req']['end-h'][0].'h</b>', '<b>'.$filter['req']['end-h'][1].'h</b>'));
					}
					$condition_string .= 'Arrival: '.$start_on.'Departure: '.substr($end_on,0,-2);
				} ?>
				<tr name="notsort">
					<script>
						filter[<?php echo $key; ?>] = new Object();
						filter[<?php echo $key; ?>] = <?php echo json_encode($filter); ?>;
					</script>
					<td class="resourceType">
						<code  style="color:<?php echo $bg_color; ?>;font-weight:bold;display:inline-block">
							<?php echo ucfirst($filter['type']); ?>
						</code>
						<?php echo $filter['name']; ?>
					</td>
					<td colspan="<?php if($filter['type'] == "unavail" || $filter['type'] == "req") echo 3; else echo 2; ?>">
						<?php echo $condition_string; ?>
					</td>
					<td style="vertical-align:middle;text-align:center">
						<a href="javascript:filter_edit(<?php echo $key; ?>);" class="fa fa-pencil easy-tooltip" title="<?php echo sprintf(__('Edit %s', 'easyReservations'), __('filter', 'easyReservations')); ?>"></a>
						<a href="javascript:filter_copy(<?php echo $key; ?>);" class="fa fa-copy easy-tooltip" title="<?php echo sprintf(__('Copy %s', 'easyReservations'), __('filter', 'easyReservations')); ?>"></a>
						<a href="<?php echo wp_nonce_url('admin.php?page=reservation-resources&resource='.$resource->ID.'&delete_filter=' . $key, 'easy-resource-delete-filter'); ?>" class="fa fa-trash easy-tooltip" title="<?php echo sprintf(__('Delete %s', 'easyReservations'), __('filter', 'easyReservations')); ?>"></a>
					</td>
				</tr>
				<?php
			endforeach;
	  else: ?>
			<tr>
				<td colspan="5"><?php _e('No filter defined', 'easyReservations'); ?></td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>